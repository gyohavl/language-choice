<?php
function showList($list) {
    if ($list == 'students') {
        $html = '<h1>Studenti</h1>';
        $html .= '<p><a href=".">zpět</a> | <a href="?edit=student">přidat</a> | <a href="?edit=import-students">importovat</a></p>';
        $html .= getStudentsTable();
    } else if ($list == 'languages') {
        $html = '<h1>Jazyky</h1>';
        $html .= '<p><a href=".">zpět</a> | <a href="?edit=language">přidat</a></p>';
        $html .= getLanguagesTable();
    } else if ($list == 'data') {
        $html = '<h1>Další data</h1>';
        $html .= '<p><a href=".">zpět</a> | <a href="?edit=data">upravit vše</a></p>';
        $html .= getDataTable();
    } else {
        $html = '<h1>Stránka nenalezena</h1>';
        $html .= '<p><a href=".">zpět</a></p>';
    }

    return adminTemplate($html);
}

function getStudentsTable() {
    $html = '<table class="bordered"><thead><tr>
    <th>id</th><th>spis. č.</th><th>e-mail</th><th>jméno</th><th>třída</th><th>vybraný jazyk</th><th>upravit</th><th>přihlašovací odkaz</th><th>klíč (pro kontrolu)</th>
    </tr></thead><tbody>';
    $studentsTable = sql('SELECT * FROM `' . prefixTable('students') . '`;');
    $linkPrefix = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . '?k=';
    $languagesArray = getLanguagesArray();
    $isFiltered = !empty($_GET['filter']) ? true : false;
    $allowFilter = false;
    $from = $isFiltered ? '&from=list_students!filter_1' : '';

    foreach ($studentsTable as $row) {
        if ($row[6]) {
            if (isset($languagesArray[$row[6]])) {
                $language = $languagesArray[$row[6]];
                $allowFilter = true;
            } else {
                $language = '<i class="empty">(neexistující jazyk č. ' . $row[6] . ')</i>';
            }
        } else {
            $language = '<i class="empty">(žádný)</i>';
        }

        if ($isFiltered && $row[6] && isset($languagesArray[$row[6]])) {
            continue;
        }

        $html .= "<tr><td>$row[0]</td><td>$row[1]</td><td>$row[3]</td><td>$row[4]</td><td>$row[5]</td><td>$language</td><td><a href=\"?edit=student&id=$row[0]$from\">upravit</a></td><td><input type=\"text\" value=\"$linkPrefix$row[2]\" onclick=\"this.setSelectionRange(0, this.value.length)\" readonly></td><td>$row[2]</td></tr>";
    }

    $html .= '</tbody></table>';

    if ($allowFilter) {
        if ($isFiltered) {
            $html = '<p><a href="?list=students">zobrazit všechny studenty</a></p>' . $html;
        } else {
            $html = '<p><a href="?list=students&filter=1">zobrazit pouze studenty bez vybraného jazyka</a></p>' . $html;
        }
    }

    return $html;
}

function getLanguagesTable() {
    $html = '<table class="bordered"><thead><tr>
    <th>id</th><th>jazyk</th><th>značka</th><th>třída</th><th>obsazeno/kapacita</th><th>upravit</th>
    </tr></thead><tbody>';
    $languagesTable = sql('SELECT * FROM `' . prefixTable('languages') . '`;');

    foreach ($languagesTable as $row) {
        $numberOfChoices = getLanguageOccupancy($row['class'], $row['id']);
        $html .= "<tr><td>$row[0]</td><td>$row[1]</td><td>$row[4]</td><td>$row[2]</td><td>$numberOfChoices/$row[3]</td><td><a href=\"?edit=language&id=$row[0]\">upravit</a></td></tr>";
    }

    $html .= '</tbody></table>';
    return $html;
}

function getLanguagesSelect($chosen) {
    $html = '<select name="choice" id="choice">';
    $languagesTable = sql('SELECT * FROM `' . prefixTable('languages') . '`;');
    $html .= '<option value="" style="font-style:italic">(žádná)</option>';
    $languagesArray = getLanguagesArray();

    if (!isset($languagesArray[$chosen])) {
        $html .= '<option value="' . $chosen . '" style="font-style:italic" selected>(neexistující jazyk č. ' . $chosen . ')</option>';
    }

    foreach ($languagesTable as $row) {
        $numberOfChoices = getLanguageOccupancy($row['class'], $row['id']);
        $selected = $chosen === $row[0] ? 'selected' : '';
        $html .= "<option value=\"$row[0]\" $selected>$row[1], $row[2]. třída ($numberOfChoices/$row[3])</option>";
    }

    $html .= '</select>';
    return $html;
}

function getLanguagesArray($export = false) {
    $languagesTable = sql('SELECT * FROM `' . prefixTable('languages') . '`;');
    $retArr = array();

    foreach ($languagesTable as $row) {
        $retArr[$row[0]] = $export ? $row['export'] : "$row[1] [$row[2]]";
    }

    return $retArr;
}

function getLanguageOccupancy($class, $langId) {
    $result = sql('SELECT COUNT(*) FROM `' . prefixTable('students') . '` WHERE `class`=? AND `choice`=?;', true, array($class, $langId));
    $number = isset($result[0]) && isset($result[0][0]) ? intval($result[0][0]) : 0;
    return $number;
}

function isLanguageAvailable($class, $langId) {
    $result = sql('SELECT `limit` FROM `' . prefixTable('languages') . '` WHERE `id`=?;', true, array($langId));
    $limit = isset($result[0]) && isset($result[0][0]) ? intval($result[0][0]) : 0;
    return getLanguageOccupancy($class, $langId) < $limit;
}

function getClassesSelect($chosen) {
    $html = '';
    $fieldName = 'class';

    foreach (getClasses() as $class) {
        $selected = $class === $chosen ? 'checked' : '';
        $html .= '<input type="radio" name="' . $fieldName . '" id="' . $fieldName . $class . '" value="' . $class . '" ' . $selected . '>
            <label for="' . $fieldName . $class . '">' . $class . '. třída</label><br>';
    }

    return $html;
}

function getDataValue($name) {
    $result = sql('SELECT `value` FROM `' . prefixTable('data') . '` WHERE `name`=?;', true, array($name));
    return isset($result[0]) && isset($result[0][0]) ? $result[0][0] : null;
}

function getDataTable() {
    fillDataTable();
    $result = sql('SELECT * FROM `' . prefixTable('data') . '`;');
    $resultData = array();
    $html = '';

    foreach ($result as $row) {
        $resultData[$row['name']] = $row['value'] ? $row['value'] : '<i class="empty">(prázdné)</i>';

        if ($row['name'] == 'choice.confirmation_send' || $row['name'] == 'choice.allow_change') {
            $resultData[$row['name']] = $row['value'] ? 'ano' : 'ne';
        }
    }

    $dataFields = getDataFields();

    foreach ($dataFields as $categoryName => $category) {
        $html .= '<h2>' . _t($categoryName, 'heading') . ' <a href="?edit=data&name=' . $categoryName . '">(upravit)</a></h2><table class="bordered"><tbody>';

        foreach ($category as $fieldName) {
            $html .= '<tr><th>' . _t($categoryName, $fieldName) . '</th><td>'
                . $resultData[_field($categoryName, $fieldName)] . '</td>';
            // $html .= '<td><a href="?edit=data&name=' . _field($categoryName, $fieldName) . '">upravit</a></td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
    }

    return $html;
}
