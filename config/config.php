<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

// Autoload doesn't work as I wish ...
require_once(dirname(__FILE__) . '/../datum_formatieren.php');

if (!function_exists('datum_formatieren'))
{
    function datum_formatieren($entry)
    {
        static $obj = null;
        if (is_null($obj))
            $obj = new DatumFormatieren();
        return $obj->datum_formatieren($entry);
    }
}

if (!function_exists('uhrzeit_formatieren'))
{
    function uhrzeit_formatieren($entry)
    {
        static $obj = null;
        if (is_null($obj))
            $obj = new DatumFormatieren();
        return $obj->uhrzeit_formatieren($entry);
    }
}

if (!function_exists('get_start_unix'))
{
    function get_start_unix($entry)
    {
        static $obj = null;
        if (is_null($obj))
            $obj = new DatumFormatieren();
        return $obj->get_start_unix($entry);
    }
}

