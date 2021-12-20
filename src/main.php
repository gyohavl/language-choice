<?php
date_default_timezone_set('Europe/Prague');
include(__DIR__ . '/db.php');
include(__DIR__ . '/setup.php');

function adminTemplate($content) {
    return str_replace('{content}', $content, file_get_contents(__DIR__ . '/../templates/admin.html'));
}
