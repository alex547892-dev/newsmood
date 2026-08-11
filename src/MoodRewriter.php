<?php
namespace App;

class MoodRewriter
{
    private \PDO $db;
    private ?array $aiConfig;

    public function __construct()
    {
        $config = require __DIR__ . '/../config.php';
        $this->db = Database::getInstance();
        $this->aiConfig = $config['ai_api'] ?? null;
        // Если ключ не задан или равен заглушке, считаем AI недоступным
        if (empty($this->aiConfig['key']) || $this->aiConfig['key'] === 'ВАШ_API_КЛЮЧ') {
            $this->aiConfig = null;
        }
    }

    /**
     * Получает переписанный вариант для новости и настроения.
     * Если уже есть в кеше – возвращает готовый массив.
     * Если нет – генерирует через AI и сохраняет.
     *
     * @param int $newsId
     * @param string $moodCode
     * @return array|null ['title_rewritten', 'content_rewritten'] или null, если новость не найдена
     */
    public function getOrCreate(int $newsId, string $moodCode): ?array
    {
        // Проверяем кеш
        $stmt = $this->db->prepare(
            'SELECT title_rewritten, content_rewritten FROM news_mood WHERE news_id = ? AND mood_code = ?'
        );
        $stmt->execute([$newsId, $moodCode]);
        $row = $stmt->fetch();
        if ($row && !empty($row['content_rewritten'])) {
            return $row;
        }

        // Получаем оригинальную новость
        $newsStmt = $this->db->prepare('SELECT title_original, content_original FROM news WHERE id = ?');
        $newsStmt->execute([$newsId]);
        $news = $newsStmt->fetch();
        if (!$news) {
            return null;
        }

        // Если AI недоступен, возвращаем оригинал (без сохранения в кеш)
        if ($this->aiConfig === null) {
            return [
                'title_rewritten'   => $news['title_original'],
                'content_rewritten' => $news['content_original']
            ];
        }

        // Генерируем через AI
        $titleRewritten = $this->callAi($news['title_original'], $moodCode);
        $contentRewritten = $this->callAi($news['content_original'] ?? '', $moodCode);

        // Сохраняем в кеш
        $insert = $this->db->prepare(
            'INSERT OR REPLACE INTO news_mood (news_id, mood_code, title_rewritten, content_rewritten, generated_at) VALUES (?, ?, ?, ?, datetime("now"))'
        );
        $insert->execute([$newsId, $moodCode, $titleRewritten, $contentRewritten]);

        return [
            'title_rewritten'   => $titleRewritten,
            'content_rewritten' => $contentRewritten
        ];
    }

    /**
     * Отправляет текст в AI API для переписывания в указанном настроении.
     *
     * @param string $text
     * @param string $moodCode
     * @return string
     */
    private function callAi(string $text, string $moodCode): string
    {
        if (trim($text) === '') {
            return '';
        }
        if ($this->aiConfig === null) {
            return $text; // fallback
        }

        $moodLabels = [
            'happy'   => 'радостном и оптимистичном',
            'sad'     => 'грустном и меланхоличном',
            'ironic'  => 'ироничном и саркастичном',
            'neutral' => 'нейтральном и безэмоциональном'
        ];
        $mood = $moodLabels[$moodCode] ?? $moodCode;

        // Промпт с жесткими требованиями сохранения фактов и длины
        $prompt = <<<PROMPT
Перепиши следующий текст в {$mood} тоне.

**Важные правила:**
- Сохрани все фактические данные: числа, даты, имена, названия организаций, географические названия, цитаты, статистические данные.
- Не изменяй суть событий, не добавляй вымышленных деталей.
- Не сокращай текст искусственно, сохраняй его первоначальную длину и структуру абзацев.
- Просто переформулируй те же факты, используя соответствующую эмоциональную окраску и лексику.
- Не добавляй комментариев от себя, не оценивай события.
- НЕ добавляй комментарии от себя в начале текста с пояснениями, что ты делать собираешься! (ОЧЕНЬ ВАЖНО)

Исходный текст:
$text
PROMPT;

        $payload = json_encode([
            'model'       => $this->aiConfig['model'],
            'messages'    => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.7,
            'max_tokens'  => 2048
        ]);

        $ch = curl_init($this->aiConfig['url']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->aiConfig['key']
            ],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 30
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            return $text; // fallback to original on network error
        }

        $data = json_decode($response, true);
        $content = $data['choices'][0]['message']['content'] ?? null;
        return $content ? trim($content) : $text;
    }
}