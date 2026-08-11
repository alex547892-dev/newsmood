<?php
namespace App;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config.php';
            $dbPath = $config['db_path'];
            try {
                self::$instance = new PDO('sqlite:' . $dbPath, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                self::$instance->exec('PRAGMA foreign_keys = ON');
                self::initializeSchema();
            } catch (PDOException $e) {
                die("Ошибка базы данных: " . $e->getMessage() . " Проверьте права на запись в папку проекта.");
            }
        }
        return self::$instance;
    }

    private static function initializeSchema(): void
    {
        $sql = file_get_contents(__DIR__ . '/../migrations/001_create_tables.sql');
        self::$instance->exec($sql);
    }
}