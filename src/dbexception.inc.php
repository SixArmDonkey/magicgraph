<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph;

use Exception;
use stdClass;


class DBException extends Exception 
{
 /**
   * SQL Statement
   * @var string
   */
  private $sql = '';

  /**
   * Last options
   * @var array
   */
  private $opts = [];

  /**
   * Create a new database exception.
   * @param string $message Error message
   * @param string $code Error code
   * @param Exception $previous Previous error
   * @param string $sql SQL statement that generated the exception
   */
  public function __construct( $message = '', $code = '0', $previous = null, $sql = '', $opts = [] )
  {
    $this->sql = $sql;
    $this->opts = $opts;
    parent::__construct( "SQL Error: " . $message . ' ' . $this->__toString(), $code, $previous );

  }


  /**
   * Print the exception and query
   * @return string HTML formatter error text
   */
  public function __toString()
  {
    return "Failed Query: " . $this->sql . ' ' . parent::__toString();
  }


  /**
   * Retrieve the sql statement used
   * @return string sql
   */
  public function getSQL()
  {
    return $this->sql;
  }


  /**
   * Retrieve the options sent to the prepared statement
   * @return array|stdClass opts
   */
  public function getOpts()
  {
    return $this->opts;
  }  
}

