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
namespace buffalokiwi\magicgraph\pdo;

use buffalokiwi\magicgraph\DBException;
use Generator;


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
  public function multiSelect( string $sql ) : Generator;


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
}
