<?php

class Validation
{
    public static function required(array $data, array $fields): array
    {
        $e = [];
        foreach ($fields as $f) {
            if (!isset($data[$f]) || trim((string)$data[$f]) === '') $e[$f] = 'Obrigatório';
        }
        return $e;
    }
}
