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
use buffalokiwi\buffalotools\types\RuntimeBigSet;
use buffalokiwi\magicgraph\DBException;
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\IModelMapper;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\ReadOnlyModelWrapper;


class MappingObjectFactory implements IObjectFactory
{
  /**
   * Data mapper 
   * @var IModelMapper
   */
  private $mapper;
  
  /**
   * Properties
   * @var IPropertySet
   */
  private $properties;
  
  /**
   * Property names 
   * @var string[] 
   */
  private $pNames;
  
  /**
   * Selected names 
   * @var IBigSet
   */
  private IBigSet $select;
  
  
  public function __construct( IModelMapper $mapper, ?IPropertySet $properties = null )
  {
    $this->mapper = $mapper;
    
    if ( $properties != null )
      $this->properties = $properties;
    else
      $this->properties = $mapper->createAndMap([])->getPropertySet();
    
    $this->pNames = $this->properties->getMembers();
    $this->select = new RuntimeBigSet( $this->pNames );
  }
  
  
  /**
   * Specify columns to select.
   * @param string $names Zero or more names.  Leave names empty to select all columns.
   * @return IObjectFactory this 
   * @final 
   */
  public final function select( string ...$names ) : IObjectFactory
  {
    $this->select->clear();
    
    if ( !empty( $names ))
      $this->select->add( ...$names );
    
    return $this;
  }
  
  
  /**
   * Retrieve the columns to select.
   * If the set is empty, then select all columns
   * @return IBigSet selection set 
   */
  protected final function getSelect() : IBigSet
  {
    return $this->select;
  }
  
  
  /**
   * Test if models created by this repo are of some type.  
   * @param string $clazz interface or class name 
   * @return bool
   */
  public function isA( string $clazz ) : bool
  {
    $model = $this->create();
    return !( !is_a( $model, $clazz, false ) && !is_subclass_of( $model, $clazz, false ) && !$model->instanceOf( $clazz ));      
  }
  
  
  /**
   * Adds an additional property config to this repo.
   * When models reference themselves, sometimes it's necessary for a property 
   * config to reference the repository (circular).  
   * 
   * Feels a bit like cheating to me...
   * 
   * Warning: This method should only be caled from composition root.  This can 
   * obviously have some unintended side effects when used in other locations.
   * 
   * @param type $config
   */
  public function addPropertyConfig( IPropertyConfig ...$config )
  {
    $this->properties->addPropertyConfig( ...$config );
  }
  

  /**
   * Create a new Model instance using the internal data mapper.
   * @param array $data Raw data to use 
   * @param bool $readOnly Set the produced model to read only 
   * @return IModel model instance 
   * @throws DBException For db errors 
   * @todo Create some code that can disable or remove properties that are not 
   * fetched when the model is built.  Think about this a bit...
   */
  public function create( array $data = [], bool $readOnly = false ) : IModel
  {
    $props = clone $this->properties;
    $model = $this->mapper->createAndMap( $data, $props );
    if ( $readOnly )
      $props->setReadOnly();
    return $model;
  }

  
  
  public function createPropertySet() : IPropertySet
  {
    return clone $this->properties;
  }
  
  
  public function createPropertyNameSet() : IBigSet
  {
    return new RuntimeBigSet( $this->pNames );
  }
  
  /**
   * Test that one or more models is the correct type.
   * @param IModel ...$models Models 
   * @return void
   */
  protected function test( IModel ...$models ) : void
  {
    /**
     * @todo This is a temporary hack.  It's horrible.
     */
    if ( $models instanceof ReadOnlyModelWrapper ) 
    {
      throw new \Exception( 'Read only models may not be saved' );
    }
    
    
    $this->mapper->test( ...$models );
  }
  
  
  protected final function mapper() : IModelMapper
  {
    return $this->mapper;
  }
  
  
  protected final function properties() : IPropertySet
  {
    return $this->properties;
  }
  
  
  
  protected function getInsertProperties( IModel $model ) : IBigSet
  {
    return $this->filterPropertyNamesForSave( $model->getInsertProperties()->getActiveMembers(), $model );
  }
  
  
  protected function getModifiedProperties( IModel $model ) : IBigSet 
  {
    return $this->filterPropertyNamesForSave( $model->getModifiedProperties()->getActiveMembers(), $model );
  }  
  
  
  /**
   * Removes any properties flagged with SUBCONFIG from the list of property names
   * @param string[] $names Property names 
   * @return array filtered names 
   * @throws Exception if model is an incorrect type.
   */
  private function filterPropertyNamesForSave( array $names, IModel $model ) : IBigSet
  {
    $this->mapper->test( $model );
    $out = [];
    $set = $model->getPropertySet();
    
    foreach( $names as $name )
    {
      $prop = $set->getProperty( $name );
      /* @var $prop IProperty */
      if ( $prop->getFlags()->hasVal( IPropertyFlags::SUBCONFIG ))
        continue;
      
      $out[] = $name;
    }
    
    $b = new RuntimeBigSet( $out );
    $b->setAll();
    return $b;
  }  
}
