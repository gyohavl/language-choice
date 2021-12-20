<?php
date_default_timezone_set('Europe/Prague');
include(__DIR__ . '/db.php');
include(__DIR__ . '/setup.php');

function _i($a, $b) {
    return isset($a) ? $a : $b;
}

function adminTemplate($content) {
    return fillTemplate('admin', array('content' => $content));
}

function fillTemplate($name, $data) {
    $html = file_get_contents(__DIR__ . '/../templates/' . $name . '.html');

    foreach ($data as $key => $value) {
        $html = str_replace('{' . $key . '}', $value, $html);
    }

    return $html;
}
