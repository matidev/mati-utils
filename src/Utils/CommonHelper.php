<?php

namespace Mati\Utils;

class CommonHelper
{
    public static function dumpDie($data, $addPre = true): void
    {
        echo $addPre ? '<pre>' : '';
        var_dump($data);
        echo $addPre ? '</pre>' : '';
        exit(1);
    }

    public static function formatNumber($number, $decimalSeparator = ',', $thousandsSeparator = '.')
    {
        if (empty($number) || is_numeric($number)) {
            return $number;
        }

        return number_format($number, 0, $decimalSeparator, $thousandsSeparator);
    }

    public static function makeNumberCallLink($num, $code = '+1'): string
    {
        return $code . preg_replace('/\D/', '', ltrim($num, '0'));
    }

    public static function getUserIp(): string
    {
        return IpHelper::userIp();
    }

    public static function getUserAgent(): string
    {
        return !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }

    public static function insertArrayAfterKey(&$array, $key, $insertArray): void
    {
        $index = array_search($key, array_keys($array));

        if ($index === false) {
            $array = array_merge($array, $insertArray);
        } else {
            $array = array_merge(
                array_slice($array, 0, $index + 1, true),
                $insertArray,
                array_slice($array, $index + 1, null, true)
            );
        }
    }
}
