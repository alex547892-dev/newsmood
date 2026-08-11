<!-- templates/grid.php -->
<div class="news-grid">
    <?php foreach ($newsList as $item): ?>
        <div class="news-card">
            <div class="card-body">
                <div class="card-source"><?= htmlspecialchars($item['source_name']) ?> — <?= $item['pub_date'] ?></div>
                <h3 class="card-title"><?= htmlspecialchars($item['title']) ?></h3>
                <p class="card-excerpt"><?= htmlspecialchars($item['excerpt']) ?></p>
                <a href="/news.php?id=<?= $item['id'] ?>" class="card-link">Читать →</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>