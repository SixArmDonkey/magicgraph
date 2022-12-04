<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\persist;

use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\buffalotools\types\IBigSet;
use InvalidArgumentException;


/**
 * A sql select part of a query.
 * This may or may not make the cut.  With some effort, this may be handy.
 * @todo determine if this sucks or not.
 * @todo This definitely sucks.  
 */
class SQLSelect 
{
  /**
   * Properties to select 
   * @var IBigSet
   */
  private $properties;
  
  /**
   * Table Name 
   * @var string
   */
  private $table;
  
  /**
   * Column prefix 
   * @var string
   */
  private $prefix = '';
  
  /**
   * Table alias 
   * @var string
   */
  private $alias = '';
  
  
  /**
   * Create a new SQLSelect instance 
   * @param IPropertySet $properties Properties
   * @param string $table Table name 
   */
  public function __construct( IBigSet $properties, string $table, string $alias = '', string $prefix = '' )
  {
    $this->properties = $properties;
    if ( empty( $table ))
      throw new InvalidArgumentException( 'table must not be empty' );
    else if ( !$this->isSafe( $table ))
      throw new InvalidArgumentException( 'Invalid table name' );
    else if ( !$this->isSafe( $alias ))
      throw new InvalidArgumentException( 'Invalid alias' );
    else if ( !$this->isSafe( $prefix ))
      throw new InvalidArgumentException( 'Invalid prefix' );
    
    
    $this->table = $table;
    $this->prefix = $prefix;
    $this->alias = $alias;
  }
  
  
  public function getPrefix()
  {
    return $this->prefix;
  }
  
  
  public function getTable()
  {
    return $this->table . ' ' . (( !empty( $this->alias )) ? ' as ' . $this->alias . ' ' : '' ) . ' ';
  }
  
  
  /**
   * Retrieve the select part of a sql query 
   * @return string statement 
   */
  public function getSelect()
  {    
    return 'select ' . $this->getColumns() . ' from ' . $this->getTable() . ' ';
  }
  
  
  public function getColumns()
  {
    $a = ( !empty( $this->alias )) ? '`' . $this->alias . '`.' : '';
    
    $out = [];
    if ( $this->properties->isEmpty())
      $out[] = '*';
    else
    {
      foreach( $this->properties->getActiveMembers() as $col )
      {
        if ( !$this->isSafe( $col ))
          throw new InvalidArgumentException( $col . ' is an invalid column name' );
        
        $out[] = $a . '`' . $col . '`' . (( !empty( $this->prefix )) ? ' as `' . $this->prefix . '_' . $col . '`' : '' );
      }
    }
    
    return ' ' . implode( ',', $out ) . ' ';
    
  }
  
  
  
  /**
   * Retrieve the select part of a sql query 
   * @return string statement 
   */
  public function __toString()
  {
    return getSelect();
  }
  
  
  /**
   * Detect if a string is only [a-zA-Z0-9_]
   * @param string $s String to check
   * @return boolean is letters
   */
  private function isSafe( $s )
  {
    if ( empty( $s ))
      return true;
    return preg_match( '/^([a-zA-Z0-9_]+)$/', $s );
  }    
}
