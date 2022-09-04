<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2022 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */

declare( strict_types=1 );

namespace buffalokiwi\magicgraph\property;

use Exception;
use InvalidArgumentException;


/**
 * Creates instances of IProperty based on a supplied list of factory functions indexed by 
 * a property type constant value.
 * 
 * ie:
 * 
 * new PropertyTypeFactory( new EPropertyType(), [
 *   EPropertyType::STRING => fn( IPropertyBuilder $b ) => new StringProperty( $b ))
 *   ... for each property type listed in EPropertyType
 * ]);
 */
class PropertyFactory extends PropertyTypeFactoryFactory implements IPropertyFactory
{  
  /**
   * Create a new PropertyFactory
   * @param IPropertyType $propertyTypes A list of property type enum constant values 
   * @param array $factories a map of (string)IPropertyType => fn() : mixed 
   * Any keys of $factories must be values returned by IPropertyType::getEnumValues()
   */
  public function __construct( IPropertyType $propertyTypes, array $factories )
  {
    parent::__construct( $propertyTypes, $factories );
  }
  
  
  /**
   * Create an IProperty instance using the supplied property builder.  The type of property is determined
   * by the type returned by IPropertyBuilder::getType()
   * @param IPropertyBuilder $builder Buillder
   * @return IProperty New property 
   * @throws InvalidArgumentException
   * @throws Exception
   */
  public function createProperty( IPropertyBuilder $builder ) : IProperty
  {
    $type = $builder->getType()->value();
    $f = $this->getFactoryFunction( $type );
    
    $prop = $f( $builder );
    if ( !( $prop instanceof IProperty ))
    {
      throw new Exception( sprintf( 
        'Property factory function for property type %s does not return an instance of %s.  got %s.', 
        $type, 
        IProperty::class,
        ( is_object( $prop )) ? get_class( $prop ) : gettype( $prop )
      ));
    }  
    else if ( $prop->getType()->value() != $type )
    {
      throw new Exception( sprintf( 'Property factory function for property type %s (%s) does not return an instance '
        . 'of %s with a type set to %s. got %s', $type, $name, IPropertyBuilder::class , $type, get_class( $prop )));
    }
        
    return $prop;
  }  
}
