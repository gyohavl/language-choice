<?php
function showEditForm($form, $fill = null, $errorMessage = '') {
    $formBegin = '<form method="post" action=".">';
    $formEnd = '<input type="hidden" name="edit" value="' . $form . '"><input type="hidden" name="from" value="' . (!empty($_GET['from']) ? $_GET['from'] : '') . '">'
        . '<input type="submit" value="Uložit"></form>';
    $formName = '';
    $html = '';

    switch ($form) {
        case 'import-students':
            $formName = 'Importovat seznam studentů';
            $html = '<p><a href="?list=students">zpět</a></p><p>Formát: <code>spisové číslo,e-mail,celé jméno,třída (' . implode('/', getClasses()) . ')</code></p>'
                . $formBegin . '<textarea name="import-csv">' . fillInput($fill, 'import-csv') . '</textarea><br>' . $formEnd;
            break;

        case 'student':
            $html = $formBegin;

            if (empty($_GET['id']) && empty($_POST['id'])) {
                $studentId = null;
                $formName = 'Přidat studenta';
                $formData = $fill ? $fill : null;
                $html .= '<p><a href="?list=students">zpět</a></p>';
            } else {
                $studentId = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
                $formName = 'Upravit studenta ' . $studentId;
                $studentData = sql('SELECT * FROM `' . prefixTable('students') . '` WHERE id=?;', true, array($studentId));
                $html .= '<p><a href="' . (!empty($_GET['from']) ? '?' . processFromLink($_GET['from']) : '?list=students')
                    . '">zpět</a> | <a href="?confirm=delete-student&id=' . $studentId . '">smazat</a></p>';

                if (isset($studentData[0])) {
                    $formData = $fill ? $fill : $studentData[0];
                    $html .= '<input type="hidden" name="id" value="' . $studentId . '">';
                } else {
                    $formName = 'Chyba: student nenalezen';
                    $html = '<a href=".">zpět</a>';
                    break;
                }
            }

            $textFields = array('sid', 'email', 'name', 'class', 'choice');
            $html .= '<table>';

            foreach ($textFields as $fieldName) {
                $html .= '<tr><td><label for="' . $fieldName . '">' . _t('form', $fieldName) . '</label></td>';

                if ($fieldName == 'choice') {
                    $html .= '<td>' . getLanguagesSelect(fillInput($formData, $fieldName)) . '</td></tr>';
                } else if ($fieldName == 'class') {
                    $html .= '<td>' . getClassesSelect(fillInput($formData, $fieldName)) . '</td></tr>';
                } else {
                    $html .= '<td><input type="text" name="' . $fieldName . '" id="' . $fieldName . '" value="' . fillInput($formData, $fieldName) . '" required></td></tr>';
                }
            }

            $html .= $studentId ? ('<tr><td>klíč</td><td><small>' . fillInput($formData, 'key') . ' <a href="?confirm=change-key&id=' . $studentId . '">(změnit)</a></small></td></tr>') : '';
            $html .= '</table>';
            $html .= $formEnd;
            break;

        case 'language':
            $html = $formBegin;

            if (empty($_GET['id']) && empty($_POST['id'])) {
                $languageId = null;
                $formName = 'Přidat jazyk';
                $formData = $fill ? $fill : null;
                $html .= '<p><a href="?list=languages">zpět</a></p>';
            } else {
                $languageId = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
                $formName = 'Upravit jazyk ' . $languageId;
                $languageData = sql('SELECT * FROM `' . prefixTable('languages') . '` WHERE id=?;', true, array($languageId));
                $html .= '<p><a href="?list=languages">zpět</a> | <a href="?confirm=delete-language&id=' . $languageId . '">smazat</a></p>';

                if (isset($languageData[0])) {
                    $formData = $fill ? $fill : $languageData[0];
                    $html .= '<input type="hidden" name="id" value="' . $languageId . '">';
                } else {
                    $formName = 'Chyba: jazyk nenalezen';
                    $html = '<a href=".">zpět</a>';
                    break;
                }
            }

            $textFields = array('name', 'class', 'limit', 'export');
            $html .= '<table>';

            foreach ($textFields as $fieldName) {
                $html .= '<tr><td><label for="' . $fieldName . '">' . _t('form-l', $fieldName) . '</label></td>';

                if ($fieldName == 'class') {
                    $html .= '<td>' . getClassesSelect(fillInput($formData, $fieldName)) . '</td></tr>';
                } else if ($fieldName == 'limit') {
                    $html .= '<td><input type="number" min="1" name="' . $fieldName . '" id="' . $fieldName . '" value="' . fillInput($formData, $fieldName) . '" required></td></tr>';
                } else {
                    $html .= '<td><input type="text" name="' . $fieldName . '" id="' . $fieldName . '" value="' . fillInput($formData, $fieldName) . '" required></td></tr>';
                }
            }

            $html .= '</table>';
            $html .= $formEnd;
            break;

        case 'data':
            $formName = 'Upravit další data';
            $html = $formBegin;
            $name = isset($_GET['name']) ? $_GET['name'] : (isset($_POST['name']) ? $_POST['name'] : 'none');
            $fields = getDataFormFields($name);
            $html .= '<p><a href="' . (!empty($_GET['from']) ? '?' . processFromLink($_GET['from']) : '?list=data') . '">zpět</a></p>';
            $html .= '<input type="hidden" name="name" value="' . $name . '">';
            $html .= '<table>';

            foreach ($fields as $category => $catFields) {
                $html .= '<tr><th colspan="2">' . _t($category, 'heading') . '</th>';

                foreach ($catFields as $field) {
                    $fid = _field($category, $field);
                    $html .= '<tr><td><label for="' . $fid . '">' . _t($category, $field) . '</label></td><td>';
                    $otherAttributes = '';

                    if ($category == 'time') {
                        $dv = getDataValue($fid);
                        $datetime = $dv ? explode(' ', $dv, 2) : array('', '');
                        $html .= '<input type="date" onchange="updateTime(\'' . $fid . '\');" id="' . $fid . 'd" value="' . $datetime[0] . '">';
                        $html .= '<input type="time" onchange="updateTime(\'' . $fid . '\');" id="' . $fid . 't" value="' . $datetime[1] . '">';
                        $html .= '<input type="button" value="propsat →" onclick="updateTime(\'' . $fid . '\');">';
                        $otherAttributes = ' placeholder="RRRR-MM-DD hh:mm"';
                    } else if ($field == 'password') {
                        $otherAttributes = 'autocomplete="off"';
                    }

                    if ($field == 'client' || $field == 'email_body' || $field == 'confirmation_body') {
                        $html .= '<textarea type="text" name="' . $fid . '" id="' . $fid . '">' . getDataValue($fid) . '</textarea>';
                    } else if ($field == 'confirmation_send' || $field == 'allow_change') {
                        $otherAttributes = getDataValue($fid) ? 'checked' : '';
                        $html .= '<input type="checkbox" name="' . $fid . '" id="' . $fid . '" value="1" ' . $otherAttributes . '>';
                    } else {
                        $html .= '<input type="text" name="' . $fid . '" id="' . $fid . '" value="' . getDataValue($fid) . '" ' . $otherAttributes . '>';
                    }

                    if ($field == 'email_body' || $field == 'confirmation_body') {
                        $onclick = 'onclick="bodyInsert(this, \'' . $fid . '\');"';
                        $html .= '<p>povolené proměnné: ';
                        $html .= $field == 'confirmation_body' ? '<span ' . $onclick . ' title="vybraný jazyk">(volba)</span>, ' : '';
                        $html .= '<span ' . $onclick . '>(odkaz)</span>,
                        <span ' . $onclick . ' title="spisové číslo">(spisc)</span>, <span ' . $onclick . '>(jmeno)</span>,
                        <span ' . $onclick . '>(trida)</span>';
                        $html .= '</p>';
                    } else if ($field == 'client') {
                        $html .= '<p>podporuje HTML (bez ošetření XSS zranitelnosti)</p>';
                    }

                    $html .= '</td></tr>';
                }
            }

            $html .= '</table>';
            $html .= $formEnd;
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
    $successLink = '.';
    $errorMessage = 'někde nastala chyba';

    switch ($form) {
        case 'import-students':
            if (!empty($_POST['import-csv'])) {
                $lines = explode("\n", $_POST['import-csv']);
                $data = array();

                foreach ($lines as $lineNumber => $line) {
                    $errorLineNumber = 'na řádku ' . ($lineNumber + 1);

                    if ($line) {
                        $validation = validateLine($line);

                        if ($validation[0]) {
                            $data[] = $validation[1];
                        } else {
                            $errorMessage = $errorLineNumber . $validation[2];
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
                $successLink = '?list=students';
            } else {
                $errorMessage = 'odeslali jste prázdný formulář';
            }
            break;

        case 'student':
            $studentId = !empty($_POST['id']) ? $_POST['id'] : null;
            $addStudent = $studentId === null;
            $query = $addStudent
                ? 'INSERT INTO `' . prefixTable('students') . '` ('
                : 'UPDATE `' . prefixTable('students') . '` SET ';
            $requiredFields = array('sid', 'email', 'name', 'class');
            $data = array();

            foreach ($requiredFields as $field) {
                if (!empty($_POST[$field])) {
                    $query .= $addStudent ? "`$field`, " : "`$field`=?, ";
                    $data[] = sanitizeInput($_POST[$field]);
                } else {
                    $errorMessage = 'chybí data';
                    break 2;
                }
            }

            $query .= $addStudent ? "`choice`, `key`" : "`choice`=?";

            if (!empty($_POST['choice'])) {
                $data[] = $_POST['choice'];
            } else {
                $data[] = null;
            }

            if ($addStudent) {
                $query .= ') VALUES (?, ?, ?, ?, ?, ?);';
                $data[] = createUniqueKey();
            } else {
                $query .= ' WHERE `id`=?;';
                $data[] = $studentId;
            }

            sql($query, false, $data);
            $errorMessage = '';
            $successLink = '?list=students';
            break;

        case 'language':
            $languageId = !empty($_POST['id']) ? $_POST['id'] : null;
            $addLanguage = $languageId === null;
            $query = $addLanguage
                ? 'INSERT INTO `' . prefixTable('languages') . '` ('
                : 'UPDATE `' . prefixTable('languages') . '` SET ';
            $requiredFields = array('name', 'class', 'limit', 'export');
            $data = array();

            if (!is_numeric($_POST['limit']) || !is_int((int)$_POST['limit']) || (int)$_POST['limit'] < 1) {
                $errorMessage = _t('form-l', 'limit') . ' musí být přirozené číslo';
                break;
            }

            foreach ($requiredFields as $field) {
                if (!empty($_POST[$field])) {
                    $query .= $addLanguage ? "`$field`, " : "`$field`=?, ";
                    $data[] = sanitizeInput($_POST[$field]);
                } else {
                    $errorMessage = 'chybí data';
                    break 2;
                }
            }

            $query = rtrim($query, ', ');

            if ($addLanguage) {
                $query .= ') VALUES (?, ?, ?, ?);';
            } else {
                $query .= ' WHERE `id`=?;';
                $data[] = $languageId;
            }

            sql($query, false, $data);
            $errorMessage = '';
            $successLink = '?list=languages';
            break;

        case 'data':
            $name = isset($_GET['name']) ? $_GET['name'] : (isset($_POST['name']) ? $_POST['name'] : 'none');
            $fields = getDataFormFields($name);
            $errorMessage = '';

            foreach ($fields as $category => $catFields) {
                foreach ($catFields as $field) {
                    $fid = _field($category, $field);
                    $fid2 = _field($category, $field, '_');
                    $value = !empty($_POST[$fid2]) ? $_POST[$fid2] : null;

                    // validation
                    if ($category == 'time' && $value) {
                        try {
                            $date = new DateTime($value);

                            if ($date->format("Y-m-d H:i") != $value) {
                                $errorMessage = 'chybně zadané datum';
                                continue;
                            }
                        } catch (\Throwable $th) {
                            $errorMessage = 'chybně zadané datum';
                            continue;
                        }
                    }

                    if ($field != 'client') {
                        $value = sanitizeInput($value);
                    } else if ($value) {
                        $value = preg_replace('/<\/?textarea>?/', '[textarea]', $value);
                    }

                    $query = 'UPDATE `' . prefixTable('data') . '` SET `value`=? WHERE `name`=?;';
                    sql($query, false, array($value, $fid));
                }
            }

            $successLink = '?list=data';
            break;

        default:
            break;
    }

    if ($errorMessage) {
        return showEditForm($form, $_POST, $errorMessage);
    }

    if (!empty($_POST['from'])) {
        $successLink = '?' . processFromLink($_POST['from']);
    }

    redirectMessage($form, 'success', $successLink);
}

function processFromLink($link) {
    return str_replace(['_', '!'], ['=', '&'], $link);
}

function validateLine($line) {
    $values = explode(',', trim($line));

    if (count($values) == 4) {
        if ($values[0] && $values[1] && $values[2] && $values[3]) {
            if (in_array($values[3], getClasses())) {
                if (filter_var($values[1], FILTER_VALIDATE_EMAIL)) {
                    return array(true, $values, null);
                } else {
                    $errorMessage = ' byl <abbr title="v případě chyby validace ji lze obejít přes formulář přidat/upravit studenta">detekován nevalidní e-mail</abbr>';
                }
            } else {
                $errorMessage = ' zadána špatná třída (zadáno <code>' . $values[3] . '</code>, podporováno <code>' . implode('/', getClasses()) . '</code>)';
            }
        } else {
            $errorMessage = ' je jeden sloupec prázdný';
        }
    } else {
        $errorMessage = ' chybí nebo přebývá sloupec';
    }

    return array(false, null, $errorMessage);
}

function setDataValue($name, $value) {
    $query = 'UPDATE `' . prefixTable('data') . '` SET `value`=? WHERE `name`=?;';
    sql($query, false, array($value, $name));
}

function createUniqueKey() {
    $existingKeys = array();
    $existingKeysTable = sql('SELECT `key` FROM `' . prefixTable('students') . '`;');

    foreach ($existingKeysTable as $row) {
        $existingKeys[] = $row['key'];
    }

    $newKey = generateRandomString();

    if (in_array($newKey, $existingKeys)) {
        return createUniqueKey();
    }

    return $newKey;
}

// https://stackoverflow.com/questions/4356289/php-random-string-generator
function generateRandomString() {
    // using 0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ but without vowels to prevent real (especially curse) words
    // there are 50^25 possible combinations so it's pretty much impossible to break through using only brute force
    $stringLength = 25;
    $characters = '0123456789bcdfghjklmnpqrstvwxzBCDFGHJKLMNPQRSTVWXZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $stringLength; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}
