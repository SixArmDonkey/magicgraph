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
use InvalidArgumentException;


/**
 * Essentially, this is an IOC container 
 */
class TransactionFactory implements ITransactionFactory
{
  /**
   * A map of interface => function(IRunnable ...$tasks) for creating instances of 
   * some transaction type.
   * @var array 
   */
  private $map;
  
  /**
   * A list of map keys 
   * @var string
   */
  private $keys = [];
  
  /**
   * Create a new TransactionFactory instance 
   * @param array $map A map of interface => function(IRunnable ...$tasks) for creating instances of 
   * some transaction type.
   */
  public function __construct( array $map )
  {
    foreach( $map as $k => $v )
    {
      if ( !is_string( $k ) || empty( $k ))
        throw new InvalidArgumentException( 'Map keys must be non-empty strings' );
      else if ( !( $v instanceof Closure ))
        throw new InvalidArgumentException( 'Map values must be instances of \Closure' );
      
      $this->keys[] = $k;
    }
    
    $this->map = $map;
  }
  
  
  /**
   * Create a list of transactions for some list of runnables 
   * @param IRunnable $tasks tasks 
   * @return ITransaction[] transactions 
   * @throws InvalidArgumentException
   */
  public function createTransactions( IRunnable ...$tasks ) : array
  {
    if ( empty( $tasks ))
      throw new InvalidArgumentException( 'At least one task must be supplied' );
    
    
    $groups = [];
    
    foreach( $tasks as $task )
    {
      foreach( $this->keys as $key )
      {
        if ( !isset( $groups[$key] ))
          $groups[$key] = [];
        
        if ( is_subclass_of( $task, $key ))
        {
          $groups[$key][] = $task;
          break;
        }
      }
    }
    
    $out = [];
    foreach( $groups as $key => $data )
    {
      if ( empty( $data ))
        continue;
      
      $f = $this->map[$key];
      $res = $f( ...$data );
      
      if ( !( $res instanceof ITransaction ))
        throw new InvalidArgumentException( 'TransactionFactory map function does not return an instance of ITransaction for ' . $key );
      
      $out[] = $res;
    }
    
    return $out;    
  }
  
  
  /**
   * Execute a bunch of transactions directly.
   * This wraps then with something like the ChainedTransactionManager and calls 
   * run().
   * @param IRunnable $tasks Tasks to run
   * @return void
   */
  public function execute( IRunnable ...$tasks ) : void
  {
    ( new ChainedTransactionManager( ...$this->createTransactions( ...$tasks )))->run();
  }  
  
  
  /**
   * Executes all transactions, then attempts to execute the code in $afterExecute.
   * If afterExecute throws an exception, then all transactions are rolled back.
   * @param Closure $afterExecute f() : void throws \Exception - Do this after execute, and before commit.
   * @param IRunnable $tasks Tasks to execute 
   * @return void
   */
  public function executeAndTry( Closure $afterExecute, IRunnable ...$tasks ) : void
  {
    ( new ExecuteTryTransactionManager( $afterExecute, ...$this->createTransactions( ...$tasks )))->run();
  }
}
