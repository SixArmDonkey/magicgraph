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

use buffalokiwi\buffalotools\types\IEnum;
use buffalokiwi\buffalotools\types\ISet;
use buffalokiwi\magicgraph\property\DefaultBooleanProperty;
use buffalokiwi\magicgraph\property\DefaultEnumProperty;
use buffalokiwi\magicgraph\property\DefaultFloatProperty;
use buffalokiwi\magicgraph\property\DefaultIntegerProperty;
use buffalokiwi\magicgraph\property\DefaultSetProperty;
use buffalokiwi\magicgraph\property\DefaultStringProperty;
use buffalokiwi\magicgraph\property\EPropertyType;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\property\ModelProperty;
use buffalokiwi\magicgraph\property\ObjectProperty;
use buffalokiwi\magicgraph\property\ObjectPropertyBuilder;
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
  public function __construct()
  {
    parent::__construct( new StandardPropertySet( new QuickPropertyConfig([])));
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
      //..Code smell.  Replace with a factory?
      if ( is_string( $value ))
      {
        $prop = (new DefaultStringProperty( $property ))->reset();
      }      
      else if ( is_int( $value ))
      {
        $prop = ( new DefaultIntegerProperty( $property ))->reset();
      }
      else if ( is_bool( $value ))
      {
        $prop = ( new DefaultBooleanProperty( $property, false ))->reset();
      }
      else if ( is_float( $value ))
      {
        $prop = ( new DefaultFloatProperty( $property ))->reset();
      }
      else if ( $value instanceof IModel )
      {
        
        $b = new ObjectPropertyBuilder( EPropertyType::TMODEL());
        $b->setName( $property );
        $b->setClass( get_class( $value ));
        $prop = ( new ModelProperty( $b ))->reset();
        
      }
      else if ( $value instanceof IEnum )
      {
        $prop = (new DefaultEnumProperty( get_class( $value ), $property, $value->value()))->reset();
      }
      else if ( $value instanceof ISet )
      {
        $prop = ( new DefaultSetProperty( get_class( $value ), $property, $value->getActiveMembers()))->reset();
      }
      else if ( is_object( $value ))
      {
        $b = new ObjectPropertyBuilder( EPropertyType::TOBJECT());
        $b->setName( $property );
        $b->setClass( get_class( $value ));
        $prop = (new ObjectProperty( $b ))->reset();
      }
      else
        throw new \InvalidArgumentException( 'Unsupported property type' );
      
      $this->getPropertySet()->addProperty( $prop );
    }
    else
      $prop = $this->getPropertySet()->getProperty( $property );
    
    parent::setValue( $prop->getName(), $value );

  }
}
