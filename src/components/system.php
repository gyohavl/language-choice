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
        $isFix = isset($_GET['send-fix']) && $_GET['send-fix'];
        $html = '<h1>Hromadné rozeslání e-mailů</h1>';
        $mlrl = getDataValue('other.last_sent');
        $html .= '<p><a href=".">zpět</a>';
        $html .= $mlrl ? ' | <i>pozor, hromadný e-mail již byl odeslán (' . $mlrl . ')</i> 🟡' : ' | hromadný e-mail zatím nebyl odeslán 🟢';
        $html .= '</p><p>menu: ';

        if ($isTest) {
            $html .= '<b>odeslání testovacího e-mailu</b> | <a href="?system=send-real">odeslání ostrého e-mailu</a> | <a href="?system=send-real&send-fix=1">opravit ostré odeslání</a>';
        } else if($isFix) {
            $html .= '<a href="?system=send-test">odeslání testovacího e-mailu</a> | <a href="?system=send-real">odeslání ostrého e-mailu</a> | <b>opravit ostré odeslání</b>';
        } else {
            $html .= '<a href="?system=send-test">odeslání testovacího e-mailu</a> | <b>odeslání ostrého e-mailu</b> | <a href="?system=send-real&send-fix=1">opravit ostré odeslání</a>';
        }

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

        $value = getEmailBody(getDataValue('text.email_body'), getEmailDummyData(), false);
        $html .= '<tr><th>' . _t('text', 'email_body') . ' (náhled)</th><td>' . ($value ? $value : $placeholder) . '</td></tr></table>';
        $html .= $isTest
            ? '<h2>Odeslání testovacího e-mailu (jednomu adresátovi)</h2><table><form method="post" action="."><input type="hidden" name="system" value="send-test">'
            : '<h2>Odeslání ostrého e-mailu (všem adresátům) 🟡</h2><table><form method="post" action="."><input type="hidden" name="system" value="send-real">';

        if (!getDataValue('mailer.password')) {
            $html .= '<tr><th><label for="password">heslo (k e-mailu)</label></th><td><input type="text" name="password" id="password" autocomplete="off" required></td></tr>';
        }

        $html .= $isTest
            ? '<tr><th><label for="test_address">adresát testovacího e-mailu</label></th><td><input type="text" name="test_address" id="test_address" required></td></tr>'
            : ('<tr><th><label for="adminpass"><abbr title="heslo pro přístup do této administrace">admin. heslo</abbr></label></th><td><input type="password" name="adminpass" id="adminpass" required></td></tr>'
                . '<tr><th>seznam adresátů</th><td>' . htmlspecialchars(implode(', ', getListOfEmails())) . '</td></tr>');
        $html .= '</table><br><input type="submit" value="Odeslat ' . ($isTest ? 'testovací' : 'ostrý') . ' e-mail"></form>';
        return adminTemplate($html);
    } else if ($view == 'export') {
        // ošetřit neexistující jazyk
    } else if ($view == 'wipe') {
    }
}

function systemAction($action) {
    global $config;

    if ($action == 'send-test' || $action == 'send-real') {
        $host = getDataValue('mailer.host');
        $email = getDataValue('mailer.email');
        $password = !empty($_POST['password']) ? $_POST['password'] : getDataValue('mailer.password');
        $sender = getDataValue('text.email_sender');
        $subject = getDataValue('text.email_subject');
        $generalBody = getDataValue('text.email_body');
    }

    if ($action == 'send-test') {
        $testAddress = !empty($_POST['test_address']) ? $_POST['test_address'] : null;

        if ($sender && $generalBody && $subject && $host && $email && $password && $testAddress) {
            $mailingList = array(getEmailDummyData($testAddress));
            return mailer($host, $email, $password, $sender, $subject, $generalBody, $mailingList, true);
        } else {
            return adminTemplate('Chyba: některé údaje nebyly vyplněny. <a href="?system=send-test">Zpět k odeslání testovacího e-mailu</a>');
        }
    } else if ($action == 'send-real') {
        $adminPass = isset($_POST['adminpass']) ? $_POST['adminpass'] : null;
        $mailingList = sql('SELECT * FROM `' . prefixTable('students') . '`;');

        if ($sender && $generalBody && $subject && $host && $email && $password && !empty($mailingList) && $adminPass) {
            if ($config['adminpass'] === $adminPass) {
                return mailer($host, $email, $password, $sender, $subject, $generalBody, $mailingList, false);
            } else {
                return adminTemplate('Chyba: zadali jste špatné heslo administrátora (jde o heslo, které jste použili pro přístup do této administrace). <a href="?system=send-real">Zpět k odeslání ostrého e-mailu</a>');
            }
            // https://github.com/PHPMailer/PHPMailer/wiki/Sending-to-lists
            // generovat seznam adres, na které byl e-mail odeslán
            // evidovat čas odeslání
            // ošetřit jednu neplatnou adresu nebo něco podobného
        } else {
            return adminTemplate('Chyba: některé údaje nebyly vyplněny. <a href="?system=send-real">Zpět k odeslání ostrého e-mailu</a>');
        }
    }
}

function getEmailDummyData($email = '') {
    return array('id' => 0, 'email' => $email, 'key' => 'asdfghjkl12345', 'sid' => 123, 'name' => 'Jan Novák', 'class' => 5);
}

function getEmailBody($generalBody, $recipient, $forEmail = true) {
    $linkPrefix = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . '?k=';
    $replacementData = array('odkaz' => $linkPrefix . $recipient['key'], 'spisc' => $recipient['sid'], 'jmeno' => $recipient['name'], 'trida' => $recipient['class']);
    $body = $generalBody;
    $body = $forEmail ? $body : preg_replace('/\n/', '<br>', $body);

    foreach ($replacementData as $key => $value) {
        $body = str_replace('(' . $key . ')', $value, $body);
    }

    return $body;
}

function getListOfEmails() {
    $emailTable = sql('SELECT `email` FROM `' . prefixTable('students') . '`;');
    return array_column($emailTable, 'email');
}

function mailer($host, $email, $password, $sender, $subject, $generalBody, $mailingList, $isTest = true) {
    // Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);
    $successfulIds = array();
    $total = count($mailingList);
    $successful = 0;
    $letter = $total === 1 ? 'u' : 'ů';
    $html = '<p>Probíhá odesílání ' . $total . ' e-mail' . $letter . '…</p>';
    $html .= '<table class="bordered"><tr><th>id</th><th>jméno</th><th>e-mail</th><th>výsledek</th></tr>';

    try {
        // Server settings
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = $host;                                  // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = $email;                                 // SMTP username
        $mail->Password   = $password;                              // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            // Enable implicit TLS encryption
        $mail->Port       = 465;                                    // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($email, $sender);
        $mail->Subject = $subject;

        foreach ($mailingList as $recipient) {
            $html .= '<tr><td>' . $recipient['id'] . '</td><td>' . $recipient['name'] . '</td><td>' . $recipient['email'] . '</td><td>';

            try {
                $mail->addAddress($recipient['email']);
            } catch (Exception $e) {
                // $skippedIds[] = $recipient['id'];
                $html .= '🔴 e-mail nebyl odeslán, špatná adresa</td></tr>';
                $mail->clearAddresses();
                continue;
            }

            try {
                $mail->Body = getEmailBody($generalBody, $recipient);
                $mail->send();
            } catch (Exception $e) {
                // $skippedIds[] = $recipient['id'];
                $html .= '🔴 e-mail nebyl odeslán, chyba odesílání: ' . translateErrorMessage($mail->ErrorInfo) . ' / ' . $mail->ErrorInfo . '</td></tr>';
                $mail->clearAddresses();
                continue;
            }

            $html .= '🟢 e-mail byl úspěšně odeslán</td></tr>';
            $successful++;
            $successfulIds[] = $recipient['id'];
            $mail->clearAddresses();
        }
    } catch (Exception $e) {
        $html .= '<tr><td>Chyba</td><td>' . translateErrorMessage($mail->ErrorInfo) . '</td><td>' . $mail->ErrorInfo . '</td></tr>';
    }

    $html .= '</table>';

    if ($isTest) {
        if ($successful) {
            $result = 'Testovací e-mail byl úspěšně odeslán. <a href="?system=send-real">Pokračovat k odeslání <b>ostrého</b> e-mailu…</a>';
        } else {
            $result = 'Testovací e-mail nebyl odeslán. <a href="?system=send-test">Zpět k odeslání testovacího e-mailu…</a>';
        }
    } else {
        $allIds = array_column($mailingList, 'id');
        $skippedIds = array_diff($allIds, $successfulIds);

        if ($successful == 1) {
            $result = 'Ostrý e-mail nebyl odeslán nikomu, zkuste znovu odeslat testovací e-mail. <a href="?system=send-test">Zpět k odeslání <b>testovacího</b> e-mailu…</a>';
        } else if ($successful === $total) {
            $result = 'Ostrý e-mail byl úspěšně odeslán na všechny zadané e-mailové adresy. Výborně! <a href=".">Pokračovat zpět do administrace…</a>';
        } else {
            $result = 'Ostrý e-mail byl odeslán pouze na ' . $successful . ' z ' . $total . ' e-mailových adres.</p><p>Identifikátory studentů jimž nebyl odeslán e-mail: '
                . implode(',', $skippedIds) . '</p><p>Identifikátory byly uloženy do databáze. Jakmile zjistíte, kde je chyba, a opravíte ji, použijte odkaz <i>opravit ostré odeslání</i>, který najdete nahoře na stránce <i>rozeslání e-mailů</i>.</p>'
                . '<p>Nyní můžete <a href="?list=students">pokračovat na tabulku studentů…</a>';
        }
    }

    // implementovat keep alive
    // uložit datum odeslání
    // implementovat opravdu odeslaného ostrého e-mailu

    // $html .= '<p>Odesílání bylo dokončeno. Bylo odesláno ' . $successful . ' z ' . $total . ' e-mailů. <a href=".">Pokračovat zpět do administrace…</a></p>';
    $html .= "<p>$result</p>";
    return adminTemplate($html);
}

function translateErrorMessage($errorInfo) {
    $strings = include(__DIR__ . '/../mailer/Strings.php');
    $key = array_search($errorInfo, $strings[0]);

    if ($key && isset($strings[1][$key])) {
        return $strings[1][$key];
    }

    return $errorInfo;
}
