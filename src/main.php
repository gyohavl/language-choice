<?php
date_default_timezone_set('Europe/Prague');
include(__DIR__ . '/db.php');
include(__DIR__ . '/setup.php');
include(__DIR__ . '/auth.php');

if (file_exists(__DIR__ . '/../config.php')) {
    $config = include(__DIR__ . '/../config.php');
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
