<?php
function showDashboard() {
    return adminTemplate(fillTemplate('dashboard', array()));
}

function redirectMessage($message = 'done', $type = 'success', $url = '.') {
    $query = parse_url($url, PHP_URL_QUERY);
    $separator = $query ? '&' : '?';
    header("Location: $url$separator$type=$message");
    exit;
}

function getInfoMessage() {
    if (!empty($_GET['success'])) {
        return '<div class="success">' . _t('success', $_GET['success']) . '</div>';
    }

    return '';
}
