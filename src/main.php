<?php
date_default_timezone_set('Europe/Prague');
include(__DIR__ . '/components/db.php');
include(__DIR__ . '/components/setup.php');
include(__DIR__ . '/components/auth.php');
include(__DIR__ . '/components/dashboard.php');
include(__DIR__ . '/components/list.php');
include(__DIR__ . '/components/edit.php');
include(__DIR__ . '/components/confirm.php');
include(__DIR__ . '/components/system.php');
include(__DIR__ . '/components/translate.php');

if (configExists()) {
    $config = include(__DIR__ . '/../config.php');
}

function adminTemplate($content) {
    return fillTemplate('admin', array('content' => $content, 'message' => getInfoMessage()));
}

function fillTemplate($name, $data) {
    $html = file_get_contents(__DIR__ . '/../templates/' . $name . '.html');

    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $value = ($value === null) ? '' : $value;
            $html = str_replace('{' . $key . '}', $value, $html);
        }
    } else {
        $html = str_replace('{content}', $data, $html);
    }

    return $html;
}

function getClasses() {
    return array(5, 9);
}

function getDataFields() {
    return array(
        'time' => array('from', 'to'),
        'text' => array('client', 'email_sender', 'email_subject', 'email_body'),
        'mailer' => array('host', 'email', 'password'),
    );
}

function _field($category, $field, $separator = '.') {
    return $category . $separator . $field;
}

function _fieldBack($name) {
    return explode('.', $name);
}

function flattenDataFields() {
    $returnArr = array();
    $dataFields = getDataFields();

    foreach ($dataFields as $categoryName => $category) {
        foreach ($category as $fieldName) {
            $returnArr[] = _field($categoryName, $fieldName);
        }
    }

    return $returnArr;
}

function getDataFormFields($name) {
    $df = getDataFields();
    $fdf = flattenDataFields();

    if ($name) {
        if (isset($df[$name])) {
            return array($name => $df[$name]);
        } else if (in_array($name, $fdf)) {
            $nameArr = _fieldBack($name);
            return array($nameArr[0] => array($nameArr[1]));
        }
    }
    
    return $df;
}

function choiceState() {
    $timeFrom = getDataValue('time.from');
    $timeTo = getDataValue('time.to');
    $now = new DateTime('now');

    if ($timeFrom) {
        $processedTimeFrom = new DateTime($timeFrom);

        if ($now >= $processedTimeFrom) {
            if ($timeTo) {
                $processedTimeTo = new DateTime($timeTo);

                if ($now <= $processedTimeTo) {
                    return 2;
                } else {
                    return 3;
                }
            } else {
                return 2;
            }
        } else {
            return 1;
        }
    } else {
        return 0;
    }
}

function isChoiceOpen() {
    return choiceState() === 2;
}

function isChoicePast() {
    return choiceState() === 3;
}
