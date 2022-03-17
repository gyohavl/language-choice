<?php
function showDashboard() {
    $html = '<h1>Nástěnka</h1><ul>
    <li><a href="?list=students">studenti</a></li>
    <li><a href="?list=languages">jazyky</a></li>
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
