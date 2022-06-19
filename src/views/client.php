<?php
include(__DIR__ . '/../main.php');

echo fillTemplate('client', getClientView());

function getClientView() {
    if (configExists() && dbConnectionOk()) {
        if (!empty($_GET['k'])) {
            $key = $_GET['k'];
            $data = sql('SELECT * FROM `' . prefixTable('students') . '` WHERE `key`=?;', true, array($key));

            if (isset($data[0])) {
                $fill = $data[0];
                $fill['info'] = getDataValue('text.client');
                $fill['languages'] = getClientLanguages($key, $data[0]['class'], $data[0]['choice']);
                return fillTemplate('client-content', $fill);
            } else {
                return '<p>Tento přihlašovací odkaz je neplatný. Pokud si myslíte, že jde o chybu, kontaktujte správce aplikace.</p>';
            }
        } else if (!empty($_POST['key'] && !empty($_POST['language']))) {
            exit;
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

function getClientLanguages($key, $class, $choice) {
    $weekdays = array('v neděli', 'v pondělí', 'v úterý', 've středu', 've čtvrtek', 'v pátek', 'v sobotu');
    $months = array('', 'ledna', 'února', 'března', 'dubna', 'května', 'června', 'července', 'srpna', 'září', 'října', 'listopadu', 'prosince');
    $languageArray = getLanguagesArray(false, false);

    switch (choiceState()) {
        case 0:
            return '<p>Možnost volby jazyka zatím není k dispozici. Termín zpřístupnění bude brzy oznámen.</p>';
            break;

        case 1:
            $timeFrom = getDataValue('time.from');
            $processedTimeFrom = new DateTime($timeFrom);
            return '<p>Možnost volby jazyka bude zpřístupněna ' . $weekdays[$processedTimeFrom->format('w')] . ' '
                . $processedTimeFrom->format('j') . '. ' . $months[$processedTimeFrom->format('n')] . ' ' . $processedTimeFrom->format('Y \v G:i')
                . '. <span id="refreshBefore">V ten čas nezapomeňte <a href="?k=' . $key . '">obnovit stránku</a>.</span></p>';
            break;

        case 2:
            $chosenLanguage = ($choice && isset($languageArray[$choice])) ? $languageArray[$choice] : null;
            $html = $chosenLanguage
                ? ('<p>Vybraný jazyk: <b>' . $chosenLanguage . '</b></p><p>Svou volbu můžete změnit kliknutím na tlačítko <i>vybrat</i> u jiného jazyka.</p>')
                : '<p>Klepnutím na tlačítko <i>vybrat</i> zvolte jazyk.</p>';
            return $html . getClientLanguagesTable($key, $class, $choice)
                . '<p id="refreshDuring">Počty volných míst nemusí být aktuální, pro zobrazení momentálního stavu <a href="?k=' . $key . '">obnovte stránku</a>.</p>';
            break;

        case 3:
            $chosenLanguage = ($choice && isset($languageArray[$choice])) ? $languageArray[$choice] : 'žádný';
            return '<p>Možnost volby jazyka již není k dispozici.</p><p>Vybraný jazyk: <b>' . $chosenLanguage . '</b></p>';
            break;

        default:
            # code...
            break;
    }
}

function getClientLanguagesTable($key, $class, $choice) {
    $html = '<form method="post" action="."><input type="hidden" name="key" value="' . $key . '"><table class="bordered"><tbody>';
    $languagesTable = sql('SELECT * FROM `' . prefixTable('languages') . '` WHERE `class`=?;', true, array($class));

    foreach ($languagesTable as $row) {
        $numberOfChoices = getLanguageOccupancy($row['class'], $row['id']);
        $available = $row['limit'] - $numberOfChoices;

        if ($choice == $row['id']) {
            $button = 'vybráno';
        } else {
            if ($available > 0) {
                $button = '<button type="submit" name="language" value="' . $row['id'] . '">vybrat</button>';
            } else {
                $button = '';
            }
        }

        $html .= '<tr><td>' . $row['name'] . '</td><td>' . $available . ' volných míst</td><td>' . $button . '</td></tr>';
    }

    $html .= '</tbody></table></form>';
    $html .= empty($languagesTable) ? '<p>Nejsou k dispozici žádné jazyky.</p>' : '';
    return $html;
}

// function clientRedirectMessage($message = 'done', $type = 'success', $url = '.') {
//     $query = parse_url($url, PHP_URL_QUERY);
//     $separator = $query ? '&' : '?';
//     header("Location: $url$separator$type=$message");
//     exit;
// }
