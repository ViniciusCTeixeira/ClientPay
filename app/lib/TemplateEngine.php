<?php

class TemplateEngine
{
    public static function render(string $text, array $vars): string
    {
        $map = [];
        foreach ($vars as $k => $v) {
            $map['{' . $k . '}'] = (string)$v;
        }
        return strtr($text, $map);
    }
}
