<?php
function sql($sql, $fetch = true, $params = array()) {
    global $config;
    $charset = getSqlLanguageSettings(true);
    $db = new PDO('mysql:dbname=' . $config['dbname'] . ';charset=' . $charset . ';host=' . $config['dbhost'], $config['dbuser'], $config['dbpass']);
    $db->exec('set names ' . $charset);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $query = $db->prepare($sql);
    $query->execute($params);
    return $fetch ? $query->fetchAll() : true;
}

function prefixTable($name) {
    return 'lc_' . $name;
}

function getSqlLanguageSettings($justCharset = false) {
    $engine = 'InnoDB';
    $charset = 'utf8';
    // $charset = 'utf8mb4';
    $collate = 'utf8_unicode_ci';
    // $collate = 'utf8mb4_0900_ai_ci';

    if ($justCharset) {
        return $charset;
    } else {
        return "ENGINE=$engine DEFAULT CHARSET=$charset COLLATE=$collate";
    }
}
