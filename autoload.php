<?php
// autoload.php

spl_autoload_register(function (string $class): void {
    // Базовый префикс неймспейса приложения
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/src/';

    // Проверяем, что класс использует наш префикс
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return; // не наш класс
    }

    // Убираем префикс, получаем относительное имя класса
    $relativeClass = substr($class, $len);

    // Заменяем разделители неймспейса на DIRECTORY_SEPARATOR
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});