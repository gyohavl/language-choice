<?php
include(__DIR__ . '/../main.php');

if (!configExists()) {
    echo showConfigForm();
} else if (!adminLoggedIn()) {
    echo showLoginForm();
} else if (!dbReady()) {
    echo showDbSetup();
} else {
    if (!empty($_GET['edit'])) {
        echo showEditForm($_GET['edit']);
    } else if (!empty($_POST['edit'])) {
        echo editData($_POST['edit']);
    } else {
        echo showDashboard();
    }
}
