<?php
function configExists() {
    return file_exists(__DIR__ . '/../config.php');
}

function showConfigForm() {
    if (!configExists()) {
        echo adminTemplate(file_get_contents(__DIR__ . '/../templates/config-form.html'));
    }
}
