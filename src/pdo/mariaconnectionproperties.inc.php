<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 *  MySql
 * @author John Quinn
 */

namespace buffalokiwi\magicgraph\pdo;

use PDO;



/**
 * MariaDB/MySQL connection properties
 */
class MariaConnectionProperties extends ConnectionProperties
{
  /**
   * Retrieve the data source name
   * @return string dsn
   */
  public function dsn() : string
  {
    return
         'mysql:'
       . 'host=' . $this->getHost()
       . (( $this->getPort() > 0 ) ? ';port=' . $this->getPort() : '' )
       . (( !empty( $this->getDatabase() )) ? ';dbname=' . $this->getDatabase() : ',charset=' . $this->getCharset());
  }


  /**
   * Retrieve an array of options for the database driver
   * @return array options
   */
  public function getOptions() : array 
  {
    return [
      //..Ensure that the time zone is set to UTC.  All local timezone conversions will happen at the application level.
      PDO::MYSQL_ATTR_INIT_COMMAND => 'SET SESSION time_zone="+00:00"', 
        
      //..If this is set to false, then a transaction is automatically started.  It's dumb and confusing.  It should start no transaction and throw an exception for insert/update instead.  whatever.
      PDO::ATTR_AUTOCOMMIT => true, 
        
      //..This is suppsed to use exceptions instead of errors, but doesn't appear to do anything since php8.
      //..This worked in php7, so maybe it's a bug with the initial release of 8.
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION  
    ];
  }
}
        