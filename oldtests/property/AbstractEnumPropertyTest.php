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

require_once( __DIR__ . '/AbstractPropertyTest.php' );

use buffalokiwi\buffalotools\types\IEnum;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\PropertyBehavior;
use buffalokiwi\magicgraph\property\SPropertyFlags;



/**
 * For Enum testing.
 * 
 * value comparisons may not be the same object instance and additional type checks are necessary.
 */
abstract class AbstractEnumPropertyTest extends AbstractPropertyTest
{
  /**
   * Tests that the default value is property assigned and returned when creating a property.
   * Also tests the IPropertyBehavior init callback where the default value can be modified.
   * @return void
   */
  public function testGetDefaultValue() : void
  {
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior(), $this->getValue());
    $this->assertInstanceOf( IEnum::class, $prop->getValue());
    $this->assertEquals( get_class( $this->getValue()), get_class( $prop->getValue()));
    $this->assertEquals( $this->getValue()->value(), $prop->getValue()->value());

    //..Test with a callback that can override the specified default value 
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior( null, function( $value ) {
      return $this->getValue2();
    }), $this->getValue());
    
    $this->assertEquals( $this->getValue2()->value(), $prop->getValue()->value());
  }  
  
  


  /**
   * Tests the setValue() and getValue() methods.
   * Ensures that values are valid, and throws exceptions if not.
   * Tests IPropertyBehavior setter for overriding or modifying values 
   * @return void
   */
  public function testGetSetValue() : void
  {
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior(), $this->getValue());
    $prop->setValue( $this->getValue2());
    $this->assertInstanceOf( IEnum::class, $prop->getValue());
    $this->assertEquals( get_class( $this->getValue()), get_class( $prop->getValue()));
    
    $this->assertEquals( $this->getValue2()->value(), $prop->getValue()->value());
    
      
    
    //..Test with a callback that can override the specified default value 
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior( null, null, function( IProperty $prop, $value ) {
      return $this->getValue2();
    }), $this->getValue());
    
    $prop->setValue( $this->getValue());
    $this->assertEquals( $this->getValue2()->value(), $prop->getValue()->value());
    
    //..Test getter override 
    //..Test with a callback that can override the specified default value 
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior( null, null, null, function( IProperty $prop, $value ) {
      return $this->getValue2();
    }), $this->getValue());
    
    $prop->setValue( $this->getValue());
    $this->assertEquals( (string)$this->getValue2(), (string)$prop->getValue());
  }
    
  
  
  /**
   * This must be called immediately after object creation and/or if you just
   * want to reset the internal state of the property.
   * The IPropertyBehavior init callback is also utilized.
   * The init callack is tested in testGetDefaultValue()    
   * @return void
   */
  public function testReset() : void
  {
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior(), $this->getValue());
    $prop->setValue( $this->getValue2());
    $prop->reset();
    $this->assertInstanceOf( IEnum::class, $prop->getValue());
    $this->assertEquals( get_class( $this->getValue()), get_class( $prop->getValue()));
    
    $this->assertEquals( $this->getValue()->value(), $prop->getValue()->value());    
  }
    
}
