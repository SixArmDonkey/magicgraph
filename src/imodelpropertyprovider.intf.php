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
use buffalokiwi\magicgraph\property\IPropertySvcConfig;
use InvalidArgumentException;


/**
 * This is a common interface used to create relationships between different
 * data sources attached to a single model.  (Think foreign keys in a database).
 * 
 * This provider is normally coupled to a repository, and will pull one or more
 * records from that repo based on some property value within the model this 
 * property is attached.
 * 
 * This adds the ability to have a save function attached.  This is used with the various
 * relationship providers.
 */
interface IModelPropertyProvider extends IPropertyServiceProvider
{
  /**
   * Initialize the  model.
   * @param IModel $model Model instance 
   * @return void
   */
  public function init( IModel $model ) : void;
  

  /**
   * Retrieve the value of some property
   * @param string $property Property 
   * @return mixed value
   * @throws InvalidArgumentException if the property is invalid 
   */
  public function getValue( IModel $model, $value,  array $context = []  );
  
  
  /**
   * Sets the value of some property
   * @param string $property Property to set
   * @param mixed $value property value
   * @throws InvalidArgumentException if the property is invalid 
   */
  public function setValue( IModel $model, $value ) : mixed;
  
  
  /**
   * Test to see if this model is valid prior to save()
   * @throws ValidationException
   */
  public function validate( IModel $model ) : void;
  

  /**
   * Retrieve the configuration used to build this provider
   * @return IPropertySvcConfig config 
   */  
  public function getModelServiceConfig() : IPropertySvcConfig;
  
  
  /**
   * Retrieve the save function used for saving stuff from the provider.
   * @param IModel $parent
   * @return IRunnable
   */
  public function getSaveFunction( IModel $parent ) : array;  
  
  
  /**
   * If this relationship provider is backed by a repository, it will be returned here.
   * @return IRepository|null
   */
  public function getRepository() : ?IRepository;
  
  
  /**
   * This will delete all of the linked models managed by this relationship provider linked to the supplied model.
   * @param IModel $model Parent model
   * @return void buh bye
   */
  public function deleteRelatedModels( IModel $model ) : void;  
}
