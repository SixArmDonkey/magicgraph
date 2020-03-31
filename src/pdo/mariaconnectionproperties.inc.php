<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
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
      PDO::MYSQL_ATTR_INIT_COMMAND => 'SET SESSION time_zone="+00:00"',
      PDO::ATTR_AUTOCOMMIT => false,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];
  }
}
        