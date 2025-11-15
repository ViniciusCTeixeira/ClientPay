<?php

class Formatter
{
    public static function money($v): string
    {
        return number_format((float)$v, 2, ',', '.');
    }

    public static function dateBr($d): string
    {
        return date('d/m/Y', strtotime($d));
    }
}
