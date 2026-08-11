<!-- templates/detail.php -->
<div class="mood-switcher">
    <?php
    $moods = [
            'original' => 'Оригинал',
            'happy' => 'Радостное 😊',
            'sad' => 'Грустное 😢',
            'ironic' => 'Ироничное 😏',
            'neutral' => 'Нейтральное 😐',
    ];
    $currentMood = $_GET['mood'] ?? 'original';
    foreach ($moods as $code => $label): ?>
        <a href="/news.php?id=<?= $news['id'] ?>&mood=<?= $code ?>"
           class="mood-btn <?= $code === $currentMood ? 'active' : '' ?>">
            <?= $label ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="detail-layout">
    <div class="original-box">
        <h2>Оригинал (<?= htmlspecialchars($news['source_name']) ?>)</h2>
        <h3><?= htmlspecialchars($news['title_original']) ?></h3>
        <p><?= nl2br(htmlspecialchars($news['content_original'])) ?></p>
        <a href="<?= htmlspecialchars($news['source_url']) ?>" target="_blank" class="source-link">Читать в источнике</a>
    </div>
    <?php if ($mood !== 'original'): ?>
        <div class="rewritten-box">
            <h2><?= $moodLabel ?> <?= $factIndicator ?></h2>
            <?php if ($rewritten): ?>
                <h3><?= htmlspecialchars($rewritten['title_rewritten']) ?></h3>
                <p><?= nl2br(htmlspecialchars($rewritten['content_rewritten'])) ?></p>
                <small>Сгенерировано AI, проверка фактов: <?= $factIndicator ? '⚠️ Возможны искажения' : '✓' ?></small>
            <?php else: ?>
                <p>Не удалось сгенерировать версию. Попробуйте позже.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="rewritten-box">
            <p>Выберите настроение выше, чтобы увидеть переписанный текст.</p>
        </div>
    <?php endif; ?>
</div>