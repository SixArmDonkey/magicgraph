<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */

declare( strict_types=1 );

namespace buffalokiwi\magicgraph\persist;

use Exception;
use \InvalidArgumentException;


/**
 * Handles begin transaction, commit and rollback calls for a list of database connections.
 * Work is grouped by database connection id.  Multiple database connections may be used.
 * 
 * Transactions are executed in the order they were received.  
 * Caveat: When multiple database connections are used, work is grouped by connection.
 * Each encountered database connection will execute all related work in the order
 * they were entered before moving on to the next database connection and then 
 * executing that connections work and so on.
 * 
 * 
 */
class MySQLTransaction implements ITransaction
{
  private $units = [];
  private $executeOrder = [];
  
  public function __construct( ISQLRunnable ...$units )
  {
    if ( empty( $units ))
      throw new InvalidArgumentException( 'Units must not be empty.  No work to do.' );
    
    $order = 0;
    foreach( $units as $u )
    {
      /* @var $u ISQLRunnable */
      $id = $u->getConnection()->getConnectionId();
      if ( !isset( $this->units[$id] ))
      {
        $this->units[$id] = [$u->getConnection(), []];
        $this->executeOrder[$order] = $id;
        $order++;
      }
      
      $this->units[$id][1][] = $u;
    }
  }
  
  
  /**
   * Begins a transaction
   */
  public function beginTransaction() : void
  {
    foreach( $this->units as &$data )
    {
      $context = $data[0];
      /* @var $context \buffalokiwi\magicgraph\pdo\IDBConnection */
      
      $data[2] = !$context->inTransaction();
      if ( $data[2] )
        $context->beginTransaction();      
    }
  }
  
  
  /**
   * Rolls back an uncommitted transaction
   */
  public function rollBack() : void
  {
    foreach( $this->units as $data )
    {
      $context = $data[0];
      /* @var $context \buffalokiwi\magicgraph\pdo\IDBConnection */
      if ( $data[2] )
        $context->rollBack();
    }
  }
  
  
  /**
   * Commits a transaction after beginTransaction has been called
   * @throws Exception 
   */
  public function commit() : void
  {
    foreach( $this->units as $data )
    {
      $context = $data[0];
      /* @var $context \buffalokiwi\magicgraph\pdo\IDBConnection */
      if ( $data[2] )
        $context->commit();
    }    
  }
  
  
  /**
   * Executes the code that is part of the transaction 
   * @throws Exception 
   */
  public function run() : void
  {
    foreach( $this->executeOrder as $id )
    {
      $data = $this->units[$id];
      
      foreach( $data[1] as $unit )
      {
        /* @var $unit IRunnable */
        $unit->run();
      }
    }    
  }
}
