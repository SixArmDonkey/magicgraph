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
use InvalidArgumentException;



/**
 * 
 */
abstract class PropertyTypeIoC implements IPropertyTypeIoC
{
  /**
   * A map of property type => \Closure
   * for creating instances of IProperty
   * @var array
   */
  protected $factories = [];
  
  /**
   * Property type enum for getting the master list of property types.
   * @var IPropertyType
   */
  private $propertyTypes;
  
  
  /**
   * Create a new PropertyFactory
   * @param IPropertyType $propertyTypes
   * @param array $factories
   */
  public function __construct( IPropertyType $propertyTypes, array $factories )
  {
    foreach( $propertyTypes->getEnumValues() as $type )
    {
      if ( !isset( $factories[$type] ))
        throw new InvalidArgumentException( sprintf( '%s is a valid type, but does not have a corresponding factory function.  Please define one in the $factories argument', $type ));
      else if ( !( $factories[$type] instanceof Closure ))
        throw new InvalidArgumentException( sprintf( '%s factory function is not a valid instance of \Closure.  Please define one in the $factories argument', $type ));
      
      $this->factories[$type] = $factories[$type];
    }
    
    $this->propertyTypes = $propertyTypes;
  }
  
  
  public function getTypeInstance() : IPropertyType
  {
    return clone $this->propertyTypes;
  }
  
  
  public function getTypes() : array
  {
    return $this->propertyTypes->getEnumValues();
  }
  
  
  protected final function getFactoryFunction( string $type ) : \Closure
  {
    if ( !isset( $this->factories[$type] ))
      throw new InvalidArgumentException( sprintf( '%s is not a valid type for this factory', $type ));
    
    return $this->factories[$type];
  }
  
  
  public function createProperty( IPropertyBuilder $builder ) : IProperty
  {
    /**
     * @todo getType() may be deprecated.  
     */
    $type = $builder->getType()->value();
    if ( !isset( $this->factories[$type] ))
      throw new InvalidArgumentException( sprintf( '%s is not a valid type for this property factory', $type ));
    
    $f = $this->factories[$type];
    
    $prop = $f( $builder );
    if ( !( $prop instanceof IProperty ))
      throw new Exception( sprintf( 'Property factory function for property type %s does not return an instance of %s.', $type, IProperty::class ));
    
    return $prop;
  }
}
