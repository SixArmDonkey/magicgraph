<?php

require_once( __DIR__ . '/AbstractPropertyTest.php' );

use buffalokiwi\magicgraph\property\DefaultArrayProperty;
use buffalokiwi\magicgraph\property\EPropertyType;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyBehavior;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\property\PropertyBehavior;
use buffalokiwi\magicgraph\property\SPropertyFlags;
use buffalokiwi\magicgraph\ValidationException;

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */
class DefaultArrayPropertyTest extends AbstractPropertyTest
{
  /**
   * Creates a property to test
   * @param string $name Property name
   * @param IPropertyType $type Property type 
   * @param IPropertyFlags $flags Property flag set 
   * @param IPropertyBehavior $behavior Property behavior callbacks 
   * @param mixed $defaultValue Default property value 
   * @return IProperty instance to test
   * @abstract
   */
  protected function createProperty( 
    string $name, 
    IPropertyType $type,
    IPropertyFlags $flags, 
    ?IPropertyBehavior $behavior, 
    $defaultValue 
  ) : IProperty
  {
    return new DefaultArrayProperty( $name, $defaultValue, $behavior, ...$flags->getActiveMembers());
  }
  
  
  /**
   * Retrieve the property type to test
   * @return IPropertyType type
   * @abstract
   */
  protected function getPropertyType() : IPropertyType
  {
    return new EPropertyType( IPropertyType::TARRAY );
  }
  
  
  /**
   * Returns some value to test.
   * This must be of the appropriate type.
   * DO NOT RETURN NULL.
   * @return mixed value 
   * @abstract
   */
  protected function getValue()
  {
    return ['value1'];
  }
  
  
  /**
   * Returns a second value to test.
   * This must be of the appropriate type.
   * DO NOT RETURN NULL.
   * @return mixed value 
   * @abstract
   */
  protected function getValue2()
  {
    return ['value2'];
  }
  
  
  /**
   * Sets a validation behavior callback that purposely fails.
   * Tests that validation throws a ValidationException due to the behavior callback.
   * @return void
   */
  public function testValidateBehaviorCallback() : void
  {
    //..Test behavior validate callback 
    //..Behavior callback will simply return false, which means invalid 
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior( function( IProperty $prop, $value ) {            
      $other = ( !is_array( $this->getValue())) ? [$this->getValue()] : $this->getValue();
      
      //..This will always fail without this line.      
      if ( $value === $other )
       return true;
      
      return false;      
    }), $this->getValue());
    
    //..Triggers an error becuase it's difficult to identify where the error came from without it.
    //..This may change in a future release.
    $this->expectError();
    $prop->validate( $this->getValue2());
    
    PHPUnit_Framework_Error_Warning::$enabled = false;
    $this->expectException( ValidationException::class );
    $prop->validate( $this->getValue2());
    PHPUnit_Framework_Error_Warning::$enabled = true;
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
    $this->assertEquals( $this->getValue2(), $prop->getValue());
    
      
    
    //..Test with a callback that can override the specified default value 
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior( null, null, function( IProperty $prop, $value ) {
      return $this->getValue2();
    }), $this->getValue());
    
    $prop->setValue( $this->getValue());
    $this->assertEquals( $this->getValue2(), $prop->getValue());
    
    //..Test getter override 
    //..Test with a callback that can override the specified default value 
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior( null, null, null, function( IProperty $prop, $value ) {
      return $this->getValue2();
    }), $this->getValue());
    
    $prop->setValue( $this->getValue());
    $this->assertSame( $this->getValue2(), $prop->getValue());
  }  
  
  
  /**
   * Property cloning is required.
   * This ensures that the internals are properly cloned.
   * @return void
   */
  public function testMagicClone() : void
  {
    $flags = new SPropertyFlags();
    $behavior = new PropertyBehavior();
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), $flags, $behavior, $this->getValue());
    //..Setting this to a different value because clone calls reset() and I want to test that reset works properly 
    $prop->setValue( $this->getValue2());
    $type = $prop->getType();
    $value = $prop->getValue();
    
    $c = clone $prop;
    
    $this->assertSame( $this->getValue(), $c->getValue());
    $this->assertSame( $this->getValue2(), $prop->getValue());
    $this->assertNotSame( $prop, $c );
    $this->assertNotSame( $flags, $c->getFlags());
    foreach( $c->getPropertyBehavior() as $b )
    {
      $this->assertNotSame( $behavior, $b );
    }
    $this->assertNotSame( $type, $c->getType());
    
    if ( is_object( $value ))
      $this->assertNotSame( $value, $c->getValue());
  }  
  
  
  /**
   * Assigning null on an array property will actually set the internal array to 
   * empty.  So validating null is fine, even without the allow null flag.
   * @return void
   */
  public function testValidateNullThrowsException() : void
  {
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior(), $this->getValue());
    //..Test invalid value 
    $prop->setValue( null );
    $this->assertIsArray( $prop->getValue());
  }  
}
