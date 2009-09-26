<?php
/**
 * DbSimple_Mysql: MySQL database.
 * (C) Dk Lab, http://en.dklab.ru
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * See http://www.gnu.org/copyleft/lesser.html
 *
 * Placeholders end blobs are emulated.
 *
 * @author Dmitry Koterov, http://forum.dklab.ru/users/DmitryKoterov/
 * @author Konstantin Zhinko, http://forum.dklab.ru/users/KonstantinGinkoTit/
 * 
 * @version 2.x $Id$
 */
require_once dirname(__FILE__) . '/Generic.php';


/**
 * Database class for MySQL.
 */
class DbSimple_Mysql extends DbSimple_Generic_Database
{
	protected $link = null;
	protected $dsn;
	
	/**
	 * constructor(string $dsn)
	 * Connect to MySQL.
	 */
	function DbSimple_Mysql($dsn)
	{
		$this->dsn = DbSimple_Generic::parseDSN($dsn);
		
		if (!is_callable('mysql_connect')) {
			return $this->_setLastError("-1", "MySQL extension is not loaded", "mysql_pconnect");
		}
	}

	/**
	 * Simple implementation of lazy load
	 *
	 */
	private function initConnection()
	{
		if( $this->link === null )
		{
			$this->link = @mysql_pconnect(
				$this->dsn['host'] . (empty($this->dsn['port'])? "" : ":".$this->dsn['port']),
				$this->dsn['user'],
				$this->dsn['pass'],
				true
			);
			
			$this->_resetLastError();
			if (!$this->link) return $this->_setDbError('mysql_connect()');
			$link = @mysql_select_db(preg_replace('{^/}s', '', $this->dsn['path']), $this->link);
			if (!$this->link) return $this->_setDbError('mysql_select_db()');
		}
		
		return $this->link;
	}
	
    function _performEscape($s, $isIdent=false)
    {
        if (!$isIdent) {
        	$this->initConnection();
            return "'" . mysql_real_escape_string($s, $this->link) . "'";
        } else {
            return "`" . str_replace('`', '``', $s) . "`";
        }
    }


    function _performTransaction($parameters=null)
    {
        return $this->query('BEGIN');
    }


    function& _performNewBlob($blobid=null)
    {
        $obj =& new DbSimple_Mysql_Blob($this, $blobid);
        return $obj;
    }


    function _performGetBlobFieldNames($result)
    {
        $blobFields = array();
        for ($i=mysql_num_fields($result)-1; $i>=0; $i--) {
            $type = mysql_field_type($result, $i); 
            if (strpos($type, "BLOB") !== false) $blobFields[] = mysql_field_name($result, $i);
        }
        return $blobFields;
    }


    function _performGetPlaceholderIgnoreRe()
    {
        return '
            "   (?> [^"\\\\]+|\\\\"|\\\\)*    "   |
            \'  (?> [^\'\\\\]+|\\\\\'|\\\\)* \'   |
            `   (?> [^`]+ | ``)*              `   |   # backticks
            /\* .*?                          \*/      # comments
        ';
    }


    function _performCommit()
    {
        return $this->query('COMMIT');
    }


    function _performRollback()
    {
        return $this->query('ROLLBACK');
    }


    function _performTransformQuery(&$queryMain, $how)
    {
        // If we also need to calculate total number of found rows...
        switch ($how) {
            // Prepare total calculation (if possible)
            case 'CALC_TOTAL':
                $m = null;
                if (preg_match('/^(\s* SELECT)(.*)/six', $queryMain[0], $m)) {
                    if ($this->_calcFoundRowsAvailable()) {
                        $queryMain[0] = $m[1] . ' SQL_CALC_FOUND_ROWS' . $m[2];
                    }
                }
                return true;
        
            // Perform total calculation.
            case 'GET_TOTAL':
                // Built-in calculation available?
                if ($this->_calcFoundRowsAvailable()) {
                    $queryMain = array('SELECT FOUND_ROWS()');
                }
                // Else use manual calculation.
                // TODO: GROUP BY ... -> COUNT(DISTINCT ...)
                $re = '/^
                    (?> -- [^\r\n]* | \s+)*
                    (\s* SELECT \s+)                                      #1     
                    (.*?)                                                 #2
                    (\s+ FROM \s+ .*?)                                    #3
                        ((?:\s+ ORDER \s+ BY \s+ .*?)?)                   #4
                        ((?:\s+ LIMIT \s+ \S+ \s* (?:, \s* \S+ \s*)? )?)  #5
                $/six';
                $m = null;
                if (preg_match($re, $queryMain[0], $m)) {
                    $query[0] = $m[1] . $this->_fieldList2Count($m[2]) . " AS C" . $m[3];
                    $skipTail = substr_count($m[4] . $m[5], '?'); 
                    if ($skipTail) array_splice($query, -$skipTail);
                }
                return true;
        }
        
        return false;
    }


    function _performQuery($queryMain)
    {
    	$this->initConnection();
    	
        $this->_lastQuery = $queryMain;
        $this->_expandPlaceholders($queryMain, false);
        
        $result = @mysql_query($queryMain[0], $this->link);
        if ($result === false) return $this->_setDbError($queryMain[0]);
        if (!is_resource($result)) {
            if (preg_match('/^\s* INSERT \s+/six', $queryMain[0])) {
                // INSERT queries return generated ID.
                return @mysql_insert_id($this->link);
            }
            // Non-SELECT queries return number of affected rows, SELECT - resource.
            return @mysql_affected_rows($this->link);
        }
        return $result;
    }

    
    function _performFetch($result)
    {
        $row = @mysql_fetch_assoc($result);
        if (mysql_error()) return $this->_setDbError($this->_lastQuery);
        if ($row === false) return null;        
        return $row;
    }
    
    
    function _setDbError($query)
    {
    	$this->initConnection();
        return $this->_setLastError(mysql_errno($this->link), mysql_error($this->link), $query);
    }
    
    
    function _calcFoundRowsAvailable()
    {
    	$this->initConnection();
        $ok = version_compare(mysql_get_server_info($this->link), '4.0') >= 0;
        return $ok;
    }
}


class DbSimple_Mysql_Blob extends DbSimple_Generic_Blob
{
    // MySQL does not support separate BLOB fetching. 
    var $blobdata = null;
    var $curSeek = 0;

    function DbSimple_Mysql_Blob(&$database, $blobdata=null)
    {
        $this->blobdata = $blobdata;
        $this->curSeek = 0;
    }

    function read($len)
    {
        $p = $this->curSeek;
        $this->curSeek = min($this->curSeek + $len, strlen($this->blobdata));
        return substr($this->blobdata, $this->curSeek, $len);
    }

    function write($data)
    {
        $this->blobdata .= $data;
    }

    function close()
    {
        return $this->blobdata;
    }

    function length()
    {
        return strlen($this->blobdata);
    }
}
?>