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

namespace buffalokiwi\magicgraph;

use buffalokiwi\magicgraph\persist\IRunnable;
use buffalokiwi\magicgraph\persist\RecordNotFoundException;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertySvcConfig;
use InvalidArgumentException;


/**
 * Contains some of the base programming for a property service backed by a single 
 * repository and model.
 */
abstract class AbstractOneOnePropertyService implements IModelPropertyProvider
{  
  /**
   * Property config for the service 
   * @var IPropertySvcConfig 
   */
  private $propCfg;
  
  /**
   * Last id from getValue()
   * @var int
   */
  private $lastId = 0;
  
  private ?IModel $lastModel = null;
  
  private bool $reloadSameModels;
  
  
  protected abstract function onSave( IModel $model ) : array;
  
  /**
   * Loads an item from somewhere by id.
   * If $id == 0, then this must return an empty model.
   * @param int $id Id 
   * @return IModel Model 
   */
  protected abstract function loadById( int $id ) : IModel;
  
  /**
   * Create a new property service 
   * @param IPropertyConfig $cfg
   * @param string $name
   * @param string $modelPropertyName Optional model property name. 
   */
  public function __construct( IPropertySvcConfig $cfg, bool $reloadSameModels = false )
  {
    $this->propCfg = $cfg;
    $this->reloadSameModels = $reloadSameModels;
  }
  
  
  /**
   * Get the property config for the main property set 
   * @return IPropertySvcConfig config 
   */
  public function getModelServiceConfig() : IPropertySvcConfig
  {
    return $this->propCfg;
  }
  
  
  /**
   * Retrieve the save function used for saving stuff from the provider.
   * @param IModel $parent
   * @return IRunnable[]
   */
  public function getSaveFunction( IModel $parent ) : array
  {
    $id = $parent->getValue( $this->propCfg->getPropertyName());
    
    if ( empty( $id ))
      return [];
    

    
    $model = $parent->getValue( $this->propCfg->getModelPropertyName());
    
    
    
    $res = $this->propCfg->getSaveFunction( $parent );
    
    
    
    /* @var $model IModel */
    if (( $model instanceof IModel ) && $model->hasEdits())
    {
      foreach( $this->onSave( $model ) as $f )
      {
        $res[] = $f;
      }
    }
    
    if ( empty( $res ))
      return [];
    else
      return $res;
  }
  
  
  public function deleteRelatedModels( IModel $model ) : void
  {
    $repo = $this->getRepository();
    if ( empty( $repo ))
      return;
    
    $id = $model->getValue( $this->propCfg->getPropertyName());
    
    if ( empty( $id ))
      return;
    
    $repo->remove( $this->loadById( $id ));    
  }
    
  
  
  /**
   * Retrieve the value of some property
   * @param string $property Property 
   * @return mixed value
   * @throws InvalidArgumentException if the property is invalid 
   */
  public function getValue( IModel $model, $value, array $context = [] )
  {
    try {
      $id = $model->getValue( $this->propCfg->getPropertyName());
      
      if ( $id != $this->lastId || !( $value instanceof IModel ))
      {
        $this->lastId = $id;
        
        if ( !empty( $id ))
          $newModel = $this->loadById( $id );
        else
          $newModel = $this->loadById( 0 );
        
        $this->lastModel = $newModel;
      }
      else if ( $id > 0 && $id == $this->lastId && ( !( $value instanceof IModel ) || !$value->hasPrimaryKeyValues()))
      {
        
        if ( $this->reloadSameModels )
          $newModel = $this->loadById( $id );
        else
          $newModel = $this->lastModel;
      }
      else 
        return $value;
      
      
    } catch( RecordNotFoundException | InvalidArgumentException $e ) {
      //..Maybe just throw an exception since we know this is invalid 
      //  and would result in bad links.
      throw $e;
    }
    
    $model->setValue( $this->propCfg->getModelPropertyName(), $newModel );
    return $newModel;
  }
  
  
  /**
   * Sets the value of some property
   * @param string $property Property to set
   * @param mixed $value property value
   * @throws InvalidArgumentException if the property is invalid 
   */
  public function setValue( IModel $model, $value ) : mixed
  {
    $value = $this->getValue( $model, $value );
    /* @var $value IModel */
    if ( !( $value instanceof IModel ))// || $value->getValue( $value->getPropertySet()->getPrimaryKey()->getName()) != $this->lastId )
    {
      throw new \Exception( 'This model may not be set directly' );
    }
    return $value;
  }
  
  
  /**
   * Test to see if this model is valid prior to save()
   * @throws ValidationException
   */
  public function validate( IModel $model ) : void
  {    
    if ( empty( $this->lastId ) || ( !$model->hasPrimaryKeyValues() && !$model->hasEdits()))
      return;
    
   
    $m = $model->getValue( $this->propCfg->getModelPropertyName());
    if ( $m instanceof IModel )
    {
      $m->validate();
    }
    else
      throw new \Exception( 'Backing model for ' . $this->propCfg->getModelPropertyName() . ' has not been initialized' ); 
  }  
  
  
  
  /**
   * Initialize the  model.
   * @param IModel $model Model instance 
   * @return void
   */
  public function init( IModel $model ) : void
  {
    //..Nothing needs to happen here.
    //..Lazy loading is provided via getValue().
  }
  
  
  /**
   * Get the property config for the main property set 
   * @return IPropertyConfig config 
   */
  public function getPropertyConfig() : IPropertyConfig
  {
    return $this->propCfg;
  }
}
