<?php

/**
 * @param $bytes
 *
 * @return string
 */
function formatSizeUnits($bytes)
{
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }

    return $bytes;
}

/**
 * @param $object
 *
 * @return bool|int|string
 */
function get_class_name($object)
{
    $classname = get_class($object);
    if ($pos = strrpos($classname, '\\')) {
        return substr($classname, $pos + 1);
    }

    return $pos;
}

/**
 * @param $file
 *
 * @return string
 */
function htry_plugin_url($file)
{
    return plugins_url($file, dirname(dirname(__FILE__)));
}