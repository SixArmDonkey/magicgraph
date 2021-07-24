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


/**
 * Attempts to manage commit/rollback style transactions across multiple
 * units of work.  Each supplied ITransaction can use a different storage
 * engine stored, on whatever network, if desired.
 * 
 * 1) Transactions are started, run/executed and committed in the order they were received 
 * 2) If an exception occurs, the current transaction is rolled back, and any remaining non-committed 
 * transactions are also rolled back.  
 * 
 * This does not guarantee everything will be rolled back (commit fail or unsupported driver feature)
 * 
 * @todo This needs better exception handling during rollback 
 * @todo rollback on commit fail should be an option.  We may not want to roll everything back if one of many commits failed?  Maybe?
 */
class ChainedTransactionManager implements IRunnable
{
  /**
   * A list of transactions
   * @var ITransaction[]
   */
  private $trans;
  
  public function __construct( ITransaction ...$transaction )
  {
    $this->trans = $transaction;
  }
  
  
  /**
   * Execute all of the transactions 
   * @return void
   * @throws \Exception 
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
      } catch( \Exception | \TypeError $e ) {
        $this->rollBack();
        throw $e;
      }
    }
    
    try {
      foreach( $this->trans as $t )
      {
        $t->commit();
      }    
    } catch( \Exception | \TypeError $e ) {
      $this->rollBack();
      throw $e;
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
