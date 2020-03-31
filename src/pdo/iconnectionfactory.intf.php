<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\pdo;

use buffalokiwi\magicgraph\DBException;


interface IConnectionFactory
{
  /**
   * Connect to a database host
   * @param IConnectionProperties $args Connection arguments
   * @param bool $forceNew Force a new connection 
   * @return IDBConnection db connection
   * @throws DBException if there is a connection issue
   */
  public function createConnection( ?IConnectionProperties $args = null, bool $forceNew = false ) : IDBConnection;
  
  /**
   * Retrieve a database connection 
   * @return IDBConnection
   */
  public function getConnection( bool $forceNew = false ) : IDBConnection;
  
  
  /**
   * Close all connections.
   * This simply sets the internal reference to null.
   */
  public function close() : void;
  
  
  /**
   * Close a specific connection.
   * This simply sets the internal reference to null.
   * @param \buffalokiwi\magicgraph\pdo\IDBConnection $conn
   */
  public function closeConnection( IDBConnection $conn );  
}
