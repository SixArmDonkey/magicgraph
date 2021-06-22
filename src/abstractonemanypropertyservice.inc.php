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

namespace buffalokiwi\magicgraph;

use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\persist\IRunnable;
use buffalokiwi\magicgraph\persist\RecordNotFoundException;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertySvcConfig;
use InvalidArgumentException;


/**
 * Contains some of the base programming for a property service backed by a single 
 * repository and an array property full of IModel instances.
 * @todo Write tests for how getValue uses spl_object_id and the garbage collector.
 */
abstract class AbstractOneManyPropertyService implements IModelPropertyProvider
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
  private $lastId = [];
  
  private $init = [];
  
  
  /**
   * Loads the models from some source 
   */
  protected abstract function loadModels( int $parentId, IModel $parent ) : array;
  
  protected abstract function create( array $data ) : IModel;
  
  /**
   * Create a new property service 
   * @param IPropertyConfig $cfg
   * @param string $name
   * @param IRepository $repo
   * @param string $foreignKey Property name from supplied IRepository that is 
   * queried against IPropertySvcConfig::getPropertyName();
   * @param string $modelPropertyName Optional model property name. 
   */
  public function __construct( IPropertySvcConfig $cfg )    
  {
    $this->propCfg = $cfg;
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
   * @return IRunnable
   */
  public function getSaveFunction( IModel $parent ) : array
  {
    return $this->propCfg->getSaveFunction( $parent );
  }
  
  
  public function deleteRelatedModels( IModel $parent ) : void
  {
    $repo = $this->getRepository();
    if ( empty( $repo ))
      return;
    
    $priKey = $repo->createPropertySet()->getPrimaryKey()->getName();
    
    $repo->select( $priKey );
    $idList = [];
    foreach( $this->callLoadModels( $parent->getValue( $this->propCfg->getPropertyName()), $parent ) as $model )
    {
      $idList[] = $model->getValue( $priKey );
    }
    
    foreach( $idList as $id )
    {
      $repo->removeById((string)$id );
    }    
  }
  
  
  
  /**
   * Retrieve the value of some property
   * @param string $property Property 
   * @return mixed value
   * @throws InvalidArgumentException if the property is invalid 
   */
  public function getValue( IModel $model, $value, array $context = [] )
  {    
    //..This service is shared between different model instances that may or may
    //  not use the attached repo.
    //  
    //  Scenario:
    //  
    //  Object A has a one to many relationship with Object B 
    //  Object B has a one to many relationship with Object C
    //  Object C has a one to many relationship with Object A 
    //  
    //  Object A is loaded directly from the repo A 
    //  Object A is modified
    //  Object C is loaded directly from the repo C, and Object A is loaded 
    //  
    //  If spl_object_id is not used, then Object C will see zero entries for Object B 
    //  due to lastId and init having the wrong states.  Object A had already initialied the list.
    //  
    //..We really need a unique id per model here.
    //..spl_object_id may cause shenanigans if the garbage collector destroys an object with an id that is in this list.
    //..We shall see as we go...  
    
    /**
     * @todo Write tests for this.
     */
    $c = spl_object_id( $model );
    
    
    //..This allows this to accept arrays (IModel::toArray() output) instead of IModel instances.
    //..Caveat: If the array is empty, then the db gets called.  I have no idea how to get around this one right now.
    $newData = [];
    if ( is_array( $value ))
    {
      foreach( $value as $entry )
      {
        
        /* @var $entry IModel */
        //..Only add previously loaded models that do not have primary key values 
        if ( is_array( $entry ))
        {
          $entries = [];

          $keys = array_keys( $entry );
          if ( reset( $keys ) == 0 )
          {
            //..This is probably many entries
            foreach( $entry as $e )
            {
              $newData[] = $this->create( $e );
            }
          }
          else
          {
            //..This is probably one entry 
            $newData[] = $this->create( $entry );
          }
        }
      }
      

      if ( !empty( $newData ))
      {
        $this->init[$c] = false;
        $id = $model->getValue( $this->propCfg->getPropertyName());
        $this->lastId[$c] = $id;
        $model->setValue( $this->propCfg->getModelPropertyName(), $newData );        
        return $newData;
      }
    }
    
    
    if ( empty( $newData ))
    {

      if ( !isset( $this->init[$c] ))
        $this->init[$c] = true;

      if ( !isset( $this->lastId[$c] ))
        $this->lastId[$c] = -1;

      //..Need to grab the attachd models 
      try {
        $id = $model->getValue( $this->propCfg->getPropertyName());

        if (( !empty( $id ) && ( $id != $this->lastId[$c] || !is_array( $value ))) || ( empty( $value ) && $this->init[$c] ))
        {
          $this->init[$c] = false;
          $this->lastId[$c] = $id;

          //..No need to query for empty.
          $newData = $this->callLoadModels( $id, $model );
        }
        else
        {
          return $value;
        }

      } catch( RecordNotFoundException | InvalidArgumentException $e ) {
        //..Initialize with an empty model.  I hate null.
        //..Maybe just throw an exception since we know this is invalid 
        //  and would result in bad links.
        throw $e;
        //$newModel = $this->repo->create( [] ); 
      }

      //..Commit the loaded data 
      if ( is_array( $value ))
      {
        foreach( $value as $entry )
        {
          if ( !$this->hasAllPriKeyValues( $entry ))
          {          
            $newData[] = $entry;
          }
        }
      }
    }

    $model->setValue( $this->propCfg->getModelPropertyName(), $newData );
    
    
    //..Test if some other program wants control of what records to return.
    //..What?
    if ( is_string( $value ) && !empty( $value ))
    {      
      return $value;
    }    
    
    
    return $newData;
  }
  
  
  /**
   * Sets the value of some property
   * @param string $property Property to set
   * @param mixed $value property value
   * @throws InvalidArgumentException if the property is invalid 
   */
  public function setValue( IModel $model, $value ) : mixed
  {
    return $this->getValue( $model, $value );
    /* @var $value IModel */
    /*
    if ( !is_array( $value ) && ( is_string( $value ) && empty( $value )))// || $value->getValue( $value->getPropertySet()->getPrimaryKey()->getName()) != $this->lastId )
    {
      throw new \Exception( 'This value must be an array.' );
    }
    */
  }
  
  
  /**
   * Test to see if this model is valid prior to save()
   * @throws ValidationException
   */
  public function validate( IModel $model ) : void
  {
    $a = $model->getValue( $this->propCfg->getModelPropertyName());
    if ( is_array( $a ))
    {
      //..Do nothing here.  
      
      
      //..This is unnecessary, and it causes problems with cascading saves.
      /*
      foreach( $a as $m )
      {
        if ( $m instanceof IModel )
        {
          $m->validate();
        }
        else
          throw new ValidationException( 'Array property must only contain instances of IModel' );
      }
       * 
       */
    }
    else
      throw new ValidationException( 'Backing model for ' . $this->propCfg->getModelPropertyName() . ' has not been initialized' ); 
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
  
  
  private function callLoadModels( int $parentId, IModel $parent ) : array
  {
    $data = $this->loadModels( $parentId, $parent );
    foreach( $data as $m )
    {
      if ( !( $m instanceof IModel ))
        throw new \Exception( 'OneManyPropertyService did not return an IModel instance when loading data' );
    }
    return $data;
  }
  
  
  /**
   * Test to see if some model has values for all primary keys 
   * @param IModel $model Model 
   * @return bool has all keys 
   */
  private function hasAllPriKeyValues( IModel $model ) : bool
  {
    foreach( $model->getPropertySet()->getPrimaryKeyNames() as $name )
    {
      if ( empty( $model->getValue( $name )))
        return false;
    }
    
    return true;
    
  }
}
