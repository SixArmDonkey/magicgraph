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

use Closure;
use InvalidArgumentException;


/**
 * A factory/IOC style container for creating ITransactions from IRunnable 
 */
interface ITransactionFactory
{
  /**
   * Create a list of transactions for some list of runnables 
   * @param IRunnable $tasks tasks 
   * @return ITransaction[] transactions 
   * @throws InvalidArgumentException
   */
  public function createTransactions( IRunnable ...$tasks ) : array;  
  
  
  /**
   * Execute a bunch of transactions directly.
   * This wraps then with something like the ChainedTransactionManager and calls 
   * run().
   * @param IRunnable $tasks Tasks to run
   * @return void
   */
  public function execute( IRunnable ...$tasks ) : void;
  
  
  /**
   * Executes all transactions, then attempts to execute the code in $afterExecute.
   * If afterExecute throws an exception, then all transactions are rolled back.
   * @param Closure $afterExecute f() : void throws \Exception - Do this after execute, and before commit.
   * @param IRunnable $tasks Tasks to execute 
   * @return void
   */
  public function executeAndTry( Closure $afterExecute, IRunnable ...$tasks ) : void;  
}
