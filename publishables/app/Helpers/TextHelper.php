<?php

namespace App\Helpers;

class TextHelper
{
    /**
     * @param $text
     * @param $limit
     * @return string
     */
    public static function character_shorter(string $text, int $limit): string
    {
        if (strlen($text) > $limit) {
            $new_text = function_exists("mb_substr")
                ? mb_substr($text, 0, $limit)
                : substr($text, 0, $limit);

            $new_text = trim($new_text);
            return $new_text . "...";
        } else {
            return $text;
        }
    }

    /**
     * @param $text
     * @param $limit
     * @return string
     */
    public static function word_shorter(string $text, int $limit): string
    {
        preg_match('/^\s*+(?:\S++\s*+){1,' . (int)$limit . '}/', $text, $matches);
        if (str_word_count($text) > $limit)
            $matches[0] .= '...';
        return rtrim($matches[0] ?? '');
    }

    /**
     * @param $text
     * @param $censored_words
     * @param string $replacement
     * @return string
     */
    public static function word_censored(string $text, $censored_words, $replacement = '')
    {
        $text = str_replace($censored_words, $replacement, $text);

        return trim($text);
    }

    public static function wrapInTag(string $word, ?string $tag = ''): string
    {
        $open = $tag ? '<' . $tag . '>' : '';
        $close = $tag ? '</' . $tag . '>' : '';

        return $open . $word . $close;
    }

}
