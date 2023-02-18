<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */

namespace buffalokiwi\magicgraph\pdo;

use buffalokiwi\magicgraph\DBException;
use PDOException;


/**
 * A factory for creating database connections 
 */
abstract class ConnectionFactory implements IConnectionFactory
{
  /**
   * Create a new connection.  Override this in the child class.
   */
  protected abstract function createNewConnection( IConnectionProperties $args ) : IDBConnection;
  
  /**
   * A list of active connections
   * [hash => IDBConnection]
   * @var array 
   */
  private $connections = [];
  
  
  /**
   * DB props
   * @var IConnectionProperties
   */
  private $props;
  
  public function __construct( IConnectionProperties $args )
  {
    $this->props = $args;
  }
  
  
  public function getConnection( bool $forceNew = false ) : IDBConnection
  {
    return $this->createConnection( $this->props );
  }

  
  /**
   * Connect to a database host
   * @param IConnectionProperties $args Connection arguments
   * @param bool $forceNew Force a new connection 
   * @return IDBConnection db connection
   * @throws DBException if there is a connection issue
   */
  public function createConnection( ?IConnectionProperties $args = null, bool $forceNew = false ) : IDBConnection
  {
    if ( $args == null )
      $args = $this->props;
    
    $h = $args->hash();

    if ( !isset( $this->connections[$h] ) || $forceNew )
    {
      //..Create the connection
      try {
        $c = $this->createNewConnection( $args );
      } catch( PDOException $e ) {
        throw new DBException( $e->getMessage(), $e->getCode(), $e, '(connection error)' );
      }

      if ( !$forceNew )
        $this->connections[$h] = $c;
    }

    return $this->connections[$h];
  }  
  
  
  public function close() : void
  {
    foreach( array_keys( $this->connections ) as $k )
    {      
      unset( $this->connections[$k] );
      break;
    }
  }
  
  
  public function closeConnection( IDBConnection $conn )
  {
    foreach( $this->connections as $k => $c )
    {
      if ( $k == $conn->getProperties()->hash())
      {
        unset( $this->connections[$k] );
        break;
      }
    }
  }
}
