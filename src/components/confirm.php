<?php
function confirm() {
    if (empty($_GET['confirm']) && empty($_POST['confirm'])) {
        return 'chyba';
    }

    $confirm = isset($_GET['confirm']) ? $_GET['confirm'] : $_POST['confirm'];
    $id = null;
    $confirmed = false;

    if (!empty($_GET['id']) || !empty($_POST['id'])) {
        $id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
    }

    if (!empty($_POST['confirmed'])) {
        $confirmed = boolval($_POST['confirmed']);
    }

    if ($confirmed) {
        switch ($confirm) {
            case 'delete-student':
                if ($id !== null) {
                    sql('DELETE FROM `' . prefixTable('students') . '` WHERE id=?;', false, array($id));
                    $successLink = '?list=students';
                }
                break;

            default:
                # code...
                break;
        }

        $successLink = isset($successLink) ? $successLink : '.';
        $successText = isset($successText) ? $successText : 'Hotovo. <a href="' . $successLink . '">Pokračovat zpět do administrace…</a>';
        return adminTemplate($successText);
    } else {
        return confirmForm($confirm, $id);
    }
}

function confirmForm($confirm, $id) {
    $html = 'Opravdu chcete ' . _t('confirm', $confirm);
    $html .= $id !== null ? ' č. ' . $id . '?' : '';
    $html .= '<form method="post" action=".">';
    $html .= $id !== null ? '<input type="hidden" name="id" value="' . $id . '">' : '';
    $html .= '<input type="hidden" name="confirm" value="' . $confirm . '"><input type="submit" name="confirmed" value="Potvrdit"></form>';
    return adminTemplate($html);
}
