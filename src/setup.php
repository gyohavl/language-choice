<?php
function configExists() {
    return file_exists(__DIR__ . '/../config.php');
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
                file_put_contents(__DIR__ . '/../config.php', "<?php
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
    var_dump(sql("SELECT table_name FROM information_schema.TABLES WHERE TABLE_TYPE = 'BASE TABLE'"));
}

function showDbSetup() {
    if (!dbReady()) {

    }
}
