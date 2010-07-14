<?php


class myAuthRememberKeyTable extends PluginmyAuthRememberKeyTable
{

    public static function getInstance()
    {
        return Doctrine_Core::getTable('myAuthRememberKey');
    }
}