<?php
// install.php
require_once __DIR__ . '/autoload.php';

use App\Database;

header('Content-Type: text/plain; charset=utf-8');
try {
    $db = Database::getInstance();
    echo "База данных успешно создана/проверена.\n";

    // Проверим, что таблицы есть
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    echo "Таблицы: " . implode(', ', $tables) . "\n";
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}