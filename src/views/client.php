<?php
include(__DIR__ . '/../main.php');

if (configExists() && dbConnectionOk()) {
    if (!empty($_GET['k'])) {
        $data = sql('SELECT * FROM `' . prefixTable('students') . '` WHERE `key`=?;', true, array($_GET['k']));

        if (isset($data[0])) {
            echo fillTemplate('client', $data[0]);
        }
    }
}