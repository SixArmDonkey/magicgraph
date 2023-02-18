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
namespace buffalokiwi\magicgraph\pdo;

use buffalokiwi\magicgraph\DBException;
use InvalidArgumentException;
use PDO;
use \Generator;
use Traversable;


class MariaDBConnection extends PDOConnection implements IDBConnection
{
  /**
   * Build an insert query using a prepared statement.
   * This will work for most queries, but if you need to do something
   * super complicated, write your own sql...
   *
   *
   * @param string $table Table name
   * @param array $pairs Column names and values map
   * @return string last insert id for updates
   * @throws InvalidArgumentException
   * @throws DBException
   */
  public function insert( string $table, array $pairs ) : string
  {
    if ( empty( $table ))
      throw new InvalidArgumentException( 'table can\'t be empty' );
    else if ( empty( $pairs ))
      throw new InvalidArgumentException( 'pairs can\'t be empty' );

    //..Get the data for insert
    list( $keys, $vals, $params ) = $this->getKVP( $pairs );

    //..Build the columns
    $cstr = '`' . implode( '`,`', $keys ) . '`';

    //..Build the values
    $vstr = implode( ',', $vals );

    $sql = "insert into $table ($cstr) values($vstr)";
    $options = $this->prepareOptions( $params );
        
    //..execute    
    $this->prepareAndExec( $sql, $options );
        
    //..Get the last insert id    
    return $this->lastInsertId();
  }

  
  /**
   * Build an update query using a prepared statement.
   * @param string $table Table name
   * @param array $pkPairs list of [primary key => value] for locating records to update.
   * @param array $pairs Column names and values map
   * @param int $limit Limit to this number
   * @return int the number of affected rows
   * @throws InvalidArgumentException
   * @throws DBException
   */
  public function update( string $table, array $pkPairs, array $pairs, int $limit = 1 ) : int
  {
    if ( empty( $table ))
      throw new InvalidArgumentException( 'table can\'t be empty' );
    else if ( empty( $pairs ))
      throw new InvalidArgumentException( 'pairs can\'t be empty' );
    else if ( empty( $pkPairs ))
      throw new InvalidArgumentException( 'pkPairs can\'t be empty' );
    

    //..Get the data for insert
    list( $keys, $vals, $params ) = $this->getKVP( $pairs );

    //..Set cols
    $s = [];

    //..Loop the keys
    foreach( $keys as $k => $c )
    {
      $s[] = '`' . $c . '`=' . $vals[$k];
    }

    
    
    $pkCols = [];
    
    foreach( $pkPairs as $col => $val )
    {
      if ( !$this->isSafe( $col ))
        throw new InvalidArgumentException( 'prikey column is invalid' );
      
      $pkCols[] = '`' . $col . '`=?';
      $params[] = $val;
    }
    
    $sql = "update $table set " . implode( ',', $s ) . " where " . implode( ' and ', $pkCols ) . " order by `$col` limit $limit";
    $options = $this->prepareOptions( $params );

    //..Query
    $stmt = $this->prepareAndExec( $sql, $options );
    
    return $stmt->rowCount();
  }

  
  /**
   * Execute a delete query for a record using a compound key.
   * @param string $table table name
   * @param array $pkPairs primary key to value pairs 
   * @param int $limit limit
   * @return int Always returns 1.
   * @throws InvalidArgumentExcepton if table or col or id are empty or if col
   * contains invalid characters or if limit is not an integer or is less than
   * one
   * @throws DBException if there is a problem executing the query
   */
  public function delete( string $table, array $pkPairs, int $limit = 1 ) : int
  {
    if ( empty( $table ))
      throw new InvalidArgumentException( 'table and col and id can\'t be empty' );
    else if ( empty( $pkPairs ))
      throw new InvalidArgumentException( 'pkPairs must not be empty' );
    
    
    $pkCols = [];
    $params = [];
    
    foreach( $pkPairs as $col => $val )
    {
      if ( !$this->isSafe( $col ))
        throw new InvalidArgumentException( 'prikey column is invalid' );
      
      $pkCols[] = '`' . $col . '`=?';
      $params[] = $val;
    }
        
    
    $sql = "delete from $table where " . implode( ' and ', $pkCols ) . " limit " . $limit;
    $options = $this->prepareOptions( $params );

    $this->prepareAndExec( $sql, $options );
    
    return 1;
  }  
  
  

  /**
   * Select the current database
   * @param string $db Database name
   */
  public function selectdb( string $db ) : void
  {
    if ( $this->isSafe( $db ))
      $this->exec( "use " . $db );
  }


  /**
   * Returns the current database being used
   * @return string Current database name
   */
  public function curdb() : string
  {
    try {
      $data = $this->query(
        "select database() as `db`"
      );

      if ( isset( $data[0]['db'] ))
      {
        return $data[0]['db'];
      }
    } catch ( DBException $e ) {
      //..do nothing
    }

    return '';
  }  
  
  
  
  public function forwardCursor( string $statement, $options = null, $scroll = false ) : Generator
  {
    if ( $this->getAttribute( PDO::ATTR_DRIVER_NAME ) == "mysql" )
      $this->setAttribute( PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false );
    
    return parent::forwardCursor( $statement, $options, $scroll );
  }  
  


  /**
   * This will prepare a list of integers or a single int for a prepared sql
   * query.  This returns '(?)' if $val is an int and '(?,?,...)' if
   * $val is an int[].  if $val is an array and it is not all ints then an
   * InvalidArgumentException is thrown.
   *
   * @param int|array $val value to use
   * @param boolean $allInt set to false to toggle off int checking
   * @throws InvalidArgumentException if val is invalid
   */
  public function prepareIn( $val, bool $allInt = true ) : string
  {
    if ( is_array( $val ) || $val instanceof Traversable )
    {
      if ( $allInt )
      {
        if ( $this->allInt( $val ))
          return $this->pimplode( $val );
        else
          throw new InvalidArgumentException( 'val must contain all integers' );
      }
      else
      {
        return $this->pimplode( $val );
      }
    }
    else if ( $allInt )
    {
      if ( !ctype_digit((string)$val ))
        throw new IllegalArgumentException( "val must be an integer" );
      return '(?)';
    }
    else
    {
      return '(?)';
    }
  }
  

  /**
   * This will extract a columns prefixed by a prefix in prefixList.
   * The returned array will contain an array for each prefix in the list.
   * If any columsn match the format prefix_xxx the the column name will have the
   * prefix_ removed and be added to the output list that matches the prefix
   * in the list.
   *
   * @param array $prefixList List of prefixes to extract
   * @param array $data Row data
   * @return array columns
   */
  public function extractRow( array $prefixList, array $data ) : array
  {
    $out = [];

    //..Set up the output array
    foreach( $prefixList as $prefix )
    {
      $out[$prefix] = [];
    }

    //..Loop the data
    foreach( $data as $col => $val )
    {
      //..Split the prefix off
      $col = explode( '_', $col );

      //..Ensure prefix
      if ( sizeof( $col ) < 2 )
        continue;

      //..get the actual prefix
      $prefix = array_shift( $col );

      //..Put the column name back together
      $col = implode( '_', $col );

      //..Add the column to the output
      if ( isset( $out[$prefix] ))
        $out[$prefix][$col] = $val;
    }

    ksort( $out );
    
    //..return the data
    return $out;
  }
  
  
  /**
   * Prepare the options argument for use in a prepared statement 
   * @param array|object $options
   * @return array
   */
  protected function prepareOptions( $options ) : array 
  {
    $options = parent::prepareOptions( $options );
    
    foreach( $options as &$val )
    {
      if ( $val instanceof \DateTimeInterface )
      {
        $val = $val->format( 'Y-m-d H:i:s' );
        if ( empty( $val ))
          $val = null;
      }
    }
    
    return $options;
  }  
  
  
 
  private function allInt( array $a )
  {
    foreach( $a as $i )
    {
      if ( !ctype_digit((string)$i ))
        return false;
    }
    
    return true;
  }    
}
