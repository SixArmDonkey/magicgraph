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


namespace buffalokiwi\magicgraph\property;

use Closure;
use Exception;
use InvalidArgumentException;



/**
 * This is a factory factory based on IPropertyType values.  
 * 
 * Internally, this will maintain a list of factories indexed by property type and when calling createProperty(), 
 * a property of the appropriate type will be returned.
 * 
 * Subclasses may use getFactoryFunction(), which will return a closure (factory) used to return IProperty instances.
 */
class PropertyTypeFactoryFactory 
{
  /**
   * A map of property type => \Closure
   * for creating instances of something 
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
   * @param IPropertyType $propertyTypes A list of property type enum constant values 
   * @param array $factories a map of (string)IPropertyType => fn() : mixed 
   * Any keys of $factories must be values returned by IPropertyType::getEnumValues()
   */
  public function __construct( IPropertyType $propertyTypes, array $factories )
  {
    //..This probably isn't necessary, but I want to ensure that any types not listed in propertyType
    //  do not appear as keys in $factories.
    
    $enumValues = $propertyTypes->getEnumValues();
    
    $types = array_diff( array_keys( $factories ), $enumValues );
    if ( count( $types ) > 0 )
    {
      $invalidTypes = implode( ',', $types );
      throw new InvalidArgumentException(
        '$factories array must only contain keys listed in propertyType::getEnumValues()' );
    }
        
    
    foreach( $enumValues as $type )
    {
      if ( !isset( $factories[$type] ))
      {
        throw new InvalidArgumentException( sprintf( '%s is a valid type, but does not have a corresponding '
          . 'factory function.  Please define one in the $factories argument', $type ));
      }
      else if ( !( $factories[$type] instanceof Closure ))
      {
        throw new InvalidArgumentException( sprintf( '%s factory function is not a valid instance of \Closure.  '
          . 'Please define one in the $factories argument', $type ));
      }
      
      $this->factories[$type] = $factories[$type];
    }
    
    $this->propertyTypes = $propertyTypes;
  }
  
  
  /**
   * Retrieve a map of: [(string)IPropertyType => fn() : IProperty]
   * @return IPropertyType Type 
   */
  public function getTypeInstance() : IPropertyType
  {
    return $this->propertyTypes;
  }
  
  
  /**
   * Retrieve a list of available property types  
   * @return array string[] type list 
   */
  public function getTypes() : array
  {
    return $this->propertyTypes->getEnumValues();
  }
  
  
  /**
   * Retrieve a closure used to create an instance of the supplied property type 
   * @param string $type Type 
   * @return \Closure fn( IPropertyBuilder ) : IProperty 
   * @throws InvalidArgumentException
   */
  protected final function getFactoryFunction( string $type ) : \Closure
  {
    if ( !isset( $this->factories[$type] ))
      throw new InvalidArgumentException( sprintf( '%s is not a valid type for this property factory', $type ));
    
    return $this->factories[$type];
  }  
}
