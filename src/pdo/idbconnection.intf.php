<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 *  Database
 * @author John Quinn
 */

namespace buffalokiwi\magicgraph\pdo;

use buffalokiwi\magicgraph\DBException;
use Closure;
use Generator;
use \InvalidArgumentException;



/**
 * Defines a generic database connection
 */
interface IDBConnection extends IPDOConnection
{   
  /**
   * A unique connection id generated each time a new instance of some IDBConnection is
   * created.  Implementations MUST ensure the returned value is unique.
   * @return string id
   */
  public function getConnectionId() : string;
  
  /**
   * Create a forward-only cursor.  Used to stream database results.
   * @param string $statement sql statement 
   * @param Closure $callback for each record 
   * @param array $options bindings.  object or array
   * @param bool $scroll scroll 
   * @return Generator Results rows 
   */
  public function forwardCursor( string $statement, $options = null, $scroll = false ) : Generator;
  
  /**
   * Select some stuff from some database
   * @param string $statement sql statement
   * @param type $opt Bindings for prepared statement.  This can be an object or an array 
   */
  
  public function select( string $statement, $opt = null ) : \Generator;
  

  /**
   * Execute some query with no result set.
   * @param string $statement Query 
   * @param array|object $opt Bindings 
   * @return int Affected rows
   */  
  public function execute( string $statement, $opt = null ) : int;
  
  
  /**
   * Build an insert query using a prepared statement.
   * This will work for most queries, but if you need to do something
   * super complicated, write your own sql...
   *
   *
   * @param string $table Table name
   * @param array $pairs Column names and values map
   * @return int last insert id for updates
   * @throws InvalidArgumentException
   * @throws DBException
   */
  public function insert( string $table, array $pairs ) : string;

  /**
   * Build an update query using a prepared statement.
   * @param string $table Table name
   * @param string $col Unique column name
   * @param mixed $id Unique id
   * @param array $pairs Column names and values map
   * @param int $limit Limit to this number
   * @return int the number of affected rows
   * @throws InvalidArgumentException
   * @throws DBException
   */
  //public function update( string $table, string $col, string $id, array $pairs, int $limit = 1 ) : int;

  
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
   public function update( string $table, array $pkPairs, array $pairs, int $limit = 1 ) : int;

  /**
   * Execute a delete query
   * @param string $table table name
   * @param string $col column name
   * @param mixed $id unique id
   * @param int $limit limit
   * @return int affected rows
   * @throws InvalidArgumentExcepton if table or col or id are empty or if col
   * contains invalid characters or if limit is not an integer or is less than
   * one
   * @throws DBException if there is a problem executing the query
   */
  //public function delete( string $table, string $col, string $id, int $limit = 1 ) : int;

  
  /**
   * Execute a delete query for a record using a compound key.
   * @param string $table table name
   * @param array $pkPairs primary key to value pairs 
   * @param int $limit limit
   * @return int affected rows
   * @throws InvalidArgumentExcepton if table or col or id are empty or if col
   * contains invalid characters or if limit is not an integer or is less than
   * one
   * @throws DBException if there is a problem executing the query
   */
  public function delete( string $table, array $pkCols, int $limit = 1 ) : int;
  
  
  /**
   * This will prepare a list of integers or a single int for a prepared sql
   * query.  This returns '(?)' if $val is an int and '(?,?,...)' if
   * $val is an int[].  if $val is an array and it is not all ints then an
   * InvalidArgumentException is thrown.
   *
   * @param int|array $val value to use
   * @param boolean $allInt set to false to toggle off int checking
   * @return string in statement 
   * @throws InvalidArgumentException if val is invalid
   */
  public function prepareIn( $val, bool $allInt = true ) : string;

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
  public function extractRow( array $prefixList, array $data ) : array;
}
