<?php
include(__DIR__ . '/../main.php');

if (configExists()) {
    echo file_get_contents('templates/client.html');
}