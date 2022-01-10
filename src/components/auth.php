<?php
function adminLoggedIn() {
    global $config;
    session_start();
    return isset($_SESSION['adminpass']) ? password_verify($config['adminpass'], $_SESSION['adminpass']) : false;
}

function showLoginForm() {
    global $config;
    $formDisplayData = array('error' => '');

    if (isset($_POST['adminpass'])) {
        if ($config['adminpass'] == $_POST['adminpass']) {
            $_SESSION['adminpass'] = password_hash($config['adminpass'], PASSWORD_DEFAULT);
            return adminTemplate('Výborně! Byli jste přihlášeni. <a href=".">Pokračovat do administrace…</a>');
        }

        $formDisplayData['error'] = 'Zadali jste špatné heslo.';
    }

    return adminTemplate(fillTemplate('login-form', $formDisplayData));
}
