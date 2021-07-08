<?php

namespace App\Helpers;

use Str;

class RandomGenerator
{

    public static function alphaNumericCode($lengthOfString)
    {
        $strResult = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . Str::random(50);
        return substr(str_shuffle($strResult), 0, $lengthOfString);
    }

    public static function numericCode($lengthOfString)
    {
        $strResult = '0123456789' . rand(10, 999);
        return substr(str_shuffle($strResult), 0, $lengthOfString);
    }
}
