<?php
include('../src/main.php');
if (!configExists()) {
    echo showConfigForm();
} else if (!adminLoggedIn()) {
    echo showLoginForm();
} else {
    echo showDbSetup();
}