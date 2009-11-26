<?php
class RPCPlugins {

    private $plugins;

    function __construct ($pathname, $rpcServer) {
        $d = dir($pathname);
        while (($file = $d->read()) !== false) {
            if (ereg('(.*)\.php$', $file, $regs)) {
                include_once ($pathname . '/' . $file);
                $class=$regs[1];
                $this->plugins = new $class($rpcServer);
            }
        }
    }

}

class RPCPlugin {

    private $_rpcServer;

    function __construct($rpcServer) {
        $this->_rpcServer = $rpcServer;

        $methods = get_class_methods($this);

        foreach ($methods as $method) {
            if (substr($method, 0,1) != '_') {
                xmlrpc_server_register_method($rpcServer,get_class($this) . "." . $method,array(&$this,$method));
            }
        }

    }
   
}