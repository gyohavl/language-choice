<?php
function showDashboard() {
    $html = '<h1>Nástěnka</h1><h2>Studenti <a href="?edit=students">(upravit)</a></h2>';
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

function showEditForm($form) {
    switch ($form) {
        case 'students':
            $formName = 'seznam studentů';
            $html = '<textarea name="students"></textarea><br>';
            break;

        default:
            $formName = '';
            $html = '<a href=".">zpět</a>';
            break;
    }

    return adminTemplate('<h1>Upravit ' . $formName . '</h1><form method="post" action=".">' . $html . '<input type="hidden" name="edit" value="' . $form . '"><input type="submit" value="Odeslat"></form>');
}

function editData($form) {
    return $form;
}
