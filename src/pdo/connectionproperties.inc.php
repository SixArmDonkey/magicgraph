<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 *  Database
 * @author John Quinn
 */

namespace buffalokiwi\magicgraph\pdo;

use buffalokiwi\magicgraph\DBException;



/**
 * A database connection object that represents connection arguments
 */
abstract class ConnectionProperties implements IConnectionProperties
{
  /**
   * Database host
   * @var string
   */
  private $host  = '';

  /**
   * Database username
   * @var string
   */
  private $user  = '';

  /**
   * Database password
   * @var string
   */
  private $pass  = '';

  /**
   * Default database name
   * @var string
   */
  private $db    = '';

  /**
   * MySQL server port
   * @var int
   */
  private $port  = 3306;

  /**
   * The charset to use by default
   * @var string
   */
  private $charset = 'utf8';


  /**
   * Retrieve the data source name.
   * This is not implemented in this class
   * @return string dsn
   */
  public abstract function dsn() : string;


  /**
   * Retrive an array of options for the database driver
   * @return array options
   */
  public function getOptions() : array
  {
    return array();
  }


  /**
   * Retrieve the host name
   * @return string host
   */
  public function getHost() : string
  {
    return $this->host;
  }


  /**
   * Retrieve the user name
   * @return string user name
   */
  public function getUser() : string
  {
    return $this->user;
  }


  /**
   * Get the password
   * @return string password
   */
  public function getPassword() : string
  {
    return $this->pass;
  }


  /**
   * Return the initial database name
   * @return string initial database name
   */
  public function getDatabase() : string
  {
    return $this->db;
  }


  /**
   * Retrieve the port number in use
   * @return int port
   */
  public function getPort() : int
  {
    return $this->port;
  }


  /**
   * Retrieve a hash representing these connection properties
   * @return string hash
   */
  public function hash() : string
  {
    return md5( $this->host . $this->user . $this->pass );
  }


  /**
   * Set the database name
   * @param string $dbName Database name
   */
  public function setDatabase( string $dbName )
  {
    if ( empty( $dbName )) return;

    $this->db = $dbName;
  }


  /**
   * Sets the default connection character set
   * @param string $charset Charset
   */
  public function setCharset( string $charset )
  {
    if ( !empty( $charset ))
      $this->charset = $charset;
  }


  /**
   * Retrieve the character set
   * @return string charset
   */
  public function getCharset() : string
  {
    return $this->charset;
  }





  /**
   * Create a connection parameter object
   * @param string $host Database host
   * @param string $user Database user
   * @param string $pass Database password
   * @param string $db   Database name (Optional)
   * @param array(k=>v) Options
   *   Valid options:
   *     charset    - set the default character set for the current connection
   *     autocommit - set the auto-commit state
   *
   */
  public function __construct( string $host, string $user, string $pass, string $db = '', int $port = 3306, array $options = array())
  {
    if (( empty( $host )) || ( empty( $user )))
      throw new dbException( "Invalid connection arguments" );
    else if ( $port <= 0 )
    {
      throw new DBException( 'Invalid port number' );
    }
    
    $this->host = $host;
    $this->user = $user;
    $this->pass = $pass;
    $this->db   = $db;
    $this->port = $port;


    if ( !empty( $options ))
      $this->processOptions( $options );


  }


  /**
   * Process the options
   * @param array $options Options array
   */
  protected function processOptions( array $options )
  {
    foreach( $options as $k => $v )
    {
      if ( $k == 'charset' )
        $this->setCharset( $v );
      else if ( $k == 'autocommit' )
        $this->setAutoCommit( $v );
    }
  }
}


