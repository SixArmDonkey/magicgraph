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

use buffalokiwi\magicgraph\pdo\IDBConnection;
use buffalokiwi\magicgraph\search\ISearchQueryGenerator;


class SQLRepositoryProxy extends RepositoryProxy implements ISQLRepository
{
  /**
   * repo
   * @var ISQLRepository
   */
  private $repo;
  
  public function __construct( IRepository $repo )
  {
    parent::__construct( $repo );
    $this->repo = $repo;
  }
  
  
  /**
   * This should return something like 'mysql' or 'sqlserver' or 'redis' or 'lucene' or whatever.
   * Each IRepository implementation should return the type here.
   * @return string the type 
   */
  public function getPersistenceType() : string
  {
    return $this->repo->getPersistenceType();
  }
    
  
  /**
   * Retrieve the database table name backing this repository.
   * @return string database table name
   */
  public function getTable() : string
  {
    return $this->repo->getTable();
  }
  
  
  /**
   * Retrieve the database connection 
   * @return IDBConnection database connection 
   */
  public function getDatabaseConnection() : IDBConnection
  {
    return $this->repo->getDatabaseConnection();
  }
  
  
  /**
   * Lock the table via "lock tables".
   * Disables autocommit.
   * @return void
   */
  public function lockTable() : void
  {
    $this->repo->lockTable();
  }
  
  
  /**
   * Unlock the tables obtained by lockTable()
   * This also commits.
   * @return void
   */
  public function unlockTable() : void
  {
    $this->repo->unlockTable();
  }
  
  
  /**
   * Attempts to obtain a lock for this table via GET_LOCK().
   * This blocks until one can be obtained.
   * @return void
   */
  public function getLock() : void
  {
    $this->repo->getLock();
  }
  
  
  /**
   * Release the lock obtained by getLock()
   * @return void
   */
  public function releaseLock() : void
  {
    $this->repo->releaseLock();
  }
  
  
  /**
   * If the table is locked via lock tables.
   * @return bool locked 
   */
  public function isTableLocked() : bool
  {
    return $this->repo->isTableLocked();
  }
  
  
  /**
   * If there is a lock in effect via GET_LOCK()
   * @return bool is locked 
   */
  public function isLocked() : bool
  {
    return $this->repo->isLocked();
  }
  
  
  /**
   * Retrieve user records by page.
   * @param int $page Page number  
   * @param int $size page size 
   * @return array IUser[] Users 
   */
  public function getPage( int $page, int $size = 25, string $orderBy = '' ) : array
  {
    return $this->repo->getPage( $page, $size, $orderBy );
  }
  
  
  /**
   * Get the stored repo 
   * @return ISQLRepository
   * @final 
   */
  protected final function getRepo() : ISQLRepository
  {
    return $this->repo;
  }
  
  
  /**
   * Retrieve the search query generator 
   * @return ISearchQueryGenerator generator 
   */
  public function getSearchQueryGenerator() : ISearchQueryGenerator
  {
    return $this->repo->getSearchQueryGenerator();
  }
  
  
  /**
   * Specify columns to select.
   * @param string $names Zero or more names.  Leave names empty to select all columns.
   * @return ISQLRepository this 
   */
  public function select( string ...$names ) : ISQLRepository
  {
    return $this->repo->select( ...$names );
  }
}
