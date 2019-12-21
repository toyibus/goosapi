<?php namespace Goosapi\Utils;

class ArrayUtils 
{
    public static function isMatchAssoc($array, $except = "")
    {
        foreach ($array as $key => $val)
        {
            if (!empty($except) && preg_match($except, $key)) continue;
            if ($key != $val) return false;
        }
        return true; 
    }

    public static function findByKey($array, $pattern)
    {
        $results = [];

        foreach ($array as $key => $val)
        {
            if (preg_match($pattern, $key)) $results[$key] = $val;
        }
        return $results;
    }

    public static function getValue($array, $key, $default = "")
    {
        return empty($array[$key]) ? $default : $array[$key];
    }

    public static function findEmptyValue($array, $callback = null)
    {
        $results = [];
        foreach ($array as $key => $value)
        {
            if (empty($value))
            {
                $results[$key] = $value;
                if ($callback) $callback($key);
            }
        }
        return $results;
    }
}