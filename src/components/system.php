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

        $html .= '<li>výběr jazyka <b>' . (getDataValue('choice.allow_change') ? '🟢 ' : '🟡 ne') . 'lze</b> dodatečně změnit <a href="?edit=data&name=choice.allow_change&from=system_state"">(upravit)</a></li>';
        $html .= '<li><a href="?system=confirmation-test">nastavení potvrzovacího e-mailu</a> (funkce je ' . (isConfirmationEmailReady() ? 'zapnutá' : 'vypnutá') . ')</li>';
        $html .= '<li><a href="?system=client-preview">náhled uživatelské části webu</a></li>';

        $mlrl = getDataValue('generated.last_sent');
        $html .= $mlrl ? '<li>hromadný e-mail byl odeslán ' . $mlrl . ' 🟢</li>' : '<li>hromadný e-mail zatím nebyl odeslán 🟡 <a href="?system=send-test">(nastavit odeslání)</a></li>';

        $result = sql('SELECT COUNT(*) FROM `' . prefixTable('students') . '` WHERE `choice` IS NULL', true);
        $number = isset($result[0]) && isset($result[0][0]) ? intval($result[0][0]) : 0;
        $html .= $number ? '<li>ještě ' . $number . ' studentů nemá zvolený jazyk 🟡</li>' : '<li>všichni studenti mají zvolený jazyk 🟢</li>';

        $html .= '</ul>';
        return adminTemplate($html);
    } else if ($view == 'send-test' || $view == 'send-real') {
        $isTest = $view == 'send-test';
        $isFix = isset($_GET['send-fix']) && $_GET['send-fix'];
        $html = '<h1>Hromadné rozeslání úvodního e-mailu</h1>';
        $mlrl = getDataValue('generated.last_sent');
        $html .= '<p><a href=".">zpět</a>';
        $html .= $mlrl ? ' | <i>pozor, hromadný e-mail již byl odeslán (' . $mlrl . ')</i> 🟡' : ' | hromadný e-mail zatím nebyl odeslán 🟢';
        $html .= '</p><p>menu: ';

        if ($isTest) {
            $html .= '<b>odeslání testovacího e-mailu</b> | <a href="?system=send-real">odeslání ostrého e-mailu</a> | <a href="?system=send-real&send-fix=1">opravit ostré odeslání</a>';
        } else if ($isFix) {
            $html .= '<a href="?system=send-test">odeslání testovacího e-mailu</a> | <a href="?system=send-real">odeslání ostrého e-mailu</a> | <b>opravit ostré odeslání</b>';
        } else {
            $html .= '<a href="?system=send-test">odeslání testovacího e-mailu</a> | <b>odeslání ostrého e-mailu</b> | <a href="?system=send-real&send-fix=1">opravit ostré odeslání</a>';
        }

        $html .= '</p>';
        $html .= $isFix ? '<p>Tuto stránku použijte pouze v případě, kdy selže odeslání ostrého e-mailu a e-maily se odešlou jen na některé adresy. </p>' : '';
        $html .= '<h2>' . _t('mailer', 'heading') . ' <a href="?edit=data&name=mailer&from=system_' . $view . '">(upravit)</a></h2><table class="bordered">';
        $placeholder = '<i>(chybí!)</i>';
        $fields = array('host', 'email', 'password');

        foreach ($fields as $field) {
            $value = getDataValue('mailer.' . $field);

            if ($value || $field != 'password') {
                $html .= '<tr><th>' . _t('mailer', $field) . '</th><td>' . ($value ? $value : $placeholder) . '</td></tr>';
            }
        }

        $html .= '</table><h2>' . _t('text', 'heading') . ' <a href="?edit=data&name=text&from=system_' . $view . '">(upravit)</a></h2><table class="bordered">';
        $fields = array('sender', 'subject', 'body');

        foreach ($fields as $field) {
            $value = getDataValue('text.email_' . $field);
            $value = $value ? preg_replace('/\n/', '<br>', $value) : '';
            $html .= '<tr><th>' . _t('text', 'email_' . $field) . '</th><td>' . ($value ? $value : $placeholder) . '</td></tr>';
        }

        $value = getEmailBody(getDataValue('text.email_body'), getEmailDummyData(), false);
        $html .= '<tr><th>' . _t('text', 'email_body') . ' (náhled)</th><td>' . ($value ? $value : $placeholder) . '</td></tr></table>';
        $html .= $isTest
            ? '<h2>Odeslání testovacího e-mailu (jednomu adresátovi)</h2><table><form method="post" action="."><input type="hidden" name="system" value="send-test">'
            : ('<h2>Odeslání ostrého e-mailu (' . ($isFix ? 'vybraným adresátům' : 'všem adresátům') . ') 🟡</h2><table><form method="post" action="."><input type="hidden" name="system" value="send-real">');
        $html .= $isFix ? '<input type="hidden" name="fix" value="1">' : '';

        if (!getDataValue('mailer.password')) {
            $html .= '<tr><th><label for="password">heslo (k e-mailu)</label></th><td><input type="text" name="password" id="password" autocomplete="off" required></td></tr>';
        }

        $disabled = $isFix && !getDataValue('generated.skipped_ids') ? 'disabled' : '';
        $value = $disabled ? null : htmlspecialchars(implode(', ', getListOfEmails($isFix)));
        $html .= $isTest
            ? ('<tr><th><label for="test_address">adresát testovacího e-mailu</label></th><td><input type="text" name="test_address" id="test_address" required></td></tr>'
                . '<tr><th><label for="test_count"><abbr title="vyšší počty používejte pouze u testovacích adres – kvůli spamovým filtrům (rovněž k odesílání použijte testovací server)">'
                . 'počet kopií test. e-mailu</abbr></label></th><td><input type="number" name="test_count" id="test_count" value="1" required></td></tr>')
            : ('<tr><th><label for="adminpass"><abbr title="heslo pro přístup do této administrace">admin. heslo</abbr></label></th><td><input type="password" name="adminpass" id="adminpass" required></td></tr>'
                . '<tr><th>seznam adresátů</th><td>' . ($value ? $value : $placeholder) . '</td></tr>');
        $html .= $isFix ? '<tr><td></td><td><a href="?edit=data&name=generated.skipped_ids&from=system_send-real!send-fix_1">upravit seznam adresátů</a> (do textového pole patří ID studentů oddělená čárkou)</td></tr>' : '';
        $html .= '</table><br><input type="submit" value="Odeslat ' . ($isTest ? 'testovací' : 'ostrý') . ' e-mail" ' . $disabled . '></form>';
        $html .= $isTest ? ' | <a href="?system=test-timeout">otestovat timeout</a>' : '';
        return adminTemplate($html);
    } else if ($view == 'test-timeout') {
        $pageParts = explode('#separator#', adminTemplate('#separator#'), 2);
        @ob_end_clean();
        header('Content-Type: text/html; charset=utf-8');
        echo $pageParts[0];
        echo '<p><a href="?system=send-test">zpět</a></p>';
        echo '<p>Odesílání e-mailů může zabrat až dvě minuty. Tato stránka slouží k vyzkoušení, zda je server i klient konfigurován tak, aby tuto dobu zvládl. Cílem je, aby se níže objevila číslice 120.</p>';
        flush();
        $limit = 120;

        for ($i = 0; $i < $limit; $i++) {
            echo $i + 1 . ' ';
            sleep(1);
            flush();
        }

        echo '<p>Skvěle, test proběhl v pořádku!</p><p><a href="?system=send-test">Pokračovat k odeslání testovacího úvodního e-mailu…</a></p>';
        echo $pageParts[1];
        return '';
    } else if ($view == 'confirmation-test') {
        $html = '<h1>Potvrzovací e-mail</h1><p><a href="?system=state">zpět</a></p><h2>Důležité informace</h2>';
        $html .= '<ul>'
            . '<li>Odeslání potvrzovacího e-mailu zpomaluje odpověď serveru při volbě jazyka uživatelem. Nemá však vliv na rychlost uložení odpovědi do databáze.</li>'
            . '<li>Pro fukčnost zasílání potvrzovacího e-mailu je nutné do databáze uložit heslo k e-mailu (viz níže). Toto heslo je uloženo nezabezpečeně.</li>'
            . '<li>Kromě hesla je nutné uložit i další údaje, všechny jsou uvedeny níže. Na základě těchto údajů je funkce odeslání potvrzovacího e-mailu aktuálně <b>' . (isConfirmationEmailReady() ? 'zapnutá' : 'vypnutá') . '</b>. Reálnou funkčnost odesílání lze však prověřit pouze odesláním testovacího e-mailu pomocí formuláře (rovněž níže).</li>'
            . '<li>Při problémech s odesíláním/doručováním e-mailů je vhodnější použít stránku <a href="?system=send-test">odeslání testovacího úvodního e-mailu</a>, neboť zobrazuje případné chybové hlášky.</li>'
            . '</ul>';
        $html .= '<h2>' . _t('mailer', 'heading') . ' <a href="?edit=data&name=mailer&from=system_' . $view . '">(upravit)</a></h2><table class="bordered">';
        $placeholder = '<i>(chybí!)</i>';
        $fields = array('host', 'email', 'password');

        foreach ($fields as $field) {
            $value = getDataValue('mailer.' . $field);
            $html .= '<tr><th>' . _t('mailer', $field) . '</th><td>' . ($value ? $value : $placeholder) . '</td></tr>';
        }

        $html .= '</table><h2>' . _t('text', 'heading') . ' <a href="?edit=data&name=text&from=system_' . $view . '">(upravit)</a></h2><table class="bordered">';

        $value = getDataValue('text.email_sender');
        $html .= '<tr><th>' . _t('text', 'email_sender') . '</th><td>' . ($value ? $value : $placeholder) . '</td></tr>';

        $html .= '</table><h2>' . _t('choice', 'heading') . ' <a href="?edit=data&name=choice&from=system_' . $view . '">(upravit)</a></h2><table class="bordered">';

        $value = getDataValue('choice.confirmation_send');
        $html .= '<tr><th>' . _t('choice', 'confirmation_send') . '</th><td>' . ($value ? 'ano' : '<i>(nutno zaškrtnout!)</i>') . '</td></tr>';
        $value = getDataValue('choice.confirmation_subject');
        $html .= '<tr><th>' . _t('choice', 'confirmation_subject') . '</th><td>' . ($value ? $value : $placeholder) . '</td></tr>';
        $value = getDataValue('choice.confirmation_body');
        $value = $value ? preg_replace('/\n/', '<br>', $value) : '';
        $html .= '<tr><th>' . _t('choice', 'confirmation_body') . '</th><td>' . ($value ? $value : $placeholder) . '</td></tr>';

        $value = getEmailBody(getDataValue('choice.confirmation_body'), getEmailDummyData(), false);
        $html .= '<tr><th>' . _t('choice', 'confirmation_body') . ' (náhled)</th><td>' . ($value ? $value : $placeholder) . '</td></tr></table>';
        $html .= '<h2>Odeslání testovacího potvrzovacího e-mailu</h2><table><form method="post" action="."><input type="hidden" name="system" value="confirmation-test">';

        $html .= '<tr><th><label for="test_address">adresát testovacího e-mailu</label></th><td><input type="text" name="test_address" id="test_address" required></td></tr>';
        $html .= '</table><br><input type="submit" value="Odeslat testovací e-mail"></form>';
        return adminTemplate($html);
    } else if ($view == 'client-preview') {
        $html = '<h1>Náhled uživatelského zobrazení</h1><p><a href="?system=state">zpět</a>';
        $html .= '<ul><li>Samotný výběr jazyka v tomto náhledu není funkční.</li><li>Návrat na tuto stránku je možný pomocí tlačítka „zpět“ v prohlížeči.</li></ul>';
        $html .= '<form method="get" action=".."><input type="hidden" name="k" value="test_view"><table>';
        $html .= '<tr><td><label for="state">stav přihlašování</label></td><td>'
            . '<select name="state" id="state">'
            . '<option value="current">aktuání</option>'
            . '<option value="0">bez zadaného času zahájení</option>'
            . '<option value="1">před zahájením</option>'
            . '<option value="2">během přihlašování</option>'
            . '<option value="3">po ukončení</option>'
            . '</select>'
            . '</td></tr>';
        $html .= '<tr><td><label for="class">' . _t('form', 'class') . '</label></td><td>' . getClassesSelect(9) . '</td></tr>';
        $html .= '<tr><td><label for="choice">' . _t('form', 'choice') . '</label></td><td>' . getLanguagesSelect('') . '</td></tr>';
        $html .= '<tr><td></td><td><input type="submit" value="Zobrazit náhled"></td></tr></table></form>';
        return adminTemplate($html);
    } else if ($view == 'export' || $view == 'export-csv' || $view == 'export-csv-links') {
        $data = "spisc,e-mail,jmeno,trida,volba\r\n";

        if ($view == 'export-csv-links') {
            $data = "spisc,e-mail,jmeno,trida,odkaz,volba\r\n";
        }

        $studentsTable = sql('SELECT * FROM `' . prefixTable('students') . '`;');
        $languagesArray = getLanguagesArray(true);
        $linkPrefix = getLinkPrefix();

        foreach ($studentsTable as $row) {
            if ($row[6]) {
                $language = isset($languagesArray[$row[6]]) ? $languagesArray[$row[6]] : "($row[6])";
            } else {
                $language = '';
            }

            if ($view != 'export-csv-links') {
                $data .= "$row[1],$row[3],$row[4],$row[5],$language\r\n";
            } else {
                $data .= "$row[1],$row[3],$row[4],$row[5],$linkPrefix$row[2],$language\r\n";
            }
        }

        if ($view == 'export-csv' || $view == 'export-csv-links') {
            header('Content-Type: text/csv; header=present; charset=utf-8');
            return $data;
        }

        $html = '<h1>Exportovat data o studentech</h1><p><a href=".">zpět</a> | <a href="?system=export-csv" download="studenti.csv">stáhnout CSV bez odkazů</a> | <a href="?system=export-csv-links" download="studenti.csv">stáhnout CSV s odkazy</a></p>';
        $html .= '<p>Formát: <code>spisové číslo,e-mail,celé jméno,třída (' . implode('/', getClasses()) . '),vybraný jazyk (značka)</code></p>';
        $html .= '<textarea class="export" readonly>' . $data . '</textarea>';
        return adminTemplate($html);
    } else if ($view == 'wipe') {
        $html = '<h1>Možnosti smazání dat</h1><p><a href=".">zpět</a></p><ul>'
            . '<li><a href="?confirm=wipe-next">' . _t('confirm', 'wipe-next') . '</a> (tato možnost se hodí pro každoroční čištění na konci celého procesu)</li>'
            // . '<li><a href="?confirm=wipe-mailer-password">' . _t('confirm', 'wipe-mailer-password') . '</a></li>'
            . '<li><a href="?confirm=wipe-students">' . _t('confirm', 'wipe-students') . '</a></li>'
            . '<li><a href="?confirm=wipe-languages">' . _t('confirm', 'wipe-languages') . '</a></li>'
            . '<li><a href="?confirm=wipe-data">' . _t('confirm', 'wipe-data') . '</a></li>'
            . '<li><a href="?confirm=wipe-clean">' . _t('confirm', 'wipe-clean') . '</a> (tato možnost z databáze odstraní všechny tři tabulky této aplikace)</li>'
            . '</ul>';
        return adminTemplate($html);
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
        $testCount = !empty($_POST['test_count']) ? $_POST['test_count'] : 1;
        $testCount = ($testCount < 1) ? 1 : $testCount;
        $testCount = ($testCount > 100) ? 100 : $testCount;

        if ($sender && $generalBody && $subject && $host && $email && $password && $testAddress) {
            $mailingList = array();

            for ($i = 0; $i < $testCount; $i++) {
                $sid = $i + 1;

                if ($testCount == 1) {
                    $sid = 123;
                }

                $mailingList[] = getEmailDummyData($testAddress, $sid);
            }

            return mailer($host, $email, $password, $sender, $subject, $generalBody, $mailingList, true);
        } else {
            return adminTemplate('Chyba: některé údaje nebyly vyplněny. <a href="?system=send-test">Zpět k odeslání testovacího e-mailu</a>');
        }
    } else if ($action == 'send-real') {
        $adminPass = isset($_POST['adminpass']) ? $_POST['adminpass'] : null;
        $isFix = !empty($_POST['fix']);
        $button = $isFix ? '<a href="?system=send-real&send-fix=1">Zpět k opravě odeslání ostrého e-mailu</a>' : '<a href="?system=send-real">Zpět k odeslání ostrého e-mailu</a>';

        if ($isFix) {
            $skippedIds = getDataValue('generated.skipped_ids');
            $mailingList = $skippedIds ? sql('SELECT * FROM `' . prefixTable('students') . '` WHERE `id` IN (' . $skippedIds . ');') : null;
        } else {
            $mailingList = sql('SELECT * FROM `' . prefixTable('students') . '`;');
        }

        if ($sender && $generalBody && $subject && $host && $email && $password && !empty($mailingList) && $adminPass) {
            if ($config['adminpass'] === $adminPass) {
                return mailer($host, $email, $password, $sender, $subject, $generalBody, $mailingList, false);
            } else {
                return adminTemplate('Chyba: zadali jste špatné heslo administrátora (jde o heslo, které jste použili pro přístup do této administrace). ' . $button);
            }
        } else {
            return adminTemplate('Chyba: některé údaje nebyly vyplněny. ' . $button);
        }
    } else if ($action == 'confirmation-test') {
        if (isset($_POST['test_address'])) {
            $result = sendConfirmationEmail(getEmailDummyData($_POST['test_address']), 0, true);

            if ($result == 'success') {
                redirectMessage('confirmation-test', 'success', '?system=confirmation-test');
            } else {
                return adminTemplate('Chyba: některé údaje nebyly vyplněny. <a href="?system=confirmation-test">Zpět k odeslání testovacího potvrzovacího e-mailu</a>');
            }
        }
    }
}

function getClientDummyData($class, $choice) {
    $data = getEmailDummyData('jan.novak@example.com');
    $data['class'] = $class;
    $data['choice'] = $choice;
    return $data;
}

function getEmailDummyData($email = '', $sid = 123) {
    return array('id' => 0, 'email' => $email, 'key' => 'asdfghjkl12345', 'sid' => $sid, 'name' => 'Jan Novák', 'class' => 5, 'choiceDummy' => 'italština');
}

function getEmailBody($generalBody, $recipient, $forEmail = true, $isClient = false) {
    $levelsUp = $isClient ? 1 : 2;
    $linkPrefix = getLinkPrefix($levelsUp);
    $choice = !empty($recipient['choice']) ? $recipient['choice'] : null;
    $languageArray = getLanguagesArray(false, false);
    $chosenLanguage = ($choice && isset($languageArray[$choice])) ? $languageArray[$choice] : '';
    $chosenLanguage = isset($recipient['choiceDummy']) ? $recipient['choiceDummy'] : $chosenLanguage;
    $replacementData = array('odkaz' => $linkPrefix . $recipient['key'], 'spisc' => $recipient['sid'], 'jmeno' => $recipient['name'], 'trida' => $recipient['class'], 'volba' => $chosenLanguage);
    $body = $generalBody;
    $body = $body ? $body : '';
    $body = $forEmail ? $body : preg_replace('/\n/', '<br>', $body);

    foreach ($replacementData as $key => $value) {
        $body = str_replace('(' . $key . ')', $value, $body);
    }

    return $body;
}

function getListOfEmails($isFix) {
    $emailTable = $isFix
        ? sql('SELECT `email` FROM `' . prefixTable('students') . '` WHERE `id` IN (' . getDataValue('generated.skipped_ids') . ');')
        : sql('SELECT `email` FROM `' . prefixTable('students') . '`;');
    return array_column($emailTable, 'email');
}

function mailer($host, $email, $password, $sender, $subject, $generalBody, $mailingList, $isTest = true) {
    $successfulIds = array();
    $total = count($mailingList);
    $successful = 0;
    $letter = $total === 1 ? 'u' : 'ů';
    $pageParts = explode('#separator#', adminTemplate('#separator#'), 2);
    @ob_end_clean();
    header('Content-Type: text/html; charset=utf-8');
    echo $pageParts[0];
    echo '<p>Probíhá odesílání ' . $total . ' e-mail' . $letter . '… (Počkejte, až se dokončí načítání stránky.)</p>';
    echo '<table class="bordered"><tr><th>pořadí</th><th>id</th><th>jméno</th><th>e-mail</th><th>výsledek</th></tr>';

    try {
        $mail = getPHPMailerInstance($host, $email, $password, $sender, $subject);
        // https://github.com/PHPMailer/PHPMailer/wiki/Sending-to-lists
        $mail->SMTPKeepAlive = true;

        foreach ($mailingList as $no => $recipient) {
            flush();
            echo '<tr><td>' . ($no + 1) . '/' . $total . '</td><td>' . $recipient['id'] . '</td><td>' . $recipient['name'] . '</td><td>' . $recipient['email'] . '</td><td>';

            try {
                $mail->addAddress($recipient['email']);
            } catch (Exception $e) {
                echo '🔴 e-mail nebyl odeslán, špatná adresa</td></tr>';
                $mail->clearAddresses();
                continue;
            }

            try {
                $mail->Body = getEmailBody($generalBody, $recipient);
                $mail->send();
            } catch (Exception $e) {
                echo '🔴 e-mail nebyl odeslán, chyba odesílání: ' . translateErrorMessage($mail->ErrorInfo) . ' / ' . $mail->ErrorInfo . '</td></tr>';
                $mail->clearAddresses();
                $mail->getSMTPInstance()->reset();
                continue;
            }

            echo '🟢 e-mail byl úspěšně odeslán</td></tr>';
            $successful++;
            $successfulIds[] = $recipient['id'];
            $mail->clearAddresses();
        }
    } catch (Exception $e) {
        echo '<tr><td colspan="2">🔴 chyba</td><td>' . translateErrorMessage($mail->ErrorInfo) . '</td><td>' . $mail->ErrorInfo . '</td></tr>';
    }

    echo '</table>';

    if ($isTest) {
        if ($successful) {
            $result = 'Testovací e-mail byl úspěšně odeslán. <a href="?system=send-real">Pokračovat k odeslání <b>ostrého</b> e-mailu…</a>';
        } else {
            $result = 'Testovací e-mail nebyl odeslán. <a href="?system=send-test">Zpět k odeslání testovacího e-mailu…</a>';
        }
    } else {
        $currentTime = new DateTime('now');

        if ($successful == 1) {
            $result = 'Ostrý e-mail nebyl odeslán nikomu, zkuste znovu odeslat testovací e-mail. <a href="?system=send-test">Zpět k odeslání <b>testovacího</b> e-mailu…</a>';
        } else if ($successful === $total) {
            $result = 'Ostrý e-mail byl úspěšně odeslán na všechny zadané e-mailové adresy. Výborně! <a href=".">Pokračovat zpět do administrace…</a>';
            setDataValue('generated.last_sent', $currentTime->format('j. n. Y \v G:i'));
            setDataValue('generated.skipped_ids', '');
        } else {
            $allIds = array_column($mailingList, 'id');
            $skippedIds = array_diff($allIds, $successfulIds);
            $result = 'Ostrý e-mail byl odeslán pouze na ' . $successful . ' z ' . $total . ' e-mailových adres.</p><p>Identifikátory studentů jimž nebyl odeslán e-mail: '
                . implode(',', $skippedIds) . '</p><p>Identifikátory byly uloženy do databáze. Jakmile zjistíte, kde je chyba, a opravíte ji, použijte odkaz <i>opravit ostré odeslání</i>, který najdete nahoře na stránce <i>rozeslání e-mailů</i>.</p>'
                . '<p>Nyní můžete <a href="?list=students">pokračovat na tabulku studentů…</a>';
            setDataValue('generated.last_sent', $currentTime->format('j. n. Y \v G:i'));
            setDataValue('generated.skipped_ids', implode(',', $skippedIds));
        }
    }

    echo "<p>$result</p>";
    echo $pageParts[1];
    return '';
}

function translateErrorMessage($errorInfo) {
    $strings = include(__DIR__ . '/../mailer/Strings.php');
    $key = array_search($errorInfo, $strings[0]);

    if ($key && isset($strings[1][$key])) {
        return $strings[1][$key];
    }

    return $errorInfo;
}
