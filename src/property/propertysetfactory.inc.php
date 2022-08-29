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
 * Can be used to create IPropertySet instances for a specific type of IModel
 */
class PropertySetFactory implements IPropertySetFactory
{
  /**
   * Property factory instance 
   * @var IMappedPropertyFactory 
   */
  private $factory;
  
  /**
   * A supplier for creating IPropertySet instances.
   * f( IMappedPropertyFactory $factory, IPropertyConfig ...$config ) : IPropertySet 
   * @var Closure 
   */
  private $createPropertySet;
  
  
  /**
   * 
   * @param \buffalokiwi\retailrack\product\IMappedPropertyFactory $factory Property Factory
   * @param Closure $createPropertySet A callback for creating IPropertySet instances.
   * f( IMappedPropertyFactory $factory, IPropertyConfig ...$config ) : IPropertySet 
   * @param \buffalokiwi\retailrack\product\IPropertyConfig $config
   */
  public function __construct( IMappedPropertyFactory $factory, Closure $createPropertySet )
  {
    $this->factory = $factory;
    $this->createPropertySet = $createPropertySet;
  }
  
  
  /**
   * Retrieve the IMappedPropertyFactory instance 
   * @return IMappedPropertyFactory factor 
   */
  public function getPropertyFactory() : IMappedPropertyFactory
  {
    return $this->factory;
  }
  
  
  /**
   * Retrieve the callback used for creating new IPropertySet instances 
   * @return Closure f( IMappedPropertyFactory $factory, IPropertyConfig ...$config ) : IPropertySet 
   */
  public function getCreatePropertySetSupplier() : Closure
  {
    return $this->createPropertySet;
  }
  
  
  /**
   * Using the suppliers defined in this object, create an IPropertySet instance.
   * @param IPropertyConfig $config Additional configuration data
   * @return IPropertySet property set 
   * @throws \Exception if the supplier does not return an IPropertySet instance 
   */
  public function createPropertySet( IPropertyConfig ...$config ) : IPropertySet
  {
    $c = $this->createPropertySet;
    $set = $c( $this->factory, ...$config );
    if ( !( $set instanceof IPropertySet ))
    {
      throw new Exception( sprintf( '%s supplier passed to %s did not return an instance of %s.  Got %s',
        IPropertySet::class,
        PropertySetFactory::class,
        IPropertySet::class,
        ( is_object( $set )) ? get_class( $set ) : gettype( $set )));      
    }
    
    return $set;
  }
}
