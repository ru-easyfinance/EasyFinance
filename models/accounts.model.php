<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления счетами пользователя
 * @copyright http://home-money.ru/
 * SVN $Id$
 */
class Accounts_Model
{
    /**
     * Добавляет новый счёт
     * @return bool
     */
    function add() {

        if (isset($_POST['acc']) && !empty($_POST['acc'])) {
            $account['type'] = $p_acc['type'];
            $account['currency'] = $p_acc['currency'];

            if (!empty($p_acc['name']))
            {
                $account['name'] = html($p_acc['name']);
            }
            else
            {
                $error_text['name'] = "Iacaaiea n?aoa ia aie?ii auou ionoui!";
            }

            if (isset($p_acc['money']))
            {
                if (preg_match('/^[0-9.-]+$/', $p_acc['money']))
                {
                    if ($account['type'] == 3 && $p_acc['money'] == 0)
                    {
                        $error_text['money'] = "A oeia 'aiea', ia?aeuiue eaieoae ia aie?ai auou ioeaaui!";
                    }else{
                        $account['money'] = $p_acc['money'];
                    }
                }
                else
                {
                    $error_text['money'] = "Iaaa?iua aaiiua!";
                }
            }

            $account['user_id'] = $user->getId();

            if (empty($error_text))
            {
                if($acc->saveAccount($account['type'], $account['name'], $account['money'], $account['currency']))
                {
                    //$tpl->assign('good_text', "N?ao aiaaaeai!");
                    $_SESSION['good_text'] = "N?ao aiaaaeai!";
                    header("Location: index.php?modules=account");
                }
            }
            else
            {
                $tpl->assign('error_text', $error_text);
                $tpl->assign('account', $account);
            }
        }
    }
}