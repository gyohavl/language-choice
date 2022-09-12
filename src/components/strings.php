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
            'delete-language' => 'smazat jazyk',
            'change-key' => 'změnit klíč studentovi',
            'wipe-next' => 'smazat data o studentech, časy a automaticky generovaná data',
            'wipe-mailer-password' => 'smazat uložené heslo k e-mailu',
            'wipe-clean' => 'smazat všechna data v databázi',
            'wipe-students' => 'smazat všechny studenty',
            'wipe-languages' => 'smazat všechny jazyky',
            'wipe-data' => 'smazat všechna „další data“'
        ),
        'success' => array(
            'login' => 'Výborně! Byli jste přihlášeni.',
            'setup' => 'Výborně! Údaje byly nastaveny.',
            'tables' => 'Tabulky byly vytvořeny.',
            'send-test' => 'Testovací e-mail byl odeslán.',
            'delete-student' => 'Student byl smazán.',
            'delete-language' => 'Jazyk byl smazán.',
            'change-key' => 'Klíč byl změněn.',
            'wipe-next' => 'Požadovaná data byla vymazána.',
            'wipe-mailer-password' => 'Heslo k e-mailu bylo smazáno z databáze.',
            'wipe-clean' => 'Všechna data byla smazána. Nyní můžete opět vytvořit prázdné tabulky v databázi.',
            'wipe-students' => 'Všichni studenti byli smazáni.',
            'wipe-languages' => 'Všechny jazyky byly smazány.',
            'wipe-data' => '„Další data“ byla smazána.',
            'confirmation-test' => 'E-mail byl pravděpodobně odeslán, zkontrolujte svou e-mailovou schránku.',
            'import-students' => 'Studenti byli importováni.',
            'student' => 'Student byl uložen.',
            'language' => 'Jazyk byl uložen.',
            'data' => 'Data byla uložena.',
            'done' => 'Hotovo.',
            'logout' => 'Byli jste odhlášeni.'
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
            'email_subject' => 'předmět úvodního e-mailu',
            'email_body' => 'tělo úvodního e-mailu'
        ),
        'choice' => array(
            'heading' => 'Nastavení výběru jazyka',
            'confirmation_send' => 'zasílat potvrzovací e-mail?',
            'confirmation_subject' => 'předmět potvrzovacího e-mailu',
            'confirmation_body' => 'tělo potvrzovacího e-mailu',
            'allow_change' => 'umožnit dodatečnou změnu vybraného jazyka?'
        ),
        'mailer' => array(
            'heading' => 'Nastavení e-mailového serveru (SMTP)',
            'host' => 'adresa serveru (host)',
            'email' => 'e-mailová adresa',
            'password' => '<abbr title="je uloženo nezabezpečeně, lze ho zadat až při odesílání úvodního e-mailu – je však nezbytné pro potvrzovací e-maily">heslo k e-mailu</abbr>'
        ),
        'generated' => array(
            'heading' => 'Generováno automaticky',
            'last_sent' => 'naposledy odesláno',
            'skipped_ids' => 'přeskočené identifikátory'
        ),
        'client-result' => array(
            'changed' => 'Jazyk byl úspěšně změněn.',
            'chosen' => 'Jazyk byl úspěšně zvolen.',
            'already-chosen' => 'Tento jazyk již byl zvolen dříve.',
            'no-change' => 'Jazyk byl již jednou vybrán.',
            'full' => 'Tento jazyk byl již zaplněn.',
            'no-language' => 'Zvolený jazyk není k dispozici.',
            'bad-key' => 'Špatný klíč studenta.',
            'bad-state' => 'Přihlašování není aktivní.',
            'error' => 'Chyba: '
        )
    );

    return (isset($strings[$group]) && isset($strings[$group][$key])) ? $strings[$group][$key] : "$group.$key";
}
