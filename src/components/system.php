<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function systemPage($view) {
    if ($view == 'state') {
        $html = '<h1>Stav systému</h1><p><a href=".">zpět</a></p><ul>';
        $choiceStates = array('zatím nebylo naplánováno 🔴', 'je naplánováno ⌛', 'probíhá', 'bylo ukončeno ✅');
        $html .= '<li>přihlašování ' . (isChoiceOpen() ? 'probíhá 🟢' : 'neprobíhá, ' . $choiceStates[choiceState()]) . ' <a href="?edit=data&name=time&from=system_state">(upravit)</a></li>';
        $weekdays = array('neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota');
        $classes = getClasses();
        $states = array('🔴 chybí', '🟢 v pořádku');

        foreach ($classes as $class) {
            $html .= '<li>' . $class . '. třída<ul>';

            $result = sql('SELECT COUNT(*) FROM `' . prefixTable('students') . '` WHERE `class`=?', true, array($class));
            $number = isset($result[0]) && isset($result[0][0]) ? intval($result[0][0]) : 0;
            $state = $states[$number > 0];
            $html .= '<li>' . $number . ' studentů – ' . $state . ' <a href="?list=students">(upravit)</a></li>';

            $result = sql('SELECT COUNT(*) FROM `' . prefixTable('languages') . '` WHERE `class`=?', true, array($class));
            $number = isset($result[0]) && isset($result[0][0]) ? intval($result[0][0]) : 0;
            $state = $states[$number > 1];
            $html .= '<li>' . $number . ' jazyky – ' . $state . ' <a href="?list=languages">(upravit)</a></li></ul>';
        }

        $lastProcessedTime = null;

        foreach (array('from', 'to') as $fromTo) {
            $time = getDataValue('time.' . $fromTo);
            $states = array(
                'from' => array('🔴 chybí, je nutné ho doplnit, aby přihlašování mohlo začít ', '🟢 v pořádku'),
                'to' => array('🟡 není nastaven, přihlašování nebude ukončeno', '🟢 v pořádku', '🔴 čas ukončení není po čase spuštění')
            );

            if ($time) {
                $processedTime = new DateTime($time);
                $today = new DateTime('today');
                $days = $processedTime->diff($today)->days;
                $beforeAfter = $today < $processedTime ? 'bude za <abbr title="počítáno od dnešních 0:00">' . $days . ' dnů</abbr>' : 'bylo před <abbr title="počítáno od dnešních 0:00">' . $days . ' dny</abbr>';
                $html .= '<li>' . _t('time', $fromTo) . ': ' . $weekdays[$processedTime->format('w')] . ' ' . $processedTime->format('j. n. Y (G:i)') . ', což ' . $beforeAfter . ' – '
                    . ($lastProcessedTime < $processedTime ? $states[$fromTo][1] : $states[$fromTo][2]);
                $lastProcessedTime = $processedTime;
            } else {
                $html .= '<li>' . _t('time', $fromTo) . ' – ' . $states[$fromTo][0];
            }

            $html .= ' <a href="?edit=data&name=time.' . $fromTo . '&from=system_state">(upravit)</a></li>';
        }

        $mlrl = getDataValue('other.last_sent');
        $html .= $mlrl ? '<li>hromadný e-mail byl odeslán ' . $mlrl . ' 🟢</li>' : '<li>hromadný e-mail zatím nebyl odeslán 🟡 <a href="?system=send-test">(nastavit odeslání)</a></li>';

        $result = sql('SELECT COUNT(*) FROM `' . prefixTable('students') . '` WHERE `choice` IS NULL', true);
        $number = isset($result[0]) && isset($result[0][0]) ? intval($result[0][0]) : 0;
        $html .= $number ? '<li>ještě ' . $number . ' studentů nemá zvolený jazyk 🟡</li>' : '<li>všichni studenti mají zvolený jazyk 🟢</li>';
        // $html .= '<li><a href="">zobrazit náhled aktuálního stavu uživatelské části webu</a></li>';
        $html .= '</ul>';
        return adminTemplate($html);
    } else if ($view == 'send-test' || $view == 'send-real') {
        $isTest = $view == 'send-test';
        $html = '<h1>Hromadné rozeslání e-mailů</h1><p><a href=".">zpět</a>';
        $mlrl = getDataValue('other.last_sent');
        $html .= $mlrl ? ' | <i>pozor, hromadný e-mail již byl odeslán (' . $mlrl . ')</i>' : '';
        $html .= $isTest ? ' | <a href="?system=send-real">zobrazit formulář k odeslání ostrého e-mailu</a>' : ' | <a href="?system=send-test">zobrazit formulář k odeslání testovacího e-mailu</a>';
        $html .= '</p>';
        $html .= '<h2>' . _t('mailer', 'heading') . ' <a href="?edit=data&name=mailer&from=system_' . $view . '">(upravit)</a></h2><table>';
        $placeholder = '<i>(chybí!)</i>';
        $fields = array('host', 'email', 'password');

        foreach ($fields as $field) {
            $value = getDataValue('mailer.' . $field);

            if ($value || $field != 'password') {
                $html .= '<tr><th>' . _t('mailer', $field) . '</th><td>' . ($value ? $value : $placeholder) . '</td></tr>';
            }
        }

        $html .= '</table><h2>' . _t('text', 'heading') . ' <a href="?edit=data&name=text&from=system_' . $view . '">(upravit)</a></h2><table>';
        $fields = array('sender', 'subject', 'body');

        foreach ($fields as $field) {
            $value = preg_replace('/\n/', '<br>', getDataValue('text.email_' . $field));
            $html .= '<tr><th>' . _t('text', 'email_' . $field) . '</th><td>' . ($value ? $value : $placeholder) . '</td></tr>';
        }

        $value = getEmailBodyPreview();
        $html .= '<tr><th>' . _t('text', 'email_body') . ' (náhled)</th><td>' . ($value ? $value : $placeholder) . '</td></tr></table>';
        $html .= $isTest
            ? '<h2>Odeslání testovacího e-mailu (jednomu adresátovi)</h2><table><form method="post" action="."><input type="hidden" name="system" value="send-test">'
            : '<h2>Odeslání ostrého e-mailu (všem adresátům) 🟡</h2><table><form method="post" action="."><input type="hidden" name="system" value="send-real">';

        if (!getDataValue('mailer.password')) {
            $html .= '<tr><th><label for="password">heslo (k e-mailu)</label></th><td><input type="text" name="password" id="password" autocomplete="off"></td></tr>';
        }

        $html .= $isTest
            ? '<tr><th><label for="test_address">adresát testovacího e-mailu</label></th><td><input type="text" name="test_address" id="test_address"></td></tr>'
            : '<tr><th>seznam adresátů</th><td>' . implode(', ', getListOfEmails()) . '</td></tr>';
        $html .= '</table><br><input type="submit" value="Odeslat ' . ($isTest ? 'testovací' : 'ostrý') . ' e-mail"></form>';
        $html .= $isTest ? ' | <a href="?system=send-real">zobrazit formulář k odeslání ostrého e-mailu</a>' : ' | <a href="?system=send-test">zobrazit formulář k odeslání testovacího e-mailu</a>';
        return adminTemplate($html);
    } else if ($view == 'export') {
    } else if ($view == 'wipe') {
    }
}

function systemAction($action) {
    if ($action == 'send-test') {
        $sender = getDataValue('text.email_sender');
        $body = getEmailBodyPreview(true);
        $subject = getDataValue('text.email_subject');
        $host = getDataValue('mailer.host');
        $email = getDataValue('mailer.email');
        $password = !empty($_POST['password']) ? $_POST['password'] : getDataValue('mailer.password');
        $testAddress = !empty($_POST['test_address']) ? $_POST['test_address'] : null;

        if ($sender && $body && $subject && $host && $email && $password && $testAddress) {
            // Create an instance; passing `true` enables exceptions
            $mail = new PHPMailer(true);

            try {
                // Server settings
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
                $mail->isSMTP();                                            //Send using SMTP
                $mail->Host       = $host;                                  //Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                $mail->Username   = $email;                                 //SMTP username
                $mail->Password   = $password;                              //SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
                $mail->CharSet    = 'UTF-8';

                // Recipients
                $mail->setFrom($email, $sender);
                $mail->addAddress($testAddress);

                // Content
                $mail->Subject = $subject;
                $mail->Body    = $body;

                $mail->send();
                redirectMessage('send-test', 'success', '?system=send-test');
            } catch (Exception $e) {
                return adminTemplate('Chyba: ' . $mail->ErrorInfo . ' <br><a href="?system=send-test">Zpět k odeslání testovacího e-mailu</a>');
            }
        } else {
            return adminTemplate('Chyba: některé údaje nebyly vyplněny. <a href="?system=send-test">Zpět k odeslání testovacího e-mailu</a>');
        }
    } else if ($action == 'send-real') {
        // https://github.com/PHPMailer/PHPMailer/wiki/Sending-to-lists
        // evidovat čas odeslání
        // ošetřit jednu neplatnou adresu nebo něco podobného
    }
}

function getEmailBodyPreview($forEmail = false) {
    $linkPrefix = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . '?k=';
    $dummyData = array('odkaz' => $linkPrefix . 'asdfghjkl12345', 'spisc' => 123, 'jmeno' => 'Jan Novák', 'trida' => 5);
    $bodyPreview = getDataValue('text.email_body');
    $bodyPreview = $forEmail ? $bodyPreview : preg_replace('/\n/', '<br>', $bodyPreview);

    foreach ($dummyData as $key => $value) {
        $bodyPreview = str_replace('(' . $key . ')', $value, $bodyPreview);
    }

    return $bodyPreview;
}

function getListOfEmails() {
    $emailTable = sql('SELECT `email` FROM `' . prefixTable('students') . '`;');
    $retArr = array();

    foreach ($emailTable as $row) {
        $retArr[] = $row['email'];
    }

    return $retArr;
}
