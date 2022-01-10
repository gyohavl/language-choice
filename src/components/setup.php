<?php
function configExists() {
    return file_exists(__DIR__ . '/../../config.php');
}

function showConfigForm() {
    if (!configExists()) {
        $filledIn = !empty($_POST['dbname']) + !empty($_POST['dbuser']) + !empty($_POST['dbpass']) + !empty($_POST['dbhost']) + !empty($_POST['adminpass']);
        $required = !empty($_POST['dbname']) + !empty($_POST['dbuser']) + isset($_POST['dbpass']) + !empty($_POST['dbhost']) + !empty($_POST['adminpass']);
        $formDisplayData = array(
            'error' => '',
            'dbname' => isset($_POST['dbname']) ? $_POST['dbname'] : 'jazyky',
            'dbuser' => isset($_POST['dbuser']) ? $_POST['dbuser'] : 'uzivatelske-jmeno',
            'dbpass' => isset($_POST['dbpass']) ? $_POST['dbpass'] : 'heslo',
            'dbhost' => isset($_POST['dbhost']) ? $_POST['dbhost'] : 'localhost',
            'adminpass' => isset($_POST['adminpass']) ? $_POST['adminpass'] : '',
        );

        if ($filledIn > 0) {
            if ($required == 5) {
                file_put_contents(__DIR__ . '/../../config.php', "<?php
return array(
    'dbhost' => '{$_POST['dbhost']}',
    'dbuser' => '{$_POST['dbuser']}',
    'dbpass' => '{$_POST['dbpass']}',
    'dbname' => '{$_POST['dbname']}',
    'adminpass' => '{$_POST['adminpass']}'
);
");

                return adminTemplate('Výborně! Údaje byly nastaveny. <a href=".">Pokračovat do administrace…</a>');
            }

            $formDisplayData['error'] = 'Nezadali jste všechna potřebná data.';
        }

        return adminTemplate(fillTemplate('config-form', $formDisplayData));
    }
}

function dbReady() {
    $should_exist = array(
        prefixTable('students'),
        prefixTable('languages'),
        prefixTable('data')
    );
    $st_result = sql('show tables');
    $existing_tables = array();

    foreach ($st_result as $value) {
        $existing_tables[] = $value[0];
    }

    // checks if all necessary tables exist
    $containsAllValues = !array_diff($should_exist, $existing_tables);
    return $containsAllValues;
}

function showDbSetup() {
    if (!dbReady()) {
        if (isset($_POST['dbcreate'])) {
            sql("DROP TABLE IF EXISTS `" . prefixTable('students') . "`;
            CREATE TABLE `" . prefixTable('students') . "` (
                `id` mediumint NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `number` mediumint NOT NULL,
                `key` tinytext NOT NULL,
                `email` tinytext NOT NULL,
                `name` tinytext NOT NULL,
                `class` tinyint NOT NULL,
                `choice` tinyint NULL
              );", false);
        } else {
            return adminTemplate(
                '<form method="post">Nyní dojde k vytvoření tabulek v databázi (jejich názvy jsou vypsány níže).<br>'
                    . 'Existující tabulky se stejným názvem budou smazány. <input type="submit" name="dbcreate" value="Souhlasím"></form>'
                    . '<code>' . prefixTable('students') . '<br>' . prefixTable('languages') . '<br>' . prefixTable('data') . '</code>'
            );
        }
    }
}
