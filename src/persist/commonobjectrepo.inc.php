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
use buffalokiwi\magicgraph\property\IProperty;
use Exception;


/**
 * A common object repo is a repository decorator that caches objects 
 * retrieved from the underlying repo when retrieved via the get() or getAll() getForProperty() and create() methods.
 * 
 * This isn't the most efficient thing out there, but it does function.
 */
class CommonObjectRepo extends RepositoryProxy
{
  private $cache = [];
  private $nameCache = [];
  private $repo;
  
  public function __construct( IRepository $repo )
  {
    parent::__construct( $repo );
    $this->repo = $repo;
  }
  
  
  /**
   * Create a new Model instance using the internal data mapper.
   * @param array $data Raw data to use 
   * @param bool $readOnly Set the produced model to read only 
   * @return IModel model instance 
   * @throws DBException For db errors 
   */
  public function create( array $data = [], bool $readOnly = false ) : IModel
  {
    $model = $this->repo->create( $data, $readOnly );
    
    $cid = $this->getCid( $model );
    if ( empty( $cid ))
      return $model;
    
    if ( !isset( $this->cache[$cid] ))
    {
      $this->cache[$cid] = $model;
    }
    
    return $this->cache[$cid];    
  }
  
  
  /**
   * Save some record.
   * If the primary key value is specified, this is considered to be an update.
   * Otherwise, this is considered to be an insert.
   * 
   * @param IModel $model Model to save 
   * @throws DBException For DB errors 
   * @throws ValidationException if the model fails to validate 
   */
  public function save( IModel $model ) : void
  {
    $cid = $this->getCid( $model );
    
    if ( !empty( $cid && isset( $this->cache[$cid] )))
      unset( $this->cache[$cid] );
    
    $this->repo->save( $model );
  }
  
  
  /**
   * Saves a batch of records.
   * All records are first validated, then saved sequentially.
   * Validation exceptions will be thrown prior to any saves happening.
   * 
   * If the primary key value is specified, this is considered to be an update.
   * Otherwise, this is considered to be an insert.
   * 
   * @param IModel $model Model to save 
   * @throws DBException For DB errors 
   * @throws ValidationException if the model fails to validate 
   */
  public function saveAll( IModel ...$model ) : void
  {
    foreach( $model as $m )
    {
      $cid = $this->getCid( $m );

      if ( !empty( $cid && isset( $this->cache[$cid] )))
        unset( $this->cache[$cid] );      
    }
    
    $this->repo->saveAll( ...$model );
  }  
  
  
  private function getCid( IMOdel $model ) : string
  {
    $a = [];
    foreach( $model->getPropertySet()->getPrimaryKeyNames() as $name )
    {
      $a[] = $model->getValue( $name );
    }
    
    return implode( '', $a );   
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
    $nid = $propertyName . ',' . $value;
    
    if ( isset( $this->nameCache[$nid] ))
    {
      return $this->nameCache[$nid];
    }
    
    $out = [];
    foreach( $this->repo->getForProperty( $propertyName, $value ) as $model )
    {
      /* @var $model IModel */
      $cid = $model->getValue( ...$model->getPropertySet()->getPrimaryKeyNames());
      if ( !isset( $this->cache[$cid] ))
        $this->cache[$cid] = $model;
      
      $out[] = $this->cache[$cid];
      $this->nameCache[$nid][] =& $this->cache[$cid];
    }
    
    return $out;
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
    $cid = implode( '', $id );
    if ( !isset( $this->cache[$cid] ))
    {
      $this->cache[$cid] = $this->repo->get( ...$id );
    }
    
    return $this->cache[$cid];
  }
  
  
  /**
   * Retrieve a list of models by a list of primary key values.
   * @param array $idList id list 
   * @return IModel[] found models 
   * @throws DBException For DB Errors 
   */
  public function getAll( array $idList ) : array
  {
    $cached = [];
    $fetchIds = [];
    
    foreach( $idList as $id )
    {
      if ( isset( $this->cache[$id] ))
        $cached[] = $this->cache[$id];
      else
        $fetchIds[] = $id;
    }
    
    if ( !empty( $fetchIds ))
    {
      foreach( $this->repo->getAll( $idList ) as $model )
      {
        /* @var $model IModel */
        
        $cid = $this->getCid( $model );
        
        $this->cache[$cid] = $model;
        $cached[] = $model;
      }
    }
    
    return $cached;
  }

}
