<?php
function showDashboard() {
    $html = '<h1>Nástěnka</h1><ul>
    <li><a href="?list=students">studenti</a></li>
    <li><a href="?list=languages">jazyky</a></li>
    <li><a href="?list=data">další data</a></li>
    <li><a href="?system=state">stav systému</a></li>
    </ul>';
    return adminTemplate($html);
    // studenti
    // jazyky
    // čas spuštění a ukončení
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
}

function getInfoMessage() {
    if (!empty($_GET['success'])) {
        return '<div class="success">' . _t('success', $_GET['success']) . '</div>';
    }

    return '';
}
