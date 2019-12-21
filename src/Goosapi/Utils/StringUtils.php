<?php namespace Goosapi\Utils;

class StringUtils 
{
    public static function startWith($subject, $needle)
    {
        return $needle === "" || strrpos($subject, $needle, -strlen($subject)) !== false;
    }

    public static function replaceFirst($from, $to, $content)
    {
        $from = '/'.preg_quote($from, '/').'/';

        return preg_replace($from, $to, $content, 1);
    }
}