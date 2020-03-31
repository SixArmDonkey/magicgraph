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

use buffalokiwi\magicgraph\property\IPropertySvcConfig;
use \InvalidArgumentException;


/**
 * This is a common interface used to create relationships between different
 * data sources attached to a single model.  (Think foreign keys in a database).
 * 
 * This provider is normally coupled to a repository, and will pull one or more
 * records from that repo based on some property value within the model this 
 * property is attached.
 *  
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
  public function setValue( IModel $model, $value ) : void;
  
  
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
}
