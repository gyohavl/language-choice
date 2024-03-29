<?php
function confirm() {
    $successLink = '.';

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

            case 'delete-language':
                if ($id !== null) {
                    sql('DELETE FROM `' . prefixTable('languages') . '` WHERE id=?;', false, array($id));
                    $successLink = '?list=languages';
                }
                break;

            case 'change-key':
                if ($id !== null) {
                    sql('UPDATE `' . prefixTable('students') . '` SET `key`=? WHERE `id`=?;', false, array(createUniqueKey(), $id));
                    $successLink = '?list=students';
                }
                break;

            case 'wipe-next':
                sql("TRUNCATE TABLE `" . prefixTable('students') . "`;", false);
                setDataValue('time.from', '');
                setDataValue('time.to', '');
                setDataValue('generated.last_sent', '');
                setDataValue('generated.skipped_ids', '');
                break;

            case 'wipe-mailer-password':
                setDataValue('mailer.password', '');
                break;

            case 'wipe-students':
                sql("TRUNCATE TABLE `" . prefixTable('students') . "`;", false);
                break;

            case 'wipe-languages':
                sql("TRUNCATE TABLE `" . prefixTable('languages') . "`;", false);
                break;

            case 'wipe-data':
                sql("TRUNCATE TABLE `" . prefixTable('data') . "`;", false);
                break;

            case 'wipe-clean':
                sql("DROP TABLE `" . prefixTable('students') . "`;", false);
                sql("DROP TABLE `" . prefixTable('languages') . "`;", false);
                sql("DROP TABLE `" . prefixTable('data') . "`;", false);
                break;

            default:
                break;
        }

        redirectMessage($confirm, 'success', $successLink);
    } else {
        return confirmForm($confirm, $id);
    }
}

function confirmForm($confirm, $id) {
    $html = 'Opravdu chcete ' . _t('confirm', $confirm);
    $html .= $id !== null ? ' č. ' . $id . '?' : '?';
    $html .= ($confirm == 'change-key') ? '<br>Pokud již student obdržel úvodní e-mail, bude mu nutné zaslat nový přihlašovací odkaz, neboť ten původní přestane platit.' : '';
    $html .= '<form method="post" action=".">';
    $html .= $id !== null ? '<input type="hidden" name="id" value="' . $id . '">' : '';
    $html .= '<input type="hidden" name="confirm" value="' . $confirm . '"><input type="submit" name="confirmed" value="Potvrdit"> <a href=".">storno</a></form>';
    return adminTemplate($html);
}
