<?php
require_once __DIR__ . '/autoload.php';

use App\Database;
use App\MoodRewriter;
use App\FactChecker;

$db = Database::getInstance();
$id = $_GET['id'] ?? 0;
$mood = $_GET['mood'] ?? 'original';

$stmt = $db->prepare('SELECT * FROM news WHERE id = ?');
$stmt->execute([$id]);
$news = $stmt->fetch();
if (!$news) {
    http_response_code(404);
    echo 'Новость не найдена';
    exit;
}

$rewritten = null;
$factIndicator = '';
$moodLabel = '';

if ($mood !== 'original') {
    try {
        $rewriter = new MoodRewriter();
        $rewritten = $rewriter->getOrCreate($news['id'], $mood);

        $moodLabels = [
            'happy'   => 'Радостная версия',
            'sad'     => 'Грустная версия',
            'ironic'  => 'Ироничная версия',
            'neutral' => 'Нейтральная версия',
        ];
        $moodLabel = $moodLabels[$mood] ?? 'Версия';

        if ($rewritten && !empty($rewritten['content_rewritten'])) {
            $lost = FactChecker::check($news['content_original'], $rewritten['content_rewritten']);
            if (!empty($lost)) {
                $factIndicator = '⚠️';
            }
        }
    } catch (Exception $e) {
        $rewritten = null;
        $factIndicator = '⚠️ Ошибка генерации';
    }
}

ob_start();
require 'templates/detail.php';
$content = ob_get_clean();
require 'templates/layout.php';