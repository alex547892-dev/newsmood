<?php
namespace App;

use PDOException;

class NewsFetcher
{
    private \PDO $db;
    private string $apiKey;
    private string $apiUrl = 'https://gnews.io/api/v4/search';

    public function __construct()
    {
        $config = require __DIR__ . '/../config.php';
        $this->db = Database::getInstance();
        $this->apiKey = $config['gnews_api_key'] ?? '';
        if (empty($this->apiKey)) {
            throw new \RuntimeException('GNews API key is missing in config.php');
        }
    }

    public function fetchAndSave(): int
    {
        $articles = $this->fetchFromGNews();
        $added = 0;
        foreach ($articles as $article) {
            if ($this->saveArticle($article)) {
                $added++;
            }
        }
        return $added;
    }

    private function fetchFromGNews(): array
    {
        $query = http_build_query([
            'apikey' => $this->apiKey,
            'lang'   => 'ru',
            'max'    => 10,
            'q'      => 'новости', // обязательный параметр для /search
        ]);
        $url = $this->apiUrl . '?' . $query;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT         => 10,
            CURLOPT_CONNECTTIMEOUT  => 5,
            CURLOPT_USERAGENT       => 'Mozilla/5.0 (compatible; NewsMood/1.0)',
            CURLOPT_SSL_VERIFYPEER  => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false || !empty($error)) {
            error_log("NewsFetcher: cURL error: $error");
            throw new \RuntimeException("Ошибка подключения к GNews API: $error");
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("NewsFetcher: JSON decode error: " . json_last_error_msg());
            throw new \RuntimeException("Некорректный ответ от GNews API");
        }

        if (isset($data['errors'])) {
            $errMsg = is_array($data['errors']) ? implode(', ', $data['errors']) : $data['errors'];
            error_log("NewsFetcher: GNews API errors: $errMsg");
            throw new \RuntimeException("GNews API вернул ошибку: $errMsg");
        }

        if (!isset($data['articles']) || !is_array($data['articles'])) {
            error_log("NewsFetcher: No articles in response");
            throw new \RuntimeException("GNews API не вернул статей. Возможно, исчерпан лимит.");
        }

        return $data['articles'];
    }

    private function saveArticle(array $article): bool
    {
        $title      = $article['title'] ?? 'Без заголовка';
        $link       = $article['url'] ?? '';
        $content    = $article['content'] ?? $article['description'] ?? '';
        $sourceName = $article['source']['name'] ?? 'GNews';
        $pubDate    = null;
        if (!empty($article['publishedAt'])) {
            $timestamp = strtotime($article['publishedAt']);
            $pubDate   = $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
        }

        if (empty($link)) {
            return false;
        }

        try {
            $stmt = $this->db->prepare(
                'INSERT OR IGNORE INTO news (source_url, source_name, title_original, content_original, pub_date) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$link, $sourceName, $title, strip_tags($content), $pubDate]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("NewsFetcher: error inserting article: " . $e->getMessage());
            return false;
        }
    }
}