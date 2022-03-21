<?php
function showList($list) {
    if ($list == 'students') {
        $html = '<h1>Studenti</h1>';
        $html .= '<p><a href=".">zpět</a> | <a href="?edit=student">přidat</a> | <a href="?edit=import-students">importovat</a></p>';
        $html .= getStudentsTable();
        return adminTemplate($html);
    } else if ($list == 'languages') {
        $html = '<h1>Jazyky</h1>';
        $html .= '<p><a href=".">zpět</a> | <a href="?edit=language">přidat</a></p>';
        $html .= getLanguagesTable();
        return adminTemplate($html);
    }
}

function getStudentsTable() {
    $html = '<table><thead><tr>
    <th>id</th><th>spis. č.</th><th>e-mail</th><th>jméno</th><th>třída</th><th>vybraný jazyk</th><th>upravit</th><th>přihlašovací odkaz</th><th>klíč (pro kontrolu)</th>
    </tr></thead><tbody>';
    $studentsTable = sql('SELECT * FROM `' . prefixTable('students') . '`;');
    $linkPrefix = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . '?k=';

    foreach ($studentsTable as $row) {
        $html .= "<tr><td>$row[0]</td><td>$row[1]</td><td>$row[3]</td><td>$row[4]</td><td>$row[5]</td><td>$row[6]</td><td><a href=\"?edit=student&id=$row[0]\">upravit</a></td><td><input type=\"text\" value=\"$linkPrefix$row[2]\" onclick=\"this.setSelectionRange(0, this.value.length)\" readonly></td><td>$row[2]</td></tr>";
    }

    $html .= '</tbody></table>';
    return $html;
}

function getLanguagesTable() {
    return '';
}
