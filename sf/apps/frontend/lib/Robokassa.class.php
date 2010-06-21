<?php

class Robokassa
{
    /**
     * Получить параметры из конфига app.yml
     * @param $name имя параметра ( url, test_url, login, pass1, pass2 )
     * @return значение параметра или false
     */
    private static function getSettings( $name )
    {
        $billingSettings = sfConfig::get('app_billing_robokassa');
        return ( isset( $billingSettings[$name] ) ) ? $billingSettings[$name] : false;
    }


    /**
     * Получить URL на страницу оплаты
     *
     * @param int $invId ID транзакции
     * @param float $outSum сумма оплаты
     * @param int term срок оплаты в месяцах
     * @return string URL для перенаправления на страницу оплаты
     */
    public static function getPaymentURL( $invId, $outSum, $term )
    {
        return self::getURL(
            array(
                'MrchLogin'         => self::getSettings('login'),
                'OutSum'            => $outSum,
                'InvId'             => $invId,
                'InvDesc'           => "",
                'SignatureValue'    => md5( self::getSettings('login') . ':' . $outSum . ':' . $invId . ':' . self::getSettings('pass1') . ':' . 'shpa=' . $term ),
                'shpa'              => $term,
            )
        );
    }


    /**
     * Проверка корректности подписи платежа пользователя
     *
     * @param int $invId ID транзакции
     * @param float $outSum сумма оплаты
     * @param string $signature подпись (md5)
     * @return boolean
     */
    public static function checkResult( $invId, $outSum, $term, $signature )
    {
        return ( strtolower( md5( $outSum . ':' . $invId . ':' . self::getSettings('pass2') . ':' . 'shpa=' . $term ) ) == strtolower( $signature ) );
    }


    /**
     * Проверка корректности подписи платежа пользователя
     *
     * @param int $invId ID транзакции
     * @param float $outSum сумма оплаты
     * @param string $signature подпись (md5)
     * @return boolean
     */
    public static function checkSuccessAndFailSignature( $invId, $outSum, $term, $signature )
    {
        return ( strtolower( md5( $outSum . ':' . $invId . ':' . self::getSettings('pass1') . ':' . 'shpa=' . $term ) ) == strtolower( $signature ) );
    }

    /**
     * Получить URL обращения к серверу платежной системы по массиву параметров
     *
     * @param array $params массив параметров
     * @return string URL обращения к серверу
     */
    private static function getURL( $params )
    {
        $URL = ( self::getSettings('test') ) ? self::getSettings('test_url') : self::getSettings('url');
        $URL .= "?";

        foreach( $params as $key => $value )
        {
            if ( $URL[strlen( $URL )-1] != '?' ) {
               $URL .= '&';
            }
            $URL .= $key . "=" . $value;
        }
        return $URL;
    }
}