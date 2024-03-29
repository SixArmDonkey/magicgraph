<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */

namespace buffalokiwi\magicgraph;

use buffalokiwi\magicgraph\property\IPropertySet;
use Closure;
use Exception;
use InvalidArgumentException;


/**
 * Used for setting model properties.
 * 
 * Maps property names and values to a model.
 * 
 * This should only be used with a single object type.  ie: one mapper per repository.
 * 
 * Note: It is assumed that this will only be used to create and/or hydrate objects.
 * Therefore, this calls IModel::hydrate() instead of IModel::setValue().  
 * Edit flags are never set when using this model mapper implementation.
 */
class DefaultModelMapper implements IModelMapper
{
  /**
   * Create model supplier 
   * @var Closure 
   */
  private $createModel;

  /**
   * Class/interface name of models 
   * @var string
   */
  private $clazz;
  
  /**
   * IModelMap instances
   * @var ?IModelMap
   */
  private ?IModelMap $maps = null;
  
  
  /**
   * Create a new DefaultModelMapper instance 
   * @param Closure $createModel A supplier that returns an IModel instance.
   * f( ?IPropertySet $propertySet, array $data ) : IModel
   * @param string $clazz A fully qualified class or interface name.  All operations
   * inside an IRepository will be locked to this type.
   * @throws InvalidArgumentException if createModel does not return an IModel instance 
   */
  public function __construct( Closure $createModel, string $clazz, ?IModelMap $maps = null )
  {
    $this->createModel = $createModel;
    
    if ( empty( $clazz ))
      throw new InvalidArgumentException( 'clazz must not be empty.  Please specify a class or interface name, which is used to limit the type of objects used within this mapper and associated repositories.' );
    
    $this->clazz = $clazz;
    
    
    if ( $maps != null )
      $this->maps = $maps;
  }
  
  
  /**
   * Retrieve the class or interface name this mapper works with.
   * @return string name 
   */
  public function getClass() : string
  {
    return $this->clazz;
  }
  
  
  /**
   * Test that the supplied model implements the interface or class name returned
   * by getClass().
   * @param \buffalokiwi\magicgraph\IModel $models One or more models to test
   * @return void
   * @throws Exception if the model does not implement the interface.
   * @final 
   */
  public final function test( IModel ...$models ) : void  
  {
    foreach( $models as $k => $model )
    {
      /* @var $model IModel */
      //..is_subclass_of is for interfaces, and is_a is for classes.
      if ( !is_a( $model, $this->clazz, false ) && !is_subclass_of( $model, $this->clazz, false ) && !$model->instanceOf( $this->clazz ))
      {
        throw new Exception( "Supplied model (" . $k . ") must be an instance of " . $this->clazz . '.  Got ' . (( is_object( $model )) ? get_class( $model ) : gettype( $model )) );
      }    
    }
  }
  
  
  /**
   * Maps a map of raw data (attribute => value) to an IModel instance.
   * @param array $data data to map
   * 
   * Optionally accepts an IPropertySet instance.  Supplied if passed to the IModelMapper
   * constructor.
   * f( ?IPropertySet $propertySet, array $data ) : IModel
   * 
   * @param \buffalokiwi\magicgraph\IPropertySet|null $propertySet An optional property set.
   * If this is passed, then the supplied IPropertySet is passed to the $createModel
   * Closure as the first argument.
   * 
   * @return IModel model instance 
   * @throws Exception if the create model callback does not return an instance of IModel 
   */
  public function createAndMap( array $data, ?IPropertySet $propertySet = null ) : IModel
  {
    $model = $this->createModel( $propertySet, $data );
    $this->map( $model, $data );
    if ( !( $model instanceof IModel ))
      throw new InvalidArgumentException( 'createModel must return an instance of ' . IModel::class . '.  Got ' . (( is_object( $model )) ? get_class( $model ) : gettype( $model )) );
    
    $model->clearEditFlags();
    return $model;    
  }
  
  
  
  /**
   * Map some data from the database to properties in some model.
   * Invalid properties are silently ignored.
   * @param IModel $model Model to push data into
   * @param array $data data to push
   */
  public function map( IModel $model, array $data ) : void
  {
    $this->test( $model );
    
    
    foreach( $this->mapData( $data, true ) as $k => $v )
    {
      //..Simply ignore any non-member properties.
      //..The extra properties may be used with service providers
      try {
        $model->hydrate( $k, $v );
        //$model->setValue( $k, $v );
      } catch( \InvalidArgumentException $e ) {
        //..Do nothing.
      }
    }
    
    //$model->clearEditFlags();
  }
  
  
  /**
   * Using the supplied IModelMap instances, this will convert
   * a model to an array, then convert the array property names based on the supplied mapping.
   * If no mappings are supplied, this simply returns IModel::toArray()
   * @param IModel $model
   * @return array
   */
  public function mapToArray( IModel $model ) : array
  {
    return $this->mapData( $model->toArray(), false );
  }
  
  
  /**
   * Convert array keys from model to persistence or from persistence to model.
   * @param array $data
   * @param bool $isFromDB Set to true if keys in $data are from the persistence layer.  If keys are from the model, 
   * set to false.
   * @return array $data with converted keys 
   */
  public function convertArrayKeys( array $data, bool $isFromDB ) : array
  {
    return $this->mapData( $data, $isFromDB );
  }
  
  
  /**
   * Call the create model callback and return the results
   * 
   * Optionally accepts an IPropertySet instance.  Supplied if passed to the IModelMapper
   * constructor.
   * $data is the raw data to be mapped to the model.
   * f( ?IPropertySet $propertySet = null, array $data = [] ) : IModel
   * 
   * @param IPropertySet|null $propertySet An optional property set.
   * If this is passed, then the supplied IPropertySet is passed to the $createModel
   * Closure as the first argument.
   * @return IModel new model instance 
   * @throws Exception if the create model callback does not return an instance of IModel 
   */
  private function createModel( ?IPropertySet $propertySet = null, array $data = [] ) : IModel 
  {
    $cb = $this->createModel;    
    $model = $cb( $propertySet, $data );
    
    //..is_subclass_of is for interfaces, and is_a is for classes.
    if (( !( $model instanceof IModel )) || ( !is_a( $model, $this->clazz, false ) && !is_a( $model, $this->clazz, false )))
    {
      throw new Exception( "createModel supplier must return an instance of " . $this->clazz . '.  Got ' . (( is_object( $model )) ? get_class( $model ) : gettype( $model )));
    }
      
    //..deprecated 
    //$model->clearEditFlags();
    
    return $model;
  }
  
  
  /**
   * @param array $data
   * @param bool $dataFromDB
   * @return array
   */
  private function mapData( array $data, bool $dataFromDB ) : array
  {
    if ( $this->maps == null )
      return $data;
    
    if ( $dataFromDB )
      $map = array_flip( $this->maps->getMap());
    else
      $map = $this->maps->getMap();
    
    $out = [];
    
    foreach( $data as $k => $v )
    {
      if ( isset( $map[$k] ))
        $out[$map[$k]] = $v;
    }
    
    return $out;
    
  }
}
