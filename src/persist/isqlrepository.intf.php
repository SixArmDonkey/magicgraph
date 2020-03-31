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

use buffalokiwi\buffalotools\types\IBigSet;
use buffalokiwi\magicgraph\DBException;
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\pdo\IDBConnection;


interface ISQLRepository extends IRepository
{
  /**
   * Query the data source.
   * @param IBigSet $properties Properties to return 
   * @param IFilter $filter Filters to use 
   * @param IRows $rows Sort order and limit 
   * @return IModel[] model instances
   * @throws DBException For db errors
   * @deprecated Query builders suck.  SQL is a damn good query builder...  To be removed.
   */
  public function query( IBigSet $properties, ISQLFilter $filter, IRows $rows = null ) : array;
    
  
  /**
   * Retrieve the database table name backing this repository.
   * @return string database table name
   */
  public function getTable() : string;
  
  
  /**
   * Retrieve the database connection 
   * @return IDBConnection database connection 
   */
  public function getDatabaseConnection() : IDBConnection;
  

  /**
   * Lock the table via "lock tables".
   * Disables autocommit.
   * @return void
   */
  public function lockTable() : void;
  
  
  /**
   * Unlock the tables obtained by lockTable()
   * This also commits.
   * @return void
   */
  public function unlockTable() : void;
  
  
  /**
   * Attempts to obtain a lock for this table via GET_LOCK().
   * This blocks until one can be obtained.
   * @return void
   */
  public function getLock() : void;
  
  
  /**
   * Release the lock obtained by getLock()
   * @return void
   */
  public function releaseLock() : void;  
  

  /**
   * If the table is locked via lock tables.
   * @return bool locked 
   */
  public function isTableLocked() : bool;
  
  
  /**
   * If there is a lock in effect via GET_LOCK()
   * @return bool is locked 
   */
  public function isLocked() : bool;  
  
  
 
}
