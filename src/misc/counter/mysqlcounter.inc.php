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

namespace buffalokiwi\magicgraph\misc\counter;

use buffalokiwi\magicgraph\pdo\IDBConnection;
use buffalokiwi\magicgraph\pdo\TransactionUnit;
use Exception;
use InvalidArgumentException;
use TypeError;


/**
 * A counter table that increments the counter value by 1 each time increment() is called.
 */
class MySQLCounter implements ICounter
{
  /**
   * Table name 
   * @var string
   */
  private $table;
  
  /**
   * Database connection 
   * @var IDBConnection 
   */
  private $dbc;
  
  
  /**
   * Create a new MySQLNumber increment.
   * @param string $table Table name 
   * @param IDBConnection $dbc database connection 
   */
  public function __construct( string $table, IDBConnection $dbc )
  {
    if ( empty( $table ) || !preg_match( '/^[a-zA-Z0-9]+$/', $table ))
      throw new InvalidArgumentException( 'Invalid table name' );
    
    $this->table = $table;
    $this->dbc = $dbc;
  }
  
  
  /**
   * Increment a stored number by some offset and return the new value.
   * @param string $key Counter key 
   * @return int value 
   */
  public function increment( string $key ) : int
  {
    $trans = new TransactionUnit( $this->dbc );
    
    try {
      $hasRes = false;
      foreach( $this->dbc->select( 'select * from ' . $this->table . ' where akey=? for update', [$key] ) as $row )
      {
        $hasRes = true;
        break;
      }
      
      if ( !$hasRes )
      {
        try {          
          $this->dbc->insert( $this->table, ['akey' => $key, 'value' => 0] );
          foreach( $this->dbc->select( 'select * from ' . $this->table . ' where akey=? for update', [$key] ) as $row )
          {
            break;
          }          
        } catch( Exception $e ) {
          //..might be a dup key exception, nbd.
        }
      }
      
      //..Increment 
      $this->dbc->execute( 'update ' . $this->table . ' set value=value+1 where akey=?', [$key] );
      
      foreach( $this->dbc->select( 'select value from ' . $this->table . ' where akey=?', [$key] ) as $row )
      {
        $trans->commit();
        return (int)$row['value'];
      }
      
      throw new Exception( 'Failed to increment variable ' . $key );
      
      
    } catch( Exception | TypeError $e ) {      
      $trans->rollBack();
      throw $e;
    }
  }
}
