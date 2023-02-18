<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */

declare( strict_types=1 );

namespace buffalokiwi\magicgraph\persist;

use Exception;
use InvalidArgumentException;


/**
 * Transactions are executed in the order they were received.  
 * beginTransaction, commit and rollback do nothing.
 */
class Transaction implements ITransaction
{
  private $units;
  
  
  public function __construct( IRunnable ...$units )
  {
    if ( empty( $units ))
      throw new InvalidArgumentException( 'Units must not be empty.  No work to do.' );
    
    $this->units = $units;
  }
  
  
  /**
   * Begins a transaction
   */
  public function beginTransaction() : void
  {
    //..do nothing
  }
  
  
  /**
   * Rolls back an uncommitted transaction
   */
  public function rollBack() : void
  {
    //..Do nothing 
  }
  
  
  /**
   * Commits a transaction after beginTransaction has been called
   * @throws Exception 
   */
  public function commit() : void
  {
    //..Do nothing 
  }
  
  
  /**
   * Executes the code that is part of the transaction 
   * @throws Exception 
   */
  public function run() : void
  {
    foreach( $this->units as $unit )
    {
      /* @var $unit IRunnable */
      $unit->run();
    }    
  }
}
