<?php
include(__DIR__ . '/../main.php');

if (!configExists()) {
    echo showConfigForm();
} else if (!adminLoggedIn()) {
    echo showLoginForm();
} else if (!dbReady()) {
    echo showDbSetup();
} else {
    echo 'admin successful';
}