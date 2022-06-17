<?php
function configExists() {
    return file_exists(__DIR__ . '/../../config.php');
}

function dbConnectionOk() {
    return sql(null, null, null, true);
}

function showConfigForm($message = null) {
    global $config;

    if (!configExists() || !dbConnectionOk()) {
        $filledIn = !empty($_POST['dbname']) + !empty($_POST['dbuser']) + !empty($_POST['dbpass']) + !empty($_POST['dbhost']) + !empty($_POST['adminpass']);
        $required = !empty($_POST['dbname']) + !empty($_POST['dbuser']) + isset($_POST['dbpass']) + !empty($_POST['dbhost']) + !empty($_POST['adminpass']);
        $formDisplayData = array();
        $formDisplayData['error'] = $message;

        $formDisplayData['dbname'] = isset($config['dbname']) ? '' : 'jazyky';
        $formDisplayData['dbuser'] = isset($config['dbuser']) ? '' : 'uzivatelske-jmeno';
        $formDisplayData['dbpass'] = isset($config['dbpass']) ? '' : 'heslo';
        $formDisplayData['dbhost'] = isset($config['dbhost']) ? '' : 'localhost';
        $formDisplayData['adminpass'] = '';

        $formDisplayData['dbname'] = isset($_POST['dbname']) ? $_POST['dbname'] : $formDisplayData['dbname'];
        $formDisplayData['dbuser'] = isset($_POST['dbuser']) ? $_POST['dbuser'] : $formDisplayData['dbuser'];
        $formDisplayData['dbpass'] = isset($_POST['dbpass']) ? $_POST['dbpass'] : $formDisplayData['dbpass'];
        $formDisplayData['dbhost'] = isset($_POST['dbhost']) ? $_POST['dbhost'] : $formDisplayData['dbhost'];
        $formDisplayData['adminpass'] = isset($_POST['adminpass']) ? $_POST['adminpass'] : $formDisplayData['adminpass'];

        if ($filledIn > 0) {
            if ($required == 5) {
                if (!configExists() || $_POST['adminpass'] == $config['adminpass']) {
                    file_put_contents(__DIR__ . '/../../config.php', "<?php
return array(
    'dbhost' => '{$_POST['dbhost']}',
    'dbuser' => '{$_POST['dbuser']}',
    'dbpass' => '{$_POST['dbpass']}',
    'dbname' => '{$_POST['dbname']}',
    'adminpass' => '{$_POST['adminpass']}'
);
");
                    redirectMessage('setup');
                } else {
                    $formDisplayData['error'] = 'Zadali jste špatné heslo administrátora. Musíte zadat původní heslo, které jste zvolili při první konfiguraci připojení k databázi.';
                }
            } else {
                $formDisplayData['error'] = 'Nezadali jste všechna potřebná data.';
            }
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
            // students
            sql("DROP TABLE IF EXISTS `" . prefixTable('students') . "`;
            CREATE TABLE `" . prefixTable('students') . "` (
                `id` mediumint NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `sid` tinytext NOT NULL,
                `key` tinytext NOT NULL,
                `email` text NOT NULL,
                `name` text NOT NULL,
                `class` tinyint NOT NULL,
                `choice` tinyint NULL
            ) " . getSqlLanguageSettings() . ";", false);

            // languages
            sql("DROP TABLE IF EXISTS `" . prefixTable('languages') . "`;
            CREATE TABLE `" . prefixTable('languages') . "` (
                `id` mediumint NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` tinytext NOT NULL,
                `class` tinyint NOT NULL,
                `limit` tinyint NOT NULL,
                `export` tinytext NOT NULL
            ) " . getSqlLanguageSettings() . ";", false);

            // data
            sql("DROP TABLE IF EXISTS `" . prefixTable('data') . "`;
            CREATE TABLE `" . prefixTable('data') . "` (
                `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` tinytext NOT NULL,
                `value` text NULL
            ) " . getSqlLanguageSettings() . ";", false);

            fillDataTable();

            redirectMessage('tables');
        } else {
            return adminTemplate(
                '<form method="post" action=".">Nyní dojde k vytvoření tabulek v databázi (jejich názvy jsou vypsány níže).<br>'
                    . 'Existující tabulky se stejným názvem budou smazány. <input type="submit" name="dbcreate" value="Souhlasím"></form>'
                    . '<code>' . prefixTable('students') . '<br>' . prefixTable('languages') . '<br>' . prefixTable('data') . '</code>'
            );
        }
    }
}

function fillDataTable() {
    $result = sql('SELECT `name` FROM `' . prefixTable('data') . '`');
    $toInsert = array_fill_keys(flattenDataFields(), true);

    foreach ($result as $key => $value) {
        $toInsert[$value['name']] = false;
    }

    foreach ($toInsert as $key => $value) {
        if ($value) {
            sql(
                'INSERT INTO `' . prefixTable('data') . '` (`name`, `value`) VALUES (?, ?);',
                false,
                array($key, null)
            );
        }
    }
}
