<?php
require_once __DIR__ . '/autoload.php';

use App\NewsFetcher;

\App\Database::getInstance();

echo "<h2>Загружаем новости через GNews API</h2>";
flush();

try {
    $fetcher = new NewsFetcher();
    $count = $fetcher->fetchAndSave();
    echo "<p>Добавлено новостей: {$count}</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "<p><a href='/'>На главную</a></p>";