<?php

$PHPMAILER_LANG_original = [
    'authenticate' => 'SMTP Error: Could not authenticate.',
    'buggy_php' => 'Your version of PHP is affected by a bug that may result in corrupted messages.' .
        ' To fix it, switch to sending using SMTP, disable the mail.add_x_header option in' .
        ' your php.ini, switch to MacOS or Linux, or upgrade your PHP to version 7.0.17+ or 7.1.3+.',
    'connect_host' => 'SMTP Error: Could not connect to SMTP host.',
    'data_not_accepted' => 'SMTP Error: data not accepted.',
    'empty_message' => 'Message body empty',
    'encoding' => 'Unknown encoding: ',
    'execute' => 'Could not execute: ',
    'extension_missing' => 'Extension missing: ',
    'file_access' => 'Could not access file: ',
    'file_open' => 'File Error: Could not open file: ',
    'from_failed' => 'The following From address failed: ',
    'instantiate' => 'Could not instantiate mail function.',
    'invalid_address' => 'Invalid address: ',
    'invalid_header' => 'Invalid header name or value',
    'invalid_hostentry' => 'Invalid hostentry: ',
    'invalid_host' => 'Invalid host: ',
    'mailer_not_supported' => ' mailer is not supported.',
    'provide_address' => 'You must provide at least one recipient email address.',
    'recipients_failed' => 'SMTP Error: The following recipients failed: ',
    'signing' => 'Signing Error: ',
    'smtp_code' => 'SMTP code: ',
    'smtp_code_ex' => 'Additional SMTP info: ',
    'smtp_connect_failed' => 'SMTP connect() failed.',
    'smtp_detail' => 'Detail: ',
    'smtp_error' => 'SMTP server error: ',
    'variable_set' => 'Cannot set or reset variable: ',
];

$PHPMAILER_LANG = array();

/**
 * Czech PHPMailer language file: refer to English translation for definitive list
 * @package PHPMailer
 */

$PHPMAILER_LANG['authenticate']         = 'Chyba SMTP: Autentizace selhala.';
$PHPMAILER_LANG['connect_host']         = 'Chyba SMTP: Nelze navázat spojení se SMTP serverem.';
$PHPMAILER_LANG['data_not_accepted']    = 'Chyba SMTP: Data nebyla přijata.';
$PHPMAILER_LANG['empty_message']        = 'Prázdné tělo zprávy';
$PHPMAILER_LANG['encoding']             = 'Neznámé kódování: ';
$PHPMAILER_LANG['execute']              = 'Nelze provést: ';
$PHPMAILER_LANG['file_access']          = 'Nelze získat přístup k souboru: ';
$PHPMAILER_LANG['file_open']            = 'Chyba souboru: Nelze otevřít soubor pro čtení: ';
$PHPMAILER_LANG['from_failed']          = 'Následující adresa odesílatele je nesprávná: ';
$PHPMAILER_LANG['instantiate']          = 'Nelze vytvořit instanci emailové funkce.';
$PHPMAILER_LANG['invalid_address']      = 'Neplatná adresa: ';
$PHPMAILER_LANG['invalid_hostentry']    = 'Záznam hostitele je nesprávný: ';
$PHPMAILER_LANG['invalid_host']         = 'Hostitel je nesprávný: ';
$PHPMAILER_LANG['mailer_not_supported'] = ' mailer není podporován.';
$PHPMAILER_LANG['provide_address']      = 'Musíte zadat alespoň jednu emailovou adresu příjemce.';
$PHPMAILER_LANG['recipients_failed']    = 'Chyba SMTP: Následující adresy příjemců nejsou správně: ';
$PHPMAILER_LANG['signing']              = 'Chyba přihlašování: ';
$PHPMAILER_LANG['smtp_connect_failed']  = 'SMTP Connect() selhal.';
$PHPMAILER_LANG['smtp_error']           = 'Chyba SMTP serveru: ';
$PHPMAILER_LANG['variable_set']         = 'Nelze nastavit nebo změnit proměnnou: ';
$PHPMAILER_LANG['extension_missing']    = 'Chybí rozšíření: ';

$PHPMAILER_LANG['authenticate']         = 'Chyba SMTP: Autentizace selhala. <i>Zadali jste špatný e-mail odesílatele nebo heslo (k e-mailu).</i>';

return array($PHPMAILER_LANG_original, $PHPMAILER_LANG);
