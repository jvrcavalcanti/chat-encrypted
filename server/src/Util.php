<?php

namespace App;

final class Util
{
    public static function stringToBuffer(string $str): array
    {
        return array_map(fn(string $byte) => ord($byte), str_split($str));
    }

    public static function bufferToStrig(array $buffer): string
    {
        return array_reduce($buffer, fn(string $str, int $byte) => $str . chr($byte), '');
    }
}
