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

use buffalokiwi\magicgraph\DBException;
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\search\ISearchQueryBuilder;
use buffalokiwi\magicgraph\search\ISearchQueryGenerator;
use buffalokiwi\magicgraph\search\ISearchResults;
use Closure;
use Exception;
use Generator;
use InvalidArgumentException;


class RepositoryProxy extends SaveableMappingObjectFactoryProxy implements IRepository
{
  /**
   * Repo 
   * @var IRepository 
   */
  private $repo;
  
  
  /**
   * 
   * @param IRepository $repo
   */
  public function __construct( IRepository $repo )
  {
    parent::__construct( $repo );
    $this->repo = $repo;
  }
  
  
  public function __call( $name, $arguments ) 
  {
    if ( !method_exists( $this, $name ))
      return $this->repo->$name( ...$arguments );
    else
      return $this->$name( ...$arguments );
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
   * Retrieve an IRunnable instance to be used with some ITransaction instance.
   * This runnable will execute the supplied function prior to saving the model.
   *
   * @param Closure $beforeSave What to run prior to saving f( IRepository, ...IModel )
   * @param Closure $afterSave What to run after saving f( IRepository, ...IModel )
   * @param IModel $models One or more models to save 
   * @return IRunnable
   */
  public function getSaveFunction( ?Closure $beforeSave, ?Closure $afterSave, IModel ...$models ) : array
  {
    return $this->repo->getSaveFunction( $beforeSave, $afterSave, ...$models );
  }

  
  /**
   * Retrieve an IRunnable instance to be used with some ITransaction instance.
   * This runnable will execute the supplied function prior to saving the model.
   *
   * @param Closure|null $beforeSave What to run prior to saving f( IRepository, ...IModel )
   * @param Closure|null $afterSave What to run after saving f( IRepository, ...IModel )
   * @param Closure $getModels f() : IModel[]  Retrieve the list of models to save. 
   * @return array IRunnable[] 
   */
  public function getLazySaveFunction( ?Closure $beforeSave, ?Closure $afterSave, Closure $getModels ) : array
  {
    return $this->repo->getLazySaveFunction( $beforeSave, $afterSAve, $getModels );
  }

  
  /**
   * Create a unit of work against the repo.
   * @param Closure $action f( IRepository $repo ) : void - What to do 
   * @return IRunnable Runnable
   */
  public function createUnitOfWork( Closure $action ) : IRunnable
  {
    return $this->repo->createUnitOfWork( $action );
  }  
  
  
  /**
   * Stream the data one record at a time from the data source.  
   * @param ISearchQueryBuilder $builder Query Parameters
   * @return Generator yielded results 
   * @throws DBException For db errors 
   */
  public function stream( ISearchQueryBuilder $builder ) : \Generator
  {
    return $this->repo->stream( $builder );
  }
  
    
  /**
   * Load some record by primary key 
   * @param string $id id 
   * @return IModel model instance 
   * @throws DBException For db errors 
   * @throws RecordNotFoundException if the record can't be found 
   */
  public function get( string ...$id ) : IModel
  {
    return $this->repo->get( ...$id );
  }
  
  
  /**
   * Retrieve a list of models by a list of primary key values.
   * @param array $idList id list 
   * @return IModel[] found models 
   * @throws DBException For DB Errors 
   */
  public function getAll( array $idList ) : array
  {
    return $this->repo->getAll( $idList );
  }
  
  
  /**
   * Retrieve user records by page.
   * @param int $page Page number  
   * @param int $size page size 
   * @return array IModel[] Model
   */
  public function getPage( int $page, int $size = 25, string $orderBy = '' ) : array
  {
    return $this->repo->getPage( $page, $size, $orderBy );
  }
  
  
  /**
   * Retrieve a list of id's for some property.
   * If primary key is compound, then each returned element will be an array (map) listing
   * each key.
   * @param string $propertyName Property Name 
   * @param string $value value 
   * @return array ids 
   */
  public function getIdsForProperty( string $propertyName, string $value ) : array
  {
    return $this->repo->getIdsForProperty( $propertyName, $value );
  }
  
  
  /**
   * Retrieve a list of models where some property name matches some value.
   * @param string $propertyName Property name
   * @param mixed $value value
   * @return array
   * @throws Exception 
   */
  public function getForProperty( string $propertyName, $value ) : array
  {
    return $this->repo->getForProperty( $propertyName, $value );
  }
  
  
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
   * @param int $liimt Limit the number of results returned.
   * @return array
   * @throws InvalidArgumentException
   */
  public function findByProperty( string $propertyName, string $value, int $limit = 100 ) : array
  {
    return $this->repo->findByProperty( $propertyName, $value, $limit );
  }
  
  
  /**
   * Only operating on properties available within this repository, 
   * return any objects matching all of the supplied criteria.
   * @param array $map Map of [property => val]
   * @param int $limit Max results to return 
   * @return array Results 
   */
  public function findByProperties( array $map, int $limit = 100, int $offset = 0 ) : array
  {
    return $this->repo->findByProperties( $map, $limit, $offset );
  }
  
  
  /**
   * Retrieve the estimated record count.  
   * @param bool $full Set to true to retrieve count(*), set to false for max(primary key)
   * @return int estimated number of records 
   */
  public function count( $full = false ) : int
  {
    return $this->repo->count( $full );
  }
  
  
  /**
   * Tests to see if some value exists by primary key
   * @param string $id
   * @return bool
   */
  public function exists( string ...$id ) : bool
  {
    return $this->repo->exists( ...$id );
  }
  
  
  /**
   * Search for something.
   * @param ISearchQueryBuilder $query The search parameters 
   * @return ISearchResults results 
   */
  public function search( ISearchQueryBuilder $query ) : ISearchResults
  {
    return $this->repo->search( $query );
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
   * Retrieve a search query builder appropriate for the repository
   * @return ISearchQueryBuilder
   */
  public function getSearchQueryBuilder() : ISearchQueryBuilder
  {
    return $this->repo->getSearchQueryBuilder();
  }  
  
  
  /**
   * Specify columns to select.
   * @param string $names Zero or more names.  Leave names empty to select all columns.
   * @return IRepository this 
   */
  public function select( string ...$names ) : IRepository
  {
    return $this->repo->select( ...$names );
  }
}
