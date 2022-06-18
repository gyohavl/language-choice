<?php
function showDashboard() {
    return adminTemplate(fillTemplate('dashboard', array()));
    // text e-mailu
    // text nahoře na webu
    // export souboru
    // odeslání e-mailu ([zadat údaje k serveru,] odeslat testovací e-mail, [zadat text e-mailu,] zadat heslo, pak odeslat e-mail rodičům, uložit informaci o odeslání)
    // změna zcizeného klíče
    // vymazat všechna data z databáze
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
