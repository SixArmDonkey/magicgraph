<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
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
   * Create a new DefaultModelMapper instance 
   * @param Closure $createModel A supplier that returns an IModel instance.
   * f( ?IPropertySet $propertySet, array $data ) : IModel
   * @param string $clazz A fully qualified class or interface name.  All operations
   * inside an IRepository will be locked to this type.
   * @throws InvalidArgumentException if createModel does not return an IModel instance 
   */
  public function __construct( Closure $createModel, string $clazz )
  {
    $this->createModel = $createModel;
    
    if ( empty( $clazz ))
      throw new InvalidArgumentException( 'clazz must not be empty.  Please specify a class or interface name, which is used to limit the type of objects used within this mapper and associated repositories.' );
    
    $this->clazz = $clazz;
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
   * Map some data to properties in some model.
   * Invalid properties are silently ignored.
   * @param IModel $model Model to push data into
   * @param array $data data to push
   */
  public function map( IModel $model, array $data ) : void
  {
//    $names = $model->getPropertyNameSet();
    $this->test( $model );
    foreach( $data as $k => $v )
    {
      //..Simply ignore any non-member properties.
      //..The extra properties may be used with service providers
      try {
        //..Don't ignore anything...
//        if ( $names->isMember( $k ))
          $model->setValue( $k, $v );
      } catch( \InvalidArgumentException $e ) {
        //..Do nothing.
      }
    }
    $model->clearEditFlags();
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
    
    $model->clearEditFlags();
    
    return $model;
  }
}
