<?php
if (file_exists(__DIR__ . '/../config.php')) {
    $config = include(__DIR__ . '/../config.php');
}

function sql($sql, $fetch = true, $params = array()) {
    global $config;
    $db = new PDO('mysql:dbname=' . $config['dbname'] . ';charset=utf8mb4;host=' . $config['dbhost'], $config['dbuser'], $config['dbpass']);
    $db->exec('set names utf8mb4');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $query = $db->prepare($sql);
    $query->execute($params);
    return $fetch ? $query->fetchAll() : true;
}
