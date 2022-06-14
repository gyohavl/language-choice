<?php
function _t($group, $key) {
    $strings = array(
        'form' => array(
            'sid' => 'spisové číslo',
            'email' => 'e-mail',
            'name' => 'celé jméno',
            'class' => 'třída',
            'choice' => 'volba jazyka'
        ),
        'form-l' => array(
            'name' => 'jazyk (název)',
            'class' => 'pro třídu',
            'limit' => 'kapacita',
            'export' => 'označení pro export'
        ),
        'confirm' => array(
            'delete-student' => 'smazat studenta',
            'delete-language' => 'smazat jazyk'
        ),
        'success' => array(
            'login' => 'Výborně! Byli jste přihlášeni.',
            'setup' => 'Výborně! Údaje byly nastaveny.',
            'tables' => 'Tabulky byly vytvořeny.',
            'send-test' => 'Testovací e-mail byl odeslán.',
            'done' => 'Hotovo.'
        ),
        'time' => array(
            'heading' => 'Časy',
            'from' => 'čas spuštění',
            'to' => 'čas ukončení'
        ),
        'text' => array(
            'heading' => 'Texty',
            'client' => 'text nahoře na webu',
            'email_sender' => 'odesílatel e-mailu (jméno)',
            'email_subject' => 'předmět e-mailu',
            'email_body' => 'tělo e-mailu'
        ),
        'mailer' => array(
            'heading' => 'Nastavení e-mailového serveru',
            'host' => 'adresa serveru (host)',
            'email' => 'e-mailová adresa',
            'password' => '<abbr title="je uloženo nezabezpečeně, lze ho zadat až při odesílání e-mailů">heslo (nepovinné)</abbr>'
        ),
        'other' => array(
            'heading' => 'Ostatní',
            'last_sent' => '<abbr title="generováno automaticky">naposledy odesláno</abbr>'
        ),
    );

    return (isset($strings[$group]) && isset($strings[$group][$key])) ? $strings[$group][$key] : "$group.$key";
}
