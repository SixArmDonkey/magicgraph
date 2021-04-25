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

use buffalokiwi\magicgraph\DBException;
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\search\ISearchQueryBuilder;
use buffalokiwi\magicgraph\search\ISearchQueryGenerator;
use buffalokiwi\magicgraph\search\ISearchResults;
use Closure;
use Generator;
use InvalidArgumentException;


/**
 * Defines a data respository.  This does CRUD.
 */
interface IRepository extends ISaveableObjectFactory
{
  /**
   * Retrieve an IRunnable instance to be used with some ITransaction instance.
   * This runnable will execute the supplied function prior to saving the model.
   *
   * @param Closure $beforeSave What to run prior to saving f( IRepository, ...IModel )
   * @param Closure $afterSave What to run after saving f( IRepository, ...IModel )
   * @param IModel $models One or more models to save 
   * @return array IRunnable[] 
   */
  public function getSaveFunction( ?Closure $beforeSave, ?Closure $afterSave, IModel ...$models ) : array;

  
  /**
   * Create a unit of work against the repo.
   * @param Closure $action f( IRepository $repo ) : void - What to do 
   * @return IRunnable Runnable
   */
  public function createUnitOfWork( Closure $action ) : IRunnable;
  
  
  /**
   * Stream the data one record at a time from the data source.  
   * @param ISearchQueryBuilder $builder Query Parameters
   * @return Generator yielded results 
   * @throws DBException For db errors 
   */
  public function stream( ISearchQueryBuilder $builder ) : \Generator;
  
    
  /**
   * Load some record by primary key 
   * @param string $id id 
   * @return IModel model instance 
   * @throws DBException For db errors 
   * @throws RecordNotFoundException if the record can't be found 
   */
  public function get( string ...$id ) : IModel;
  
  
  /**
   * Retrieve a list of models by a list of primary key values.
   * @param array $idList id list 
   * @return IModel[] found models 
   * @throws DBException For DB Errors 
   */
  public function getAll( array $idList ) : array;  
  
  
  /**
   * Retrieve records by page.
   * @param int $page Page number  
   * @param int $size page size 
   * @return array IModel[] Model
   */
  public function getPage( int $page, int $size = 25, string $orderBy = '' ) : array;   
  
  
  /**
   * Retrieve a list of id's for some property.
   * If primary key is compound, then each returned element will be an array (map) listing
   * each key.
   * @param string $propertyName Property Name 
   * @param string $value value 
   * @return array ids 
   */
  public function getIdsForProperty( string $propertyName, string $value ) : array;
  
  
  /**
   * Retrieve a list of models where some property name matches some value.
   * @param string $propertyName Property name
   * @param mixed $value value  iF value is an array, then this will perform an in query.
   * @return array
   * @throws \Exception 
   * @deprecated use findByProperty 
   */
  public function getForProperty( string $propertyName, $value ) : array;
  
  
  /**
   * Perform a simple search by property name.  
   * 
   * If the engine supports simple searching, this can be used for that.
   * For example: With SQL databases, this can simply be a wildcard search.  
   * Also possible to use full text indexes or whatever else the engine supports. 
   * 
   * Depending on the repo, this could connect to any number of things for searching.
   * 
   * @param string $propertyName Property name 
   * @param string $value Search value.  What this is depends on the engine.
   * @param int $limit Limit the number of results returned.
   * @return array
   * @throws InvalidArgumentException
   * @todo Add offset argument 
   */
  public function findByProperty( string $propertyName, string $value, int $limit = 100 ) : array;  
  
  
  /**
   * Only operating on properties available within this repository, 
   * return any objects matching all of the supplied criteria.
   * @param array $map Map of [property => val]
   * @param int $limit Max results to return 
   * @return array Results 
   */
  public function findByProperties( array $map, int $limit = 100 ) : array;
  
  
  /**
   * Search for something 
   * @param ISearchQueryBuilder $builder Builder
   * @return ISearchResults results 
   */
  public function search( ISearchQueryBuilder $builder ) : ISearchResults;
      
  
  /**
   * Retrieve the estimated record count.  
   * @param bool $full Set to true to retrieve count(*), set to false for max(primary key)
   * @return int estimated number of records 
   */
  public function count( $full = false ) : int;
  
  
  /**
   * Tests to see if some value exists by primary key
   * @param string $id
   * @return bool
   */
  public function exists( string ...$id ) : bool;
  
  
  
  /**
   * Retrieve the search query generator 
   * @return ISearchQueryGenerator generator 
   */
  public function getSearchQueryGenerator() : ISearchQueryGenerator;     
}
