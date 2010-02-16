<?php

class Currency_CurrencyUserCollection extends _Core_Abstract_Collection
{

    public function __construct()
    {
        
    }

    static public function loadAll()
    {
        $self = new Currency_CurrencyUserCollection();
        //@TODO добавить новые валюты. поискать курсы малобюджетных валют.
        //сделать реализацию пользовательских курсов.

        $cache = _Core_Cache::getInstance();
		
        $currencyList = $cache->get( __CLASS__ . __METHOD__ );

        $needToLoad = array();

        if( is_array($currencyList) )
        {
            $currencysArray = $cache->getMulti( $currencyList );

            while( list( $curId, $currency ) = each( $currencysArray ) )
            {
                if( !($currency instanceof Currency) )
                {
                        $needToLoad[] = $curId;
                }
            }
        }

        if (sizeof($needToLoad)){

            $sql = "SELECT MAX(currency_date) as last FROM daily_currency";
            $lastdate = $this->db->selectRow($sql);
            $sql = "SELECT c.cur_id as id, dai.currency_sum as value, c.cur_char_code as charCode, c.cur_name as abbr, dai.direction
                FROM currency c, daily_currency dai WHERE dai.currency_id=c.cur_id
                AND dai.currency_from = 1 AND currency_date = ?
                and currency_id IN(" . implode( ',', $needToLoad ) . ')';


            $currencyList = $this->db->select($sql, $lastdate['last']);

            foreach ( $currencyList as $k=>$currency ){

                $self->add( new Currency( $currency['id'], $currency['name'],
                    $currency['charCode'], $currency['abbr'], $currency['okv'], Core::$user->getId(), $currency['value'] )
                );
            }
        }


            
        foreach( $currencysArray as $currency  )
        {
            $self->add($currency);
        }


        $cache->set( __CLASS__ . __METHOD__, array_keys( $this->container ) );

        $cache->set( __CLASS__ . __METHOD__, $this );
            
        return $self;
    }

    public function add( Currency $currency )
    {
        $this->container[ $currency->getId() ] = $currency;
    }

    /**
     *
     * @param <type> $id
     * @return <type>
     */
    public function getByID( $id )
    {
        if ( in_array ( $id, $this->container ) ){
            return $this->container[$id];
        } else {
            throw new Currency_Exception('Currency Not Found');
            return false;
        }
    }

}