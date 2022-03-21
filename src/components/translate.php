<?php
function _t($group, $key) {
    $strings = array(
        'form' => array(
            'sid' => 'spisové číslo',
            'email' => 'e-mail',
            'name' => 'celé jméno',
            'class' => 'třída',
            'choice' => 'volba jazyka'
        ),
        'form-l' => array(
            'name' => 'jazyk (název)',
            'class' => 'pro třídu (' . implode('/', getClasses()) . ')',
            'limit' => 'kapacita',
            'export' => 'označení pro export'
        ),
        'confirm' => array(
            'delete-student' => 'smazat studenta',
            'delete-language' => 'smazat jazyk'
        ),
        'success' => array(
            'login' => 'Výborně! Byli jste přihlášeni.',
            'done' => 'Hotovo.'
        )
    );

    return (isset($strings[$group]) && isset($strings[$group][$key])) ? $strings[$group][$key] : "$group.$key";
}
