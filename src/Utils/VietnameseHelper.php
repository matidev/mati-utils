<?php

namespace Mati\Utils;

/**
 * Helper functions when working with Vietnamese
 * @since 1.0.0
 */
class VietnameseHelper
{
    public static function makeSlug($string): string
    {
        $string = trim($string);
        if (empty($string)) {
            return '';
        }

        // Remove Vietnamese accents
        $string = self::removeAccents($string);

        // Remove accents from other language
        $search = ['Ș', 'Ț', 'ş', 'ţ', 'Ş', 'Ţ', 'ș', 'ț', 'î', 'Î', 'ë', 'Ë'];
        $replace = ['s', 't', 's', 't', 's', 't', 's', 't', 'i', 'i', 'e', 'E'];
        $string = str_ireplace($search, $replace, strtolower(trim($string)));

        $string = preg_replace('/[^\w\- ]/', '', $string);
        $string = str_replace(' ', '-', $string);

        return preg_replace('/-{2,}/', '-', $string);
    }

    public static function removeAccents($string)
    {
        $unicodeArray = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D' => 'Đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        ];

        foreach ($unicodeArray as $nonUnicode => $unicode) {
            $string = preg_replace("/($unicode)/i", $nonUnicode, $string);
        }

        return $string;
    }
}
