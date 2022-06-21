<?php
function sql($sql, $fetch = true, $params = array(), $checkConnection = false) {
    global $config;
    $charset = getSqlLanguageSettings(true);
    // logSql($sql, $params);

    try {
        $db = new PDO('mysql:dbname=' . $config['dbname'] . ';charset=' . $charset . ';host=' . $config['dbhost'], $config['dbuser'], $config['dbpass']);

        if ($checkConnection) {
            return true;
        }

        $db->exec('set names ' . $charset);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = $db->prepare($sql);
        $query->execute($params);
        return $fetch ? $query->fetchAll() : true;
    } catch (PDOException $error) {
        if ($checkConnection) {
            return false;
        }

        throw new Exception($error);
    }
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

function logSql($sql, $params) {
    $params = $params ? $params : array();
    $log  = date('r') . "\t" . $_SERVER['REQUEST_URI'] . "\t" . $sql . "\t" . implode(', ', $params) . PHP_EOL;
    file_put_contents(__DIR__ . '/../../db.log', $log, FILE_APPEND);
}
