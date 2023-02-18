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
use Generator;
use InvalidArgumentException;


interface IPDOConnection extends IPDO
{
  /**
   * Access the args
   * @return IConnectionProperties args
   */
  public function getProperties() : IConnectionProperties;
  

  /**
   * Close the database connection
   */
  public function close() : void;
  
  
  /**
   * Select the current database
   * @param string $db Database name
   */
  public function selectdb( string $db ) : void;


  /**
   * Returns the current database being used
   * @return string Current database name
   */
  public function curdb() : string;


  /**
   * Execute a sql statement that has multiple result sets
   * ie: a stored procedure that has multiple selects, or one of those snazzy
   * subquery statements
   * @param string $sql SQL statement to execute
   * @return Generator array results
   * @throws DBException if there is one
   */
  public function multiSelect( string $sql, array $bindings = [] ) : Generator;


  /**
   * Retrieve the last sql statement that was used
   * @return string last statement
   */
  public function getLastStatement() : string;


  /**
   * Retrieve the last set of options used
   * @return array opts
   */
  public function getLastOpts() : array;
  
  
  /**
   * Set auto commit if supported by the driver.
   * @param bool $on on or off 
   * @return void
   */
  public function setAutoCommit( bool $on ) : void;  
    

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
   * Creates a cursor over some result set 
   * @param string $statement Statement 
   * @param type $options Parameters
   * @param type $scroll Enable Scroll 
   * @return Generator Results 
   */
  public function forwardCursor( string $statement, $options = null, $scroll = false ) : Generator;  
  
  
  /**
   * Select some stuff from some database
   * @param string $statement sql statement
   * @param type $opt Bindings for prepared statement.  This can be an object or an array 
   */ 
  public function select( string $statement, $opt = null ) : \Generator;  
  
  
  /**
   * Executes a query with no result set.
   * @param string $statement
   * @param type $opt
   * @return int
   */
  public function execute( string $statement, $opt = null ) : int;
}
