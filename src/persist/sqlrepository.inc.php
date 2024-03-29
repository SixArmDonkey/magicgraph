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

namespace buffalokiwi\magicgraph\persist;

use buffalokiwi\buffalotools\types\IBigSet;
use buffalokiwi\magicgraph\DBException;
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\IModelMapper;
use buffalokiwi\magicgraph\pdo\IDBConnection;
use buffalokiwi\magicgraph\pdo\TransactionUnit;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\search\ISearchQueryBuilder;
use buffalokiwi\magicgraph\search\ISearchQueryGenerator;
use buffalokiwi\magicgraph\search\ISearchResults;
use buffalokiwi\magicgraph\search\MySQLSearchQueryGenerator;
use buffalokiwi\magicgraph\search\MySQLSearchResults;
use buffalokiwi\magicgraph\search\SearchQueryBuilder;
use buffalokiwi\magicgraph\ValidationException;
use Closure;
use Generator;
use InvalidArgumentException;
use TypeError;
use function json_encode;


/**
 * A repository for a MySQL database table.
 * This is meant for SIMPLE database operations on single tables (relationship providers can provide join functionality).
 * If you are joining tables, doing fancy things, etc then please create a 
 * new repository and write some beautiful, hand-crafted, SQL.
 * 
 * @todo Refactor this and make this a SQL base class.  Create a engine specific implementations containing sql statements.
 */ 
class SQLRepository extends SaveableMappingObjectFactory implements ISQLRepository
{
  private const TYPE = 'mysql';
  
  /**
   * Table name 
   * @var string
   */
  private string $table;
  
  /**
   * Database connection 
   * @var IDBConnection 
   */
  private IDBConnection $dbc;
  
  /**
   * If the table is locked 
   * @var bool 
   */
  private bool $locked = false;
  
  /**
   * If a lock has been obtained via GET_LOCK()
   * @var bool 
   */
  private bool $hasMutexLock = false;
  
  /**
   * Search query generator 
   * @var ISearchQueryGenerator
   */
  private ISearchQueryGenerator $searchQueryGenerator;
  
  private bool $testExists;
  
  
  private ?TransactionUnit $transaction = null;
  
  /**
   * SQL Repository 
   * @param string $table Table name 
   * @param IModelMapper $mapper Model mapper 
   * @param IDBConnection $dbc Database connection 
   * @param IPropertySet|null $properties Property set for models returned by this repo.  This SHOULD be supplied.
   * @param ISearchQueryGenerator|null $searchQueryGenerator A search query generator.  If none is specified, then a 
   * MySQLSearchQueryGenerator instance is used.
   * @throws InvalidArgumentException
   */
  public function __construct( string $table, IModelMapper $mapper, IDBConnection $dbc, 
    ?IPropertySet $properties = null, ?ISearchQueryGenerator $searchQueryGenerator = null,
    bool $testExists = false )
  {
    parent::__construct( $mapper, $properties );
    
    if ( empty( $table ))
      throw new InvalidArgumentException( 'table must not be empty' );
    else if ( !preg_match('/^[A-Za-z0-9_]+/', $table ))
      throw new InvalidArgumentException( 'Invalid table name' );
    
    $this->testExists = $testExists;
    $this->table = $table;
    $this->dbc = $dbc;
    
    if ( $properties == null )
      $properties = $mapper->createAndMap([])->getPropertySet();    
    
    if ( $searchQueryGenerator == null )
      $this->searchQueryGenerator = new MySQLSearchQueryGenerator( $table, $properties, $dbc );
    else
      $this->searchQueryGenerator = $searchQueryGenerator;
  }
  
  
  /**
   * This should return something like 'mysql' or 'sqlserver' or 'redis' or 'lucene' or whatever.
   * Each IRepository implementation should return the type here.
   * @return string the type 
   */
  public function getPersistenceType() : string
  {
    return self::TYPE;
  }
  
  
  /**
   * Specify columns to select.
   * @param string $names Zero or more names.  Leave names empty to select all columns.
   * @return ISQLRepository this 
   */
  public function select( string ...$names ) : ISQLRepository
  {
    return parent::select( ...$names );
  }
  
  
  
  /**
   * Retrieve the search query generator 
   * @return ISearchQueryGenerator generator 
   */
  public function getSearchQueryGenerator() : ISearchQueryGenerator
  {
    return $this->searchQueryGenerator;
  }

  
  /**
   * Retrieve the database table name backing this repository.
   * @return string database table name
   */
  public function getTable() : string
  {
    return $this->table;
  }
  
  
  /**
   * Lock the table via "lock tables".
   * Disables autocommit.
   * @return void
   * @todo Do we want to force an unlock within a shutdown function?  Does the driver auto unlock?  
   */
  public function lockTable() : void
  {
    if ( $this->dbc->inTransaction())
      throw new \Exception( 'Locking tables causes an implicit commit, and there is currently an active transaction.' );
    
    if ( $this->locked )
      return;
    $this->dbc->execute( 'set autocommit=0' );
    $this->dbc->execute( 'lock table ' . $this->getTable() . ' write' );    
    $this->locked = true;
  }
  
  
  /**
   * Unlock the tables obtained by lockTable()
   * This also commits.
   * @return void
   */
  public function unlockTable() : void
  {
    if ( $this->dbc->inTransaction())
      throw new \Exception( 'Unlock tables causes an implicit commit, and there is currently an active transaction.' );
    
    $this->locked = false;
    $this->dbc->execute( 'unlock tables' );
  }
  
  
  /**
   * Attempts to obtain a lock for this table via GET_LOCK().
   * This blocks until one can be obtained.
   * @return void
   */
  public function getLock() : void
  {
    if ( $this->hasMutexLock )
      return;
    
    $this->dbc->execute( 'select GET_LOCK(\'' . $this->getTable() . '\',-1)' );
    $this->hasMutexLock = true;
  }
  
  
  /**
   * Release the lock obtained by getLock()
   * @return void
   */
  public function releaseLock() : void
  {
    if ( $this->hasMutexLock )
    {
      $this->dbc->execute( 'select RELEASE_LOCK(\'' . $this->getTable() . '\')' ); 
      $this->hasMutexLock = false;      
    }
  }
  
  
  /**
   * If the table is locked via lock tables.
   * @return bool locked 
   */
  public function isTableLocked() : bool
  {
    return $this->locked;
  }
  
  
  /**
   * If there is a lock in effect via GET_LOCK()
   * @return bool is locked 
   */
  public function isLocked() : bool
  {
    return $this->hasMutexLock;
  }
  
  
  
  /**
   * Retrieve the database connection 
   * @return IDBConnection database connection 
   */
  public function getDatabaseConnection() : IDBConnection
  {
    return $this->dbc;
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
    return [new MySQLRunnable( $this, $this->getSaveClosure( $beforeSave, $afterSave, ...$models ))];
  }
  
  
  /**
   * Retrieve an IRunnable instance to be used with some ITransaction instance.
   * This runnable will execute the supplied function prior to saving the model.
   *
   * @param Closure|null $beforeSave What to run prior to saving f( IRepository, ...IModel )
   * @param Closure|null $afterSave What to run after saving f( IRepository, ...IModel )
   * @param Closure $getModels Retrieve the list of models to save. 
   * @return array IRunnable[] 
   */
  public function getLazySaveFunction( ?Closure $beforeSave, ?Closure $afterSave, Closure $getModels ) : array
  {
    return [new MySQLRunnable( $this, $this->getLazySaveClosure( $beforeSave, $afterSave, $getModels ))];
  }
  

  
  /**
   * Create a unit of work against the repo.
   * @param \Closure $action f( IRepository $repo ) : void - What to do 
   * @return IRunnable Runnable
   */
  public function createUnitOfWork( \Closure $action ) : IRunnable
  {
    //..Do this to avoid unintended shenanigans.
    $repo = $this;
    
    return new MySQLRunnable( $this, function() use ($action,$repo) : void {
      $action( $repo );
    });
  }
  
  
  /**
   * Stream the data one record at a time from the data source.  
   * @param ISearchQueryBuilder $builder Query Parameters
   * @return Generator yielded results 
   * @throws DBException For db errors 
   */
  public function stream( ISearchQueryBuilder $builder ) : \Generator
  {    
    $builder->setLimitEnabled( false );
    $query = $this->searchQueryGenerator->createQuery( $builder );
    return $this->dbc->forwardCursor( $query->getQuery(), $query->getValues());
  }
  
  
  /**
   * Search for something.
   * @param ISearchQueryBuilder $query The search parameters 
   * @return ISearchResults results 
   */
  public function search( ISearchQueryBuilder $query ) : ISearchResults
  {
    $f = function( ISearchQueryBuilder $query, bool $returnCount ) {
      $statement = $this->searchQueryGenerator->createQuery( $query, $returnCount );
      $build = [];
      
      $entityGroups = $query->getEntityGroups();

      foreach( $this->dbc->select( $statement->getQuery(), $statement->getValues()) as $row )
      {
        $curGroup = '';
        foreach( $entityGroups as $g )
        {
          if ( isset( $row[$g] ))
            $curGroup .= $row[$g];
        }        
        
        foreach( $row as $col => $val )
        {
          if ( $returnCount && $col == 'count' )
            return (int)$val;
          else if ( $returnCount )
            continue;        
          
          //..This needs to be revised.
          //..Not really sure why this is here.
          //..This probably has something to do with the EAV package
          //..It uses 'code' as the attribute code property name, and 'value' as the mixed value property name.
          if ( $col == 'code' && isset( $row['value'] ))
          {
            $col = $val;
            $val = $row['value'];
          }

          //..This needs to be revised.
          //..What is this for.
          if ( empty( $col ) || $col === null || $col == 'value' )
            continue;

          $build[$curGroup][$row[$statement->getUniqueId()]][$col] = $val;
        }
        
        if ( $returnCount )
          return 0;
      }
      
      return $build;
    };
    
    $out = [];
    
    $build = $f( $query, false );
    
        
    foreach( $build as $group )
    {
      foreach( $group as $eid => $cols )
      {
        $out[] = $this->create( $cols );
      }    
    }
        
    return new MySQLSearchResults( $query->getPage(), $query->getResultSize(), function() use($f, $query) {
      return $f( $query, true );
    }, ...$out );         
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
    $keys = $this->properties()->getPrimaryKeys();
    
    if ( empty( $keys ))
      throw new \Exception( "There are no primary keys defined in this property set.  Please defined one to use the IRepository::get() method.");
    
    $q = [];
    $vals = [];
    foreach( $keys as $k => $pri )
    {
      if ( !$this->isSafe( $pri->getName()))
        throw new InvalidArgumentException( 'Invalid primary key name' );
      
      if ( !isset( $id[$k] ))
        break;
      
      $q[] = $pri->getName() . '=?';
      $vals[] = $id[$k];
    }
    
    foreach( $this->dbc->select((new SQLSelect( $this->getSelect(), $this->table ))->getSelect()
      . ' where ' . implode( ' and ', $q ), $vals ) as $row )
    {
      return $this->create( $row );
    }
    
    throw new RecordNotFoundException( 'Record with id: ' . implode( ',', $id ) . ' does not exist' );
  }
    
  
  /**
   * Retrieve a list of id's for some property.
   * If primary key is compound, then each returned element will be an array (map) listing
   * each key.
   * @param string $propertyName Property Name 
   * @param string $value value 
   * @return array ids 
   * @todo Make this work with in and null values.
   */
  public function getIdsForProperty( string $propertyName, string $value ) : array
  {
    if ( !$this->properties()->isMember( $propertyName ))
      throw new \InvalidArgumentException( 'propertyName is not a valid property for this model' );
    
    $priKeys = [];
    foreach( $this->properties()->getPrimaryKeys() as $key )
    {
      /* @var $key IProperty */
      if ( !$this->isSafe( $key->getName()))
        throw new \Exception( 'Primary key contains invalid characters' );
      
      $priKeys[] = $key->getName();
    }
    
    if ( empty( $priKeys ))
      throw new \Exception( 'There are no primary keys defined for this model/repository' );
    
    $out = [];
    
    $multiple = sizeof( $priKeys ) > 1;
    
    foreach( $this->dbc->select( 'select ' . implode( ',', $priKeys ) . ' from ' . $this->table 
      . ' where ' . $propertyName . '=?', [$value] ) as $row )
    {
      if ( $multiple )
      {
        $a = [];
        foreach( $priKeys as $k )
        {
          $a[$k] = $row[$k];
        }
        $out[] = $a;
      }
      else
        $out[] = $row[$priKeys[0]];
    }    
    
    return $out;
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
    return $this->findByProperties([$propertyName => $value], $limit );  
  }
  
  
  /**
   * Only operating on properties available within this repository, 
   * return any objects matching all of the supplied criteria.
   * @param array $map Map of [property => val]. Can also be [property => [val, 'operator']] or something like
   * [property => [[1,2,3], 'in']].  fun times.
   * @param int $limit Max results to return 
   * @return array Results 
   */
  public function findByProperties( array $map, int $limit = 100, int $offset = 0 ) : array
  {
    if ( !$this->properties()->isMember( ...array_keys( $map )))
    {
      $c = get_class( $this->mapper()->createAndMap( [], $this->properties()));
      throw new \InvalidArgumentException( 'A supplied property is not a valid member of ' . $c );
    }

    $conditions = [];
    $values = [];
    
    foreach( $map as $col => $val )
    {
      $op = null;
      if ( is_array( $val ) && sizeof( $val ) == 2 )
      {
        $ev = end( $val );
        if ( is_string( $ev ))
        {
          try {
            $op = new ESQLOperator( $ev );
            $val = reset( $val );
          } catch( \InvalidArgumentException $e ) {
            //..do nothing
          }
        }
      }
      
      if ( !is_scalar( $val ) && !is_array( $val ))
        throw new \InvalidArgumentException( 'Values must be scalar or array' );
      
      //..Use equals if there are no wildcards.
      if ( is_array( $val ))
      {
        if ( $op == null || $op->is( ESQLOperator::IN, ESQLOperator::NOT_IN ))
        {
          /**
           * @todo getType() may be deprecated.  Base data type on detected type of the value instead of getType(), which is more betterer anyway.
           */
          
          $_op = ( $op == null ) ? 'in' : $op->value();
          $conditions[] = $col . ' ' . $_op . ' ' . $this->dbc->prepareIn( $val, $this->properties()->getProperty( $col )->getType()->is( IPropertyType::TINTEGER ));
        }
        else
        {
          $conditions[] = $op->getOperatorAndValue( $val );
        }
          
        $values = array_merge( $values, $val );
      }
      else if ( $op != null )
      {
        $conditions[] = $op->getOperatorAndValue( $val );
        $values[] = $val;
      }
      else if ( strpos((string)$val, '%' ) === false )
      {        
        $conditions[] = $col . ' = ? ';
        $values[] = $val;
      }
      else
      {
        $conditions[] = $col . ' like ? ';
        $values[] = $val;
      }            
    }

    if ( empty( $conditions ))
      return [];
    
    $where = ' where ' . implode( ' and ', $conditions );
    
    if ( $offset < 0 )
      $offset = 0;
    
    if ( $limit > 0 )
      $where .= ' limit ' . $offset . ',' . $limit;
    

    $out = [];
    
    foreach( $this->dbc->select((new SQLSelect( $this->getSelect(), $this->table ))->getSelect() . $where, $values ) as $row )
    {
      $out[] = $this->create( $row );
    }    
    
    return $out;        
  }
  
  
  
  /**
   * Retrieve a list of models where some property name matches some value.
   * 
   * If value is an array, this uses the "in" operator 
   * if the value is null, this tests for "is null"
   * All other values use the "=" operator.
   * 
   * As with everything in magic graph, NEVER feed user input into a property name argument.  ALWAYS use the whitelisted
   * column names contained within a property set.
   * 
   * @param string $propertyName Property name
   * @param mixed $value value
   * @return array
   * @throws \Exception 
   * @deprecated use findByProperty 
   */
  public function getForProperty( string $propertyName, $value ) : array
  {
    if ( is_array( $value ))
    {
      $where = ' in ' . $this->dbc->prepareIn( $value, false );
    }
    else if ( is_null( $value ))
    {
      $where = ' is null ';
      $value = [];
    }
    else
    {      
      $where = ' =? ';
      $value = [(string)$value];
    }
    
    if ( !$this->properties()->isMember( $propertyName ))
    {
      $c = get_class( $this->mapper()->createAndMap( [], $this->properties()));
      throw new \InvalidArgumentException( $propertyName . ' is not a valid property of ' . $c );
    }
    

    $out = [];
    
    foreach( $this->dbc->select((new SQLSelect( $this->getSelect(), $this->table ))->getSelect()
      . ' where ' . $propertyName . $where, $value ) as $row )
    {
      $out[] = $this->create( $row );
    }    
    
    return $out;
  }
  
  
  
  /**
   * Tests to see if some value exists by primary key
   * @param string $id
   * @return bool
   */
  public function exists( string ...$id ) : bool  
  {
    $keys = $this->properties()->getPrimaryKeys();
    
    if ( empty( $keys ))
      throw new \Exception( "There are no primary keys defined in this property set.  Please defined one to use the IRepository::get() method.");
    
    $q = [];
    $vals = [];
    $priName = '';
    foreach( $keys as $k => $pri )
    {
      if ( !$this->isSafe( $pri->getName()))
        throw new InvalidArgumentException( 'Invalid primary key name' );
      
      if ( !isset( $id[$k] ))
        break;
      
      if ( empty( $priName ))
        $priName = $pri->getName();
      
      $q[] = $pri->getName() . '=?';
      $vals[] = $id[$k];
    }
        
    foreach( $this->dbc->select( 'select ' . $priName . ' from ' . $this->table 
      . ' where ' . implode( ' and ', $q ), $vals ) as $row )
    {
      return true;
    }
    
    return false;    
  }
  
  
  
  /**
   * Retrieve a list of models by a list of primary key values.
   * If multiple primary key columns are defined, this simply uses the first one that 
   * was defined in the list of primary keys.  Use query() for compound keys.
   * @param array $idList id list 
   * @return IModel[] found models 
   * @throws DBException For DB Errors 
   */
  public function getAll( array $idList ) : array
  {
    if ( empty( $idList ))
      return [];
    
    $keys = $this->properties()->getPrimaryKeys();
    $pri = reset( $keys );
    if ( !$this->isSafe( $pri->getName()))
      throw new InvalidArgumentException( 'Invalid primary key name' );
    
    $cond = new SQLCondition( $pri->getName(), ESQLOperator::IN(), $idList );
    
    $out = [];
    foreach( $this->dbc->select((new SQLSelect( $this->getSelect(), $this->table ))->getSelect() . ' where ' . $cond->getCondition(), $idList ) as $row )
    {
      $out[] = $this->create( $row );
    }
    
    return $out;
  }
  
  
  
  protected function beginTransaction() : void
  {
    if ( $this->transaction != null )
      return;
    $this->transaction = new TransactionUnit( $this->dbc );
  }
  
  
  protected function commitTransaction() : void
  {
    if ( $this->transaction != null )
    {
      $this->transaction->commit();
      $this->transaction = null;
    }
  }
  
  
  protected function rollbackTransaction() : void
  {
    if ( $this->transaction != null )
    {
      $this->transaction->rollBack();
      $this->transaction = null;
    }
  }  
  
  
  /**
   * Save some record.
   * If the primary key value is specified, this is considered to be an update.
   * Otherwise, this is considered to be an insert.
   * 
   * @param IModel $model Model to save 
   * @throws DBException For DB errors 
   * 
   * @todo This contains some system-specific code that should really not be here. Consider moving most of this to some generic location, and passing the columns to insert/update to this method instead of processing them here.
   */
  protected function saveModel( IModel $model ) : void
  {
    $this->test( $model );
    
    
    //..Get the primary key list 
    $priKeys = $model->getPropertySet()->getPrimaryKeys();
    
    if ( empty( $priKeys ))
      throw new DBException( 'Missing primary key definitions attached to IPropertySet for ' . get_class( $model ) . ' in table ' . $this->getTable());

    //..Primary Key pairs for update query 
    $updKeys = [];
    
    //..If all defined primary key properties have a value within the model
    /* @var bool $hasPriValue */
    $hasPriValue;
    
    //..If this is a compound key, then we need to load up the row from the db to see if it exists first.
    if ( sizeof ( $priKeys ) > 1 )
      $hasPriValue = $this->doesRecordExistByCompoundPrimaryKey( $model, $priKeys );
    else 
    {
      $v = $model->getValue( $model->getPropertySet()->getPrimaryKey()->getName());
      $hasPriValue = !empty( $v ) && $v !== '0';
    }
    
    
    //..Check each pri key 
    foreach( $priKeys as $priKey )
    {
      //..Set the primary key pair 
      $updKeys[$priKey->getName()] = $model->getValue( $priKey->getName());
    }
    
    /**
     * There needs to be special processing for some arrays.
     */
    
    
    $doInsert = false;
    if ( $this->testExists )
    {
      $k = [];
      foreach( $updKeys as $u )
      {
        $k[] = (string)$u;
      }
      
      try {
        $this->get( ...$k );
      } catch( RecordNotFoundException $e ) {
        $doInsert = true;
      }
    }
    
    if ( !$hasPriValue || $doInsert )
    {
      $insProps = $this->getInsertProperties( $model );

      //..So...What happens if the model implementation returns unsanitized user data with this toArray() call.
      //..Probably some nasty fucking things.  
      $toSave = [];
      foreach( $model->toArray( $insProps ) as $k => $v )
      {
        //..Double check that toArray() returned friendly properties 
        //
        //..This may throw an exception prior to the member check.  
        $p = $model->getPropertySet()->getProperty( $k );
        
        if ( $k == $p->getPrefix() || $k . '_' == $p->getPrefix())
        {
          continue;
        }
        
        if ( $model->getPropertySet()->isMember( $k ))
        {
          $toSave[$k] = $v;
        }
      }


      foreach( $insProps->getActiveMembers() as $member )
      {
        $prop = $model->getPropertySet()->getProperty( $member );

        /**
         * @todo getType() may be deprecated.  
         */
        //..Yep, this should be an adapter or something.
        if ( $prop->getType()->value() == IPropertyType::TARRAY 
          && !$prop->getFlags()->hasAny( IPropertyFlags::NO_INSERT, IPropertyFlags::NO_ARRAY_OUTPUT ))
        {
          $toSave[$member] = json_encode( $model->getValue( $member ));
        }
      }

      //..Insert if there are no valid pri key values 
      $id = $this->dbc->insert( 
        $this->table, 
        $this->mapper()->convertArrayKeys( $toSave, false )
      );

      if ( sizeof( $priKeys ) == 1 && !$doInsert )
      {
        //..Set the primary key value if there's only a single key
        $key = reset( $priKeys );
        $model->setValue( $key->getName(), $id );
      }
    }
    else 
    {      
      $props = $this->getModifiedProperties( $model, true );

      //..Also double checking toArray() results here
      $toSave = [];
      
      foreach( $model->toArray( $props ) as $k => $v )
      {
        //..Double check that toArray() returned friendly properties 
        
        //..This may throw an exception prior to the member check.  
        $p = $model->getPropertySet()->getProperty( $k );
        
        
        if ( $k == $p->getPrefix() || $k . '_' == $p->getPrefix())
        {
          continue;
        }
        
        if ( $model->getPropertySet()->isMember( $k ))
        {
          $toSave[$k] = $v;
        }
      }
      
      
      foreach( $props->getActiveMembers() as $member )
      {
        $prop = $model->getPropertySet()->getProperty( $member );

        //..This should be some type of adapter.
        //..This is stupid.  like for real, stupid.
        /**
         * @todo getType() may be deprecated.  
         */
        if ( $prop->getType()->value() == IPropertyType::TARRAY 
          && !$prop->getFlags()->hasAny( IPropertyFlags::NO_UPDATE, IPropertyFlags::NO_ARRAY_OUTPUT ))
        {            
          $toSave[$member] = json_encode( $model->getValue( $member ));
        }
      }


      if ( !$props->isEmpty() && !empty( $toSave ))
      {
        //..Update if there are.
        $this->dbc->update( 
          $this->table, 
          $this->mapper()->convertArrayKeys( $updKeys, false ),
          $this->mapper()->convertArrayKeys( $toSave, false )
        );
      }
    }
  }
  
  
  /**
   * Saves a batch of records.
   * All records are first validated, then saved sequentially.
   * Validation exceptions will be thrown prior to any saves happening.
   * 
   * If the primary key value is specified, this is considered to be an update.
   * Otherwise, this is considered to be an insert.
   * 
   * This is split into 3 batches (iterations of supplied models):
   * 
   * 1) beforeValidate, validate and beforeSave
   * 2) saveModel
   * 3) afterSave 
   * 
   * 
   * This will wrap the call to saveAll() with a database transaction. 
   * Exceptions will call rollBack().
   * 
   * 
   * If merging multiple repositories, DO NOT USE THIS.  Use the transaction 
   * factory to create a unit of work, which will manage transactions across
   * different storage engines.
   * 
   * @param IModel $model Model to save 
   * @throws DBException For DB errors 
   * @throws ValidationException if the model fails to validate 
   */
  public function saveAll( IModel ...$model ) : void
  {
    $this->test( ...$model );
    
    $trans = new TransactionUnit( $this->dbc );
    
    try {
      parent::saveAll( ...$model );
      
      $trans->commit();
    } catch ( \Exception | TypeError $e ) {
      $trans->rollBack();
      throw $e;
    }
  }
  
  
  /**
   * Remove an entry by id.  This does not work for compound keys.
   * @param string $id id 
   * @return void
   * @throws DBException
   */
  public function removeById( string $id ) : void 
  {
    //..Get the primary key list 
    $priKeys = $this->createPropertySet()->getPrimaryKeys();
    
    if ( empty( $priKeys ))
      throw new DBException( 'Missing primary key definitions attached to IPropertySet for ' . get_class( $model ) . ' in table ' . $this->getTable());
    else if ( sizeof( $priKeys ) > 1 )
      throw new DBException( 'Models that use compound primary keys cannot use this method.  Call remove().' );
    
    $cols[$priKeys[0]->getName()] = $id;
    $this->dbc->delete( $this->table, $cols, 1 );    
  }
  
  
  /**
   * Removes a model.  
   * @param IModel $model Model to remove 
   * @throws DBException For db errors
   * @throws RecordNotFoundException if the primary key is missing or the record
   * could not be found.
   */
  public function remove( IModel $model ) : void
  {
    $this->test( $model );
    //..Get the primary key list 
    $priKeys = $model->getPropertySet()->getPrimaryKeys();
    
    if ( empty( $priKeys ))
      throw new DBException( 'Missing primary key definitions attached to IPropertySet for ' . get_class( $model ) . ' in table ' . $this->getTable());

    //..Primary Key pairs for update query 
    $updKeys = [];
    
    //..If all defined primary key properties have a value within the model
    $hasPriValue = true;
    
    //..Check each pri key 
    foreach( $priKeys as $priKey )
    {
      //..Check for empty and flag
      if ( empty( $model->getValue( $priKey->getName())))
        throw new \InvalidArgumentException( 'Missing prikey value for delete' );
      
      //..Set the primary key pair 
      $updKeys[$priKey->getName()] = $model->getValue( $priKey->getName());
    }
    
    $this->dbc->delete( $this->table, $updKeys, 1 );
  }
  
  
  
  /**
   * Retrieve the estimated record count.  
   * @param bool $full Set to true to retrieve count(*), set to false for max(primary key)
   * @return int estimated number of records 
   */
  public function count( $full = false ) : int
  {
    $keys = $this->properties()->getPrimaryKeys();
    $pri = reset( $keys );
    
    if ( !$this->isSafe( $pri->getName()))
      throw new InvalidArgumentException( 'Invalid primary key name' );

    
    if ( $full )
      $col = 'count(*)';
    else
      $col = 'max( `' . $pri->getName() . '`)';

    foreach( $this->dbc->select( 'select ' . $col . ' as `ct` from ' . $this->table ) as $row )
    {
      if ( is_numeric( $row['ct'] ))
        return (int)$row['ct'];
      else
        return 0;
    }    
    
    return 0;
  }
  
  
  
  /**
   * Retrieve user records by page.
   * @param int $page Page number  
   * @param int $size page size 
   * @return array IModel[] Users 
   */
  public function getPage( int $page, int $size = 25, string $orderBy = '' ) : array
  {
    if ( $page < 1 || $page > 100 )
      throw new \InvalidArgumentException( 'Page must be between 1 and 100' );
    else if ( $size < 1 || $size > 1000 )
      throw new \InvalidArgumentException( 'Page size must be between 1 and 1000' );
    
    $dbc = $this->getDatabaseConnection();
    
    $offset = ( $page - 1 ) * $size;
    
    if ( !empty( $orderBy ))
    {
      if ( !$this->createPropertySet()->isMember( $orderBy ))
        throw new \InvalidArgumentException( 'Invalid order by value' );
      else 
        $orderBy = ' order by ' . $orderBy;
    }
    
    $sql = (new SQLSelect( $this->getSelect(), $this->table ))->getSelect() . sprintf( $orderBy . ' limit %d,%d', $offset, $size );
    
    $out = [];
    foreach( $dbc->select( $sql ) as $row )
    {
      $out[] = $this->create( $row );
    }
    
    return $out;    
  }  
  

  /**
   * Retrieve a search query builder appropriate for the repository
   * @return ISearchQueryBuilder
   */
  public function getSearchQueryBuilder() : ISearchQueryBuilder
  {
    return new SearchQueryBuilder();
  }

  
  private function getStatement( IBigSet $properties, ?IFilter $filter = null, ?IRows $rows = null ) : string
  {
    if ( $properties == null )
      throw new \InvalidArgumentException( "properties must not be null" );
    
    $select = new SQLSelect( $properties, $this->table );    
    
    $sql = $select->getSelect() . ' ' . (( $filter != null ) ? $filter->getFilter() : '' );
    
    if ( $rows != null )
      $sql .= ' ' . $rows->getStatement();
    
    return $sql;
  }
  
  
  /**
   * Detect if a string is only [a-zA-Z0-9_]
   * @param string $s String to check
   * @return boolean is letters
   */
  private function isSafe( $s )
  {
    return preg_match( '/^([a-zA-Z0-9_]+)$/', $s );
  }  
  
  
  /**
   * Execute a query to locate rows by primary key.
   * @param \buffalokiwi\magicgraph\persist\IModel $model
   * @return boolean exists
   */
  private function doesRecordExistByCompoundPrimaryKey( IModel $model, array $priKeys )
  {
    //..Clone the prop set to work with it and clear it 
    $props = clone $model->getPropertySet();
    $props->clear();

    //..The where
    $cg = new SQLConditionGroup();

    //..Values to bind
    $values = [];
    
    

    foreach( $priKeys as $prop )
    {
      /* @var $k IProperty */
      $k = $prop->getName();
      //..Set the property to select
      $props->add( $k );

      //..Get the property value attached to the model
      $val = $model->getValue( $k );
      
      if ( empty( $val ))
        return false;

      //..Create a where condition for prikey = value 
      $cg->addCondition( new SQLCondition( $k, ESQLOperator::EQUAL(), $val ));

      //..Add the value to the bind list
      $values[] = $val;
    }
      
    //..If anything is returned, then it exists.
    foreach( $this->dbc->select(( new SQLSelect( $props, $this->table ))->getSelect() . ' where ' . $cg->getCondition(), $values ) as $row )
    {
      return true;
    }
    
    return false;
  }
}
