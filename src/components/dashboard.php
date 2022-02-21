<?php
function showDashboard() {
    $html = '<h1>Nástěnka</h1><ul><li><a href="?list=students">studenti</a></li></ul>';
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

function showList($list) {
    if ($list == 'students') {
        $html = '<h1>Studenti <a href="?edit=import-students">(importovat)</a></h1>';
        $html .= getStudentsTable();
        return adminTemplate($html);
    }
}

function getStudentsTable() {
    $html = '<table><thead><tr>
    <th>id</th><th>spisové číslo</th><th>e-mail</th><th>jméno</th><th>třída</th><th>vybraný jazyk</th><th>upravit</th><th>přihlašovací odkaz</th>
    </tr></thead><tbody>';
    $studentsTable = sql('SELECT * FROM `' . prefixTable('students') . '`;');
    $linkPrefix = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . '?k=';

    foreach ($studentsTable as $row) {
        $html .= "<tr><td>$row[0]</td><td>$row[1]</td><td>$row[3]</td><td>$row[4]</td><td>$row[5]</td><td>$row[6]</td><td><a href=\"?edit=student&id=$row[0]\">upravit</a></td><td><input type=\"text\" value=\"$linkPrefix$row[2]\" readonly></td></tr>";
    }

    $html .= '</tbody></table>';
    return $html;
}

function showEditForm($form, $fill = null, $errorMessage = '') {
    $formBegin = '<form method="post" action=".">';
    $formEnd = '<input type="hidden" name="edit" value="' . $form . '"><input type="submit" value="Odeslat"></form>';

    switch ($form) {
        case 'import-students':
            $formName = 'Importovat seznam studentů';
            $html = '<p>Formát: <code>spisové číslo,e-mail,celé jméno,třída (5/9)</code></p>'
                . $formBegin . '<textarea name="import-csv">' . fillInput($fill, 'import-csv') . '</textarea><br>' . $formEnd;
            break;

        case 'student':
            if (empty($_GET['id'])) {
                $formName = 'Chyba: chybí ID studenta';
                $html = '<a href=".">zpět</a>';
                break;
            }

            $studentId = $_GET['id'];
            $formName = 'Upravit studenta ' . $studentId;
            $studentData = sql('SELECT * FROM `' . prefixTable('students') . '` WHERE id=?;', true, array($studentId));
            $html = '';
            var_dump($studentData);
            break;

        default:
            $formName = 'Chyba: formulář nenalezen';
            $html = '<a href=".">zpět</a>';
            break;
    }

    $errorBar = $errorMessage ? "<p>Chyba: <i>$errorMessage</i></p>" : '';
    return adminTemplate('<h1>' . $formName . '</h1>'  . $errorBar . $html);
}

function fillInput($fill, $key) {
    return isset($fill[$key]) ? $fill[$key] : '';
}

function editData($form) {
    $errorMessage = 'někde nastala chyba';

    switch ($form) {
        case 'import-students':
            if (!empty($_POST['import-csv'])) {
                $lines = explode("\n", $_POST['import-csv']);
                $data = array();

                foreach ($lines as $lineNumber => $line) {
                    $errorLineNumber = 'na řádku ' . ($lineNumber + 1);

                    if ($line) {
                        $values = explode(',', trim($line));

                        if (count($values) == 4) {
                            if ($values[0] && $values[1] && $values[2]) {
                                if (in_array($values[3], getClasses())) {
                                    $data[] = $values;
                                } else {
                                    $errorMessage = $errorLineNumber . ' je špatná třída (zadáno <code>'
                                        . $values[3] . '</code>, podporováno <code>' . implode('/', getClasses()) . '</code>)';
                                    break 2;
                                }
                            } else {
                                $errorMessage = $errorLineNumber . ' je jeden sloupec prázdný';
                                break 2;
                            }
                        } else {
                            $errorMessage = $errorLineNumber . ' chybí nebo přebývá sloupec';
                            break 2;
                        }
                    }
                }

                foreach ($data as $student) {
                    sql(
                        'INSERT INTO `' . prefixTable('students') . '` (`sid`, `key`, `email`, `name`, `class`) VALUES (?, ?, ?, ?, ?);',
                        false,
                        array($student[0], createUniqueKey(), $student[1], $student[2], $student[3])
                    );
                }

                $errorMessage = '';
            } else {
                $errorMessage = 'odeslali jste prázdný formulář';
            }
            break;

        default:
            # code...
            break;
    }

    if ($errorMessage) {
        return showEditForm($form, $_POST, $errorMessage);
    }

    return adminTemplate('Hotovo. <a href=".">Pokračovat zpět do administrace…</a>');
}

function createUniqueKey() {
    $existingKeys = array();
    $existingKeysTable = sql('SELECT `key` FROM `' . prefixTable('students') . '`;');

    foreach ($existingKeysTable as $row) {
        $existingKeys[] = $row['key'];
    }

    $newKey = generateRandomString(25);

    if (in_array($newKey, $existingKeys)) {
        return createUniqueKey();
    }

    return $newKey;
}

// https://stackoverflow.com/questions/4356289/php-random-string-generator
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
