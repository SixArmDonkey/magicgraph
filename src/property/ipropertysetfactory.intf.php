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

namespace buffalokiwi\magicgraph\property;

use Closure;
use Exception;


/**
 * A factory for creating IPropertySet instances
 */
interface IPropertySetFactory
{
 /**
   * Retrieve the IMappedPropertyFactory instance 
   * @return IMappedPropertyFactory factor 
   */
  public function getPropertyFactory() : IMappedPropertyFactory;
  
  
  /**
   * Retrieve the callback used for creating new IPropertySet instances 
   * @return Closure f( IMappedPropertyFactory $factory, IPropertyConfig ...$config ) : IPropertySet 
   */
  public function getCreatePropertySetSupplier() : Closure;
  
  /**
   * Using the suppliers defined in this object, create an IPropertySet instance.
   * @param IPropertyConfig $config Additional configuration data
   * @return IPropertySet property set 
   * @throws Exception if the supplier does not return an IPropertySet instance 
   */
  public function createPropertySet( IPropertyConfig ...$config ) : IPropertySet;
}
