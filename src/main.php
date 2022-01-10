<?php
date_default_timezone_set('Europe/Prague');
include(__DIR__ . '/components/db.php');
include(__DIR__ . '/components/setup.php');
include(__DIR__ . '/components/auth.php');

if (configExists()) {
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
