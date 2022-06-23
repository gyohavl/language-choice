<?php
function adminLoggedIn() {
    global $config;
    session_start();

    // https://stackoverflow.com/questions/520237/how-do-i-expire-a-php-session-after-30-minutes/1270960#1270960
    $sessionExpired = isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800);
    $postLogout = isset($_POST['logout']) ? true : false;

    if ($sessionExpired || $postLogout) {
        session_unset();
        session_destroy();
    }

    if ($postLogout) {
        redirectMessage('logout');
    }

    $_SESSION['last_activity'] = time();
    return isset($_SESSION['adminpass']) ? password_verify($config['adminpass'], $_SESSION['adminpass']) : false;
}

function showLoginForm() {
    global $config;
    $formDisplayData = array('error' => '');

    if (isset($_POST['adminpass'])) {
        if ($config['adminpass'] === $_POST['adminpass']) {
            $_SESSION['adminpass'] = password_hash($config['adminpass'], PASSWORD_DEFAULT);
            redirectMessage('login', 'success', ($_SERVER['QUERY_STRING'] ? ('?' . $_SERVER['QUERY_STRING']) : ''));
        }

        $formDisplayData['error'] = 'Zadali jste špatné heslo.';
    }

    return adminTemplate(fillTemplate('login-form', $formDisplayData));
}
