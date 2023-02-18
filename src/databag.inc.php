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

use buffalokiwi\buffalotools\types\IEnum;
use buffalokiwi\buffalotools\types\ISet;
use buffalokiwi\magicgraph\property\EPropertyType;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\property\QuickPropertyConfig;
use buffalokiwi\magicgraph\property\StandardPropertySet;
use InvalidArgumentException;



/**
 * A model
 * 
 * Do things like
 * 
 * $d = new Databag();
 * 
 * $d->foo = 'bar';
 * $d->bar = 1;
 * $d1 = new Databag();
 * $d1->foo = 'nestedfoo';
 * $d->baz = $d1;
 */
class Databag extends DefaultModel
{
  private static ?IPropertyType $stringType = null;
  private static ?IPropertyType $intType = null;
  private static ?IPropertyType $boolType = null;
  private static ?IPropertyType $floatType = null;
  private static ?IPropertyType $modelType = null;
  private static ?IPropertyType $enumType = null;
  private static ?IPropertyType $setType = null;
  private static ?IPropertyType $objectType = null;
  
  public function __construct()
  {
    parent::__construct( new StandardPropertySet( new QuickPropertyConfig([])));
    
    if ( self::$stringType === null )
    {
      self::$stringType = EPropertyType::TSTRING();      
      self::$intType = EPropertyType::TINTEGER();
      self::$boolType = EPropertyType::TBOOLEAN();
      self::$floatType = EPropertyType::TFLOAT();
      self::$modelType = EPropertyType::TMODEL();
      self::$enumType = EPropertyType::TENUM();
      self::$setType = EPropertyType::TSET();
      self::$objectType = EPropertyType::TOBJECT();
    }
  }
  
  
  /**
   * Sets the value of some property.
   * 
   * If the IProperty::getPrepare() callback is used, $aValue is supplied as an argument, and the 
   * result of that callback is used as the value moving forward.
   * 
   * The value is validated against IProperty::validate()
   * 
   * The value is committed to the model using commitValue() 
   * 
   * The edited property set has the corresponding bit enabled 
   * 
   * @param string $property Property to set
   * @param mixed $value property value
   * @throws InvalidArgumentException if the property is not a member of this model
   * @throws ValidationException if value is invalid 
   */
  public function setValue( string $property, $value ) : void
  {
    if ( !$this->getPropertySet()->isMember( $property ))
    {
      //..Code smell?    
      $more = [];
      if ( is_string( $value ))
      {
        $type = self::$stringType;        
      }      
      else if ( is_int( $value ))
      {
        $type = self::$intType;
      }
      else if ( is_bool( $value ))
      {
        $type = self::$boolType;
      }
      else if ( is_float( $value ))
      {
        $type = self::$floatType;
      }
      else if ( $value instanceof IModel )
      {
        $type = self::$modelType;
        $more = [QuickPropertyConfig::CLAZZ => $value];
      }
      else if ( $value instanceof IEnum )
      {
        $type = self::$enumType;
        $more = [QuickPropertyConfig::CLAZZ => get_class( $value )];
        $value = $value->value();
      }
      else if ( $value instanceof ISet )
      {
        $type = self::$setType;
        $more = [QuickPropertyConfig::CLAZZ => get_class( $value )];
        $value = $value->getActiveMembers();
      }
      else if ( is_object( $value ))
      {
        $type = self::$modelType;
        $more = [QuickPropertyConfig::CLAZZ => get_class( $value )];
      }
      else
        throw new \InvalidArgumentException( 'Unsupported property type' );
      
      $this->getPropertySet()->addProperty( $this->getPropertySet()->getConfigMapper()->createPropertyByType( $type, $property, $value, $more ));
    }
    else
      $prop = $this->getPropertySet()->getProperty( $property );
    
    parent::setValue( $prop->getName(), $value );
  }
}
