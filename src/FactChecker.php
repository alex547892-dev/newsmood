<?php
namespace App;

class FactChecker
{
    /**
     * Проверяет, сохранены ли ключевые числовые данные в переписанном тексте.
     * Возвращает массив потерянных чисел/дат или пустой массив, если всё ок.
     */
    public static function check(string $original, string $rewritten): array
    {
        $lost = [];

        // Извлекаем числа (целые и десятичные)
        preg_match_all('/\d+([.,]\d+)?/', $original, $matches);
        $numbers = $matches[0] ?? [];
        foreach ($numbers as $num) {
            // Нормализуем разделитель для поиска
            $pattern = '/' . preg_quote(str_replace(',', '.', $num), '/') . '/';
            if (!preg_match($pattern, $rewritten)) {
                $lost[] = $num;
            }
        }

        // Извлекаем потенциальные имена (слова с большой буквы длиной > 1)
        preg_match_all('/\b[А-ЯA-Z][а-яa-z]+\b/u', $original, $nameMatches);
        $names = $nameMatches[0] ?? [];
        foreach ($names as $name) {
            if (mb_strlen($name) > 2 && !preg_match('/' . preg_quote($name, '/') . '/u', $rewritten)) {
                $lost[] = $name;
            }
        }

        // Можно добавить даты, но пока достаточно чисел и имён
        return array_unique($lost);
    }

    /**
     * Возвращает HTML-индикатор, если были потеряны факты.
     */
    public static function getIndicator(string $original, string $rewritten): string
    {
        $lost = self::check($original, $rewritten);
        if (count($lost) > 0) {
            return '<span title="Возможно, потеряны факты: ' . htmlspecialchars(implode(', ', $lost)) . '" style="color: orange; cursor: help;">⚠️</span>';
        }
        return '';
    }
}