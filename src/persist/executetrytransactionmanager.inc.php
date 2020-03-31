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

use Closure;
use Exception;


/**
 * Attempts to manage commit/rollback style transactions across multiple
 * units of work.  Each supplied ITransaction can use a different storage
 * engine stored, on whatever network, if desired.
 * 
 * 1) Transactions are all started in the order they are received.
 * 2) Transactions are run/executed and committed in the order they were received 
 * 3) If an exception occurs, the current transaction is rolled back, and any remaining non-committed 
 * transactions are also rolled back.  
 * 
 * While this does not guarantee that everything will be rolled back, it should
 * help reduce the number of errors.
 */
class ExecuteTryTransactionManager implements IRunnable
{
  /**
   * A list of transactions
   * @var ITransaction[]
   */
  private $trans;
  
  /**
   * After execute 
   * @var Closure
   */
  private $afterExecute;
  
  
  /**
   * 
   * @param Closure $afterExecute Run this after each transaction has run 
   * @param ITransaction $transaction
   */
  public function __construct( Closure $afterExecute, ITransaction ...$transaction )
  {
    $this->trans = $transaction;
    $this->afterExecute = $afterExecute;
  }
  
  
  /**
   * Execute all of the transactions 
   * @return void
   * @throws Exception 
   */
  public function run() : void
  {
    foreach( $this->trans as $t )
    {
      $t->beginTransaction();
    }
    
    
    foreach( $this->trans as $t )
    {
      try {
        $t->run();
      } catch( Exception | \TypeError $e ) {
        $this->rollBack();
        throw $e;
      }
    }    
    
    $f = $this->afterExecute;
    $f();
    
    
    foreach( $this->trans as $t )
    {
      try {
        $t->commit();
      } catch( Exception | \TypeError $e ) {
        $this->rollBack();
        throw $e;
      }
    }    
  }
  
  
  private function rollBack() : void
  {
    foreach( $this->trans as $t )
    {
      $t->rollBack();
    }
  }
}
