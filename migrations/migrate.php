<?php
// migrations/migrate.php
$config = require __DIR__ . '/../config.php';
$pdo = new PDO('sqlite:' . $config['db_path']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = file_get_contents(__DIR__ . '/001_create_tables.sql');
$pdo->exec($sql);
echo "Миграции выполнены.\n";