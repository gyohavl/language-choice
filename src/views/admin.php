<?php
include(__DIR__ . '/../main.php');

if (!configExists()) {
    echo showConfigForm();
} else if (!adminLoggedIn()) {
    echo showLoginForm();
} else if (!dbConnectionOk()) {
    echo showConfigForm('Připojení k databázi nefunguje.');
} else if (!dbReady()) {
    echo showDbSetup();
} else {
    if (!empty($_GET['list'])) {
        echo showList($_GET['list']);
    } else if (!empty($_GET['edit'])) {
        echo showEditForm($_GET['edit']);
    } else if (!empty($_POST['edit'])) {
        echo editData($_POST['edit']);
    } else if (!empty($_GET['confirm']) || !empty($_POST['confirm'])) {
        echo confirm();
    } else if (!empty($_GET['system'])) {
        echo systemPage($_GET['system']);
    } else if (!empty($_POST['system'])) {
        echo systemAction($_POST['system']);
    } else {
        echo showDashboard();
    }
}
