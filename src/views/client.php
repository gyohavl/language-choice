<?php
include(__DIR__ . '/../main.php');

echo fillTemplate('client', getClientView());

function getClientView() {
    if (configExists() && dbConnectionOk()) {
        if (!empty($_GET['k'])) {
            $data = sql('SELECT * FROM `' . prefixTable('students') . '` WHERE `key`=?;', true, array($_GET['k']));

            if (isset($data[0])) {
                $fill = $data[0];
                $fill['info'] = getDataValue('text.client');
                $fill['languages'] = getClientLanguages($data[0]['choice']);
                return fillTemplate('client-content', $fill);
            } else {
                return '<p>Tento přihlašovací odkaz je neplatný. Pokud si myslíte, že jde o chybu, kontaktujte správce aplikace.</p>';
            }
        } else {
            return '<p>Pro přihlášení do aplikace použijte odkaz z e-mailu.</p>';
        }
    } else {
        if (!configExists()) {
            return '<p>Tato aplikace není správně nastavena.</p>';
        } else {
            return '<p>Nefunguje připojení k databázi. Zkuste to prosím znovu později.</p>';
        }
    }
}

function getClientLanguages($choice) {
    return 'hello';
}
