<?php
function showEditForm($form, $fill = null, $errorMessage = '') {
    $formBegin = '<form method="post" action=".">';
    $formEnd = '<input type="hidden" name="edit" value="' . $form . '"><input type="submit" value="Odeslat"></form>';
    $formName = '';
    $html = '';

    switch ($form) {
        case 'import-students':
            $formName = 'Importovat seznam studentů';
            $html = '<p>Formát: <code>spisové číslo,e-mail,celé jméno,třída (5/9)</code></p>'
                . $formBegin . '<textarea name="import-csv">' . fillInput($fill, 'import-csv') . '</textarea><br>' . $formEnd;
            break;

        case 'student':
            $html = $formBegin;

            if (empty($_GET['id']) && empty($_POST['id'])) {
                $studentId = null;
                $formName = 'Přidat studenta';
                $formData = null;
            } else {
                $studentId = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
                $formName = 'Upravit studenta ' . $studentId;
                $studentData = sql('SELECT * FROM `' . prefixTable('students') . '` WHERE id=?;', true, array($studentId));

                if (isset($studentData[0])) {
                    $formData = $studentData[0];
                    $html .= '<input type="hidden" name="id" value="' . $studentId . '">';
                } else {
                    $formName = 'Chyba: student nenalezen';
                    $html = '<a href=".">zpět</a>';
                    break;
                }
            }

            $textFields = array('sid', 'email', 'name', 'class', 'choice');

            foreach ($textFields as $fieldName) {
                $html .= _t('form', $fieldName) . ' <input type="text" name="' . $fieldName . '" value="' . fillInput($formData, $fieldName) . '"><br>';
            }

            $html .= $formEnd;
            // todo: styling, select boxes, key regeneration
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
            } else {
                $errorMessage = 'odeslali jste prázdný formulář';
            }
            break;

        case 'student':
                $studentId = !empty($_POST['id']) ? $_POST['id'] : null;
            
                // todo: add student if id is not set
                $query = 'UPDATE `' . prefixTable('students') . '` SET ';
                $requiredFields = array('sid', 'email', 'name', 'class');
                $data = array();

                foreach ($requiredFields as $field) {
                    if (!empty($_POST[$field])) {
                        $query .= "`$field`=?, ";
                        $data[] = $_POST[$field];
                    } else {
                        $errorMessage = 'chybí data';
                        break 2;
                    }
                }

                $query .= "`choice`=?";

                if (!empty($_POST['choice'])) {
                    $data[] = $_POST['choice'];
                } else {
                    $data[] = null;
                }

                if ($studentId !== null) {
                    $query .= ' WHERE `id`=?;';
                    $data[] = $studentId;
                }

                sql($query, false, $data);
                $errorMessage = '';
                $successLink = '?list=students';
            break;

        default:
            # code...
            break;
    }

    if ($errorMessage) {
        return showEditForm($form, $_POST, $errorMessage);
    }

    $successLink = isset($successLink) ? $successLink : '.';
    $successText = isset($successText) ? $successText : 'Hotovo. <a href="' . $successLink . '">Pokračovat zpět do administrace…</a>';
    return adminTemplate($successText);
}

function validateLine($line) {
    $values = explode(',', trim($line));

    if (count($values) == 4) {
        if ($values[0] && $values[1] && $values[2]) {
            if (in_array($values[3], getClasses())) {
                return array(true, $values, null);
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
