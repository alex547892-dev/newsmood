<?php
require_once __DIR__ . '/autoload.php';

use App\Database;

$db = Database::getInstance();
$stmt = $db->query('SELECT id, source_name, title_original, content_original, pub_date FROM news ORDER BY pub_date DESC LIMIT 20');
$news = $stmt->fetchAll();

$newsList = [];
foreach ($news as $item) {
    $newsList[] = [
        'id'          => $item['id'],
        'source_name' => $item['source_name'],
        'pub_date'    => $item['pub_date'],
        'title'       => $item['title_original'],
        'excerpt'     => mb_substr(strip_tags($item['content_original']), 0, 150) . '…',
    ];
}

ob_start();
require 'templates/grid.php';
$content = ob_get_clean();
require 'templates/layout.php';