<?php

    /**
     *
     */
    class Helper_DetectBrowser
    {

        /**
         * Определяет поисковик по юзерагенту, если поисковик, то тру. Иначе - фелз
         * @return bool
         */
        static function detectSearchEngine ()
        {
            /**
             * Список основных ботов
             * @var array
             */
            $bots = array(
                'Googlebot',
                'Yandex',
                'StackRambler',
                'msnbot',
                'Yahoo! Slurp',
                'WordPress',
                'Twiceler',
                'igdeSpyder',
                'Baiduspider+',
                'MJ12bot',
                'Validator',
                'ia_archiver',
                'W3 SiteSearch Crawler',
                'msnbot-media',
                'AdsBot-Google',
                'Aport',
                'Mediapartners-Google',
                'Direct/',
                'Yanga WorldSearch',
                'Dolphin',
                'NetcraftSurveyAgent',
                'BlogPulse',
                'Boomerang',
                'Tagoobot',
                'ovalebot',
                'FollowSite Bot',
                'OMGCrawler',
                'Huasai',
                'DobroBot',
                'PostRank',
                'FriendFeedBot',
                'bitlybot',
                'woriobot',
                'Twingly Recon',
                'OOZBOT',
                'Snapbot',
                'GoldenSpider',
                'librabot',
                'YoudaoBot',
                'BlogScope',
            );

            foreach ($bots as $value) {
                if (!empty($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], $value ) !== false) {
                    return true;
                }
            }

            return false;

        }

        /**
         * Определяет мобильный клиент, возвращает тру, если клиент - мобильный
         * @return bool
         */
        static function detectMobile ()
        {

            /**
             * Список мобильных юзерагентов
             * @var array
             */
            $mobiles = array (
                'sony',
                'symbian',
                'nokia',
                'samsung',
                'mobile',
                'windows ce',
                'epoc',
                'opera mini',
                'nitro',
                'j2me',
                'midp-',
                'cldc-',
                'netfront',
                'mot',
                'up.browser',
                'up.link',
                'audiovox',
                'blackberry',
                'ericsson,',
                'panasonic',
                'philips',
                'sanyo',
                'sharp',
                'sie-',
                'portalmmm',
                'blazer',
                'avantgo',
                'danger',
                'palm',
                'series60',
                'palmsource',
                'pocketpc',
                'smartphone',
                'rover',
                'ipaq',
                'au-mic,',
                'alcatel',
                'ericy',
                'up.link',
                'vodafone/',
                'wap1.',
                'wap2.',
                'android',
            );

            $useragent = strtolower( $_SERVER['HTTP_USER_AGENT'] );
            foreach ( $mobiles as $value ) {
                if ( strpos( $useragent , $value ) !== false ) {
                    return true;
                }

            }

        }
    }
