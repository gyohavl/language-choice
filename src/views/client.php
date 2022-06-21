<?php
include(__DIR__ . '/../main.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo fillTemplate('client', getClientView());

function getClientView() {
    if (configExists() && dbConnectionOk()) {
        if (!empty($_GET['k'])) {
            $key = $_GET['k'];
            $data = sql('SELECT * FROM `' . prefixTable('students') . '` WHERE `key`=?;', true, array($key));

            if ($_GET['k'] == 'test_view' && isset($_GET['choice']) && isset($_GET['class'])) {
                $data[0] = getClientDummyData($_GET['class'], $_GET['choice']);
            }

            if (isset($data[0])) {
                $result = isset($_GET['result']) ? $_GET['result'] : null;
                $clientLanguages = getClientLanguages($key, $data[0]['class'], $data[0]['choice'], $result);

                if (isset($_GET['ajax'])) {
                    echo $clientLanguages;
                    exit;
                }

                $fill = $data[0];
                $fill['info'] = getDataValue('text.client');
                $fill['languages'] = $clientLanguages;
                return fillTemplate('client-content', $fill);
            } else {
                $text = '<p>Tento přihlašovací odkaz je neplatný. Pokud si myslíte, že jde o chybu, kontaktujte správce aplikace.</p>';

                if (isset($_GET['ajax'])) {
                    echo $text;
                    exit;
                }

                return $text;
            }
        } else if (!empty($_POST['key']) && !empty($_POST['language'])) {
            $result = setClientLanguage($_POST['key'], $_POST['language']);
            return clientResultMessage($_POST['key'], $result, isset($_POST['ajax']));
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

function getClientLanguages($key, $class, $choice, $result) {
    $weekdays = array('v neděli', 'v pondělí', 'v úterý', 've středu', 've čtvrtek', 'v pátek', 'v sobotu');
    $months = array('', 'ledna', 'února', 'března', 'dubna', 'května', 'června', 'července', 'srpna', 'září', 'října', 'listopadu', 'prosince');
    $choiceState = choiceState();

    if ($key == 'test_view') {
        $choiceState = (isset($_GET['state']) && $_GET['state'] != 'current') ? $_GET['state'] : $choiceState;
    }

    switch ($choiceState) {
        case 0:
            return '<p>Možnost volby jazyka zatím není k dispozici. Termín zpřístupnění bude brzy oznámen.</p>';

        case 1:
            $timeFrom = getDataValue('time.from');
            $processedTimeFrom = $timeFrom ? new DateTime($timeFrom) : new DateTime('next year'); // test_view without starting date
            $today = new DateTime('now');
            $remainingTime = $processedTimeFrom->diff($today)->days;
            $html = '<p>Možnost volby jazyka bude zpřístupněna ' . $weekdays[$processedTimeFrom->format('w')] . ' '
                . $processedTimeFrom->format('j') . '. ' . $months[$processedTimeFrom->format('n')] . ' ' . $processedTimeFrom->format('Y \v G:i')
                . '.</p>';
            $html .= $remainingTime ? '' : ('<span id="refreshBefore" data-time="' . $processedTimeFrom->format('c') . '">V ten čas nezapomeňte <a href="?k=' . $key . '">obnovit stránku</a>.</span>');
            return $html;

        case 2:
            $languagesTable = sql('SELECT * FROM `' . prefixTable('languages') . '` WHERE `class`=?;', true, array($class));
            $languageArray = getLanguagesArray(false, false, $languagesTable);
            $chosenLanguage = ($choice && isset($languageArray[$choice])) ? $languageArray[$choice] : null;
            $allowChange = (bool)getDataValue('choice.allow_change');
            $html = '';

            if ($result) {
                if ($result == 'changed' || $result == 'chosen' || $result == 'already-chosen') {
                    $html .= '<div class="success">' . _t('client-result', $result) . '</div>';
                } else {
                    $html .= '<div class="error">' . _t('client-result', 'error') . _t('client-result', $result) . '</div>';
                }
            }

            if ($chosenLanguage) {
                $html .= '<p>Vybraný jazyk: <b>' . $chosenLanguage . '</b></p>';
                $html .= $allowChange ? '<p>Svou volbu můžete změnit kliknutím na tlačítko <i>vybrat</i> u jiného jazyka.</p>' : '';
            } else {
                $html .= '<p>Klepnutím na tlačítko <i>vybrat</i> zvolte jazyk.</p>';
                $html .= $allowChange ? '<p>Svou volbu budete moci v případě potřeby ještě změnit.</p>' : '<p>Pozor, svou volbu již nebudete moci změnit.</p>';
            }

            if (!$chosenLanguage || $allowChange) {
                $html .= getClientLanguagesTable($languagesTable, $key, $choice);
                $html .= '<p id="refreshDuring">Počty volných míst nemusí být aktuální, pro zobrazení momentálního stavu <a href="?k=' . $key . '">obnovte stránku</a>.</p>';
            }

            return $html;

        case 3:
            $languagesTable = sql('SELECT * FROM `' . prefixTable('languages') . '` WHERE `class`=?;', true, array($class));
            $languageArray = getLanguagesArray(false, false, $languagesTable);
            $chosenLanguage = ($choice && isset($languageArray[$choice])) ? $languageArray[$choice] : 'žádný';
            return '<p>Možnost volby jazyka již není k dispozici.</p><p>Vybraný jazyk: <b>' . $chosenLanguage . '</b></p>';

        default:
            # code...
            break;
    }
}

function getClientLanguagesTable($languagesTable, $key, $choice) {
    $html = '<form method="post" action="."><input type="hidden" name="key" value="' . $key . '"><table class="bordered"><tbody>';

    foreach ($languagesTable as $row) {
        $numberOfChoices = getLanguageOccupancy($row['class'], $row['id']);
        $available = $row['limit'] - $numberOfChoices;

        if ($choice == $row['id']) {
            $button = 'vybráno';
        } else {
            if ($available > 0) {
                $button = '<button type="submit" name="language" value="' . $row['id'] . '" onclick="choose(this, event, \''
                    . $key . '\', ' . $row['id'] . ');">vybrat</button><span class="progress-text"></span>';
            } else {
                $button = '';
            }
        }

        $availableText = 'volných míst';

        if ($available == 1) {
            $availableText = 'volné místo';
        } else if ($available != 0 && $available < 5) {
            $availableText = 'volná místa';
        }

        $html .= '<tr><td>' . $row['name'] . '</td><td>' . $available . ' ' . $availableText . '</td><td>' . $button . '</td></tr>';
    }

    $html .= '</tbody></table></form>';
    $html .= empty($languagesTable) ? '<p>Nejsou k dispozici žádné jazyky.</p>' : '';
    return $html;
}

function setClientLanguage($key, $language) {
    $studentData = sql('SELECT * FROM `' . prefixTable('students') . '` WHERE `key`=?;', true, array($key));
    $languageData = sql('SELECT `class`, `limit` FROM `' . prefixTable('languages') . '` WHERE `id`=?;', true, array($language));
    $allowChange = (bool)getDataValue('choice.allow_change');

    if (isset($studentData[0])) {
        if (isset($languageData[0]) && $studentData[0]['class'] == $languageData[0]['class']) {
            if ($studentData[0]['choice'] != $language) {
                $numberOfChoices = getLanguageOccupancy($languageData[0]['class'], $language);
                $available = $languageData[0]['limit'] - $numberOfChoices;

                if ($available > 0) {
                    if (!$studentData[0]['choice'] || $allowChange) {
                        sql('UPDATE `' . prefixTable('students') . '` SET `choice`=? WHERE `id`=?;', false, array($language, $studentData[0]['id']));
                        sendConfirmationEmail($studentData[0], $language);

                        if ($studentData[0]['choice']) {
                            return 'changed';
                        } else {
                            return 'chosen';
                        }
                    } else {
                        return 'no-change';
                    }
                } else {
                    return 'full';
                }
            } else {
                return 'already-chosen';
            }
        } else {
            return 'no-language';
        }
    } else {
        return 'bad-key';
    }
}

function clientResultMessage($key, $index, $isAjax) {
    $ajax = $isAjax ? '&ajax=1' : '';
    header("Location: ?k=$key&result=$index$ajax");
    exit;
}
