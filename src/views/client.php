<?php
include(__DIR__ . '/../main.php');

if (configExists() && dbConnectionOk()) {
    echo file_get_contents('templates/client.html');
}