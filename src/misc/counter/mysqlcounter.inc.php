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

namespace buffalokiwi\magicgraph\misc\counter;

use buffalokiwi\magicgraph\IModelMapper;
use buffalokiwi\magicgraph\pdo\IDBConnection;
use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\persist\SQLRepository;


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
    $useTrans = !$this->dbc->inTransaction();
    if ( $useTrans )
      $this->dbc->beginTransaction();
    
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
        } catch( \Exception $e ) {
          //..might be a dup key exception, nbd.
        }
      }
      
      //..Increment 
      $this->dbc->execute( 'update ' . $this->table . ' set value=value+1 where akey=?', [$key] );
      
      foreach( $this->dbc->select( 'select value from ' . $this->table . ' where akey=?', [$key] ) as $row )
      {
        if ( $useTrans )
          $this->dbc->commit();
        
        return (int)$row['value'];
      }
      
      throw new \Exception( 'Failed to increment variable ' . $key );
      
      
    } catch( Exception | \TypeError $e ) {      
      if ( $useTrans )
        $this->dbc->rollBack();
      
      throw $e;
    }
  }
}
