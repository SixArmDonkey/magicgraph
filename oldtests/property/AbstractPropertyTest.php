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


use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyBehavior;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\property\PropertyBehavior;
use buffalokiwi\magicgraph\property\SPropertyFlags;
use buffalokiwi\magicgraph\ValidationException;
use PHPUnit\Framework\TestCase;


/**
 * Tests the AbstractProperty class.
 * 
 * This is currently broken due to the way object comparison is "not" working.
 * Objects that are technically equal may have different internal states, therefore
 * a standard "==" comparison will not work.  Furthermore, the internal property object instance 
 * may be different than the object supplied to setValue(), so "===" will not work either.
 * 
 * When checking values returned by the property, this needs to take objects into
 * consideration.  If objects are being compared, then they should implement the 
 * same interfaces, and they should also have the same value when cast to a string.
 * All properties are required to be able to be cast to strings.
 * 
 */
abstract class AbstractPropertyTest extends TestCase
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
  protected abstract function createProperty( 
    string $name, 
    IPropertyType $type,
    IPropertyFlags $flags, 
    ?IPropertyBehavior $behavior, 
    $defaultValue 
  ) : IProperty;
  
  
  /**
   * Retrieve the property type to test
   * @return IPropertyType type
   * @abstract
   */
  protected abstract function getPropertyType() : IPropertyType;
  
  
  /**
   * Returns some value to test.
   * This must be of the appropriate type.
   * DO NOT RETURN NULL.
   * @return mixed value 
   * @abstract
   */
  protected abstract function getValue();
  
  /**
   * Returns a second value to test.
   * This must be of the appropriate type.
   * DO NOT RETURN NULL.
   * @return mixed value 
   * @abstract
   */
  protected abstract function getValue2();
  


  /**
   * Tests the getName method.
   * Expects the same name to be returned 
   */
  public function testGetName() : void
  {
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), null, $this->getValue());
    $this->assertEquals( 'test', $prop->getName());
  }
  
  
  /**
   * Tests the getType method.
   * Expects an IPropertyType instance 
   * @return void
   */
  public function testGetType() : void
  {
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), null, $this->getValue());
    $this->assertInstanceOf( IPropertyType::class, $prop->getType());
    $this->assertEquals( $this->getPropertyType()->value(), $prop->getType()->value());
  }
  
  
  /**
   * Tests the getFlags() method.
   * Expects an IPropertyFlags instance 
   * @return void
   */
  public function testGetFlags() : void
  {
    $flags = new SPropertyFlags( SPropertyFlags::PRIMARY );
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), $flags, null, $this->getValue());
    $this->assertInstanceOf( IPropertyFlags::class, $prop->getFlags());
    $this->assertEquals( $flags->getValue(), $prop->getFlags()->getValue());
  }
  
  
  /**
   * Tests the getPropertyBehavior() method.
   * Expects an IPropertyBehavior instance or null 
   * @return void
   */
  public function testGetPropertyBehavior() : void
  {
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior(), $this->getValue());
    foreach( $prop->getPropertyBehavior() as $b )
    {
      $this->assertInstanceOf( IPropertyBehavior::class, $b );
    }
  }
  
  
  /**
   * Tests that validating null on a property that does not accept null values throws a ValidationException
   * @return void
   */
  public function testValidateNullException() : void 
  {
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior(), $this->getValue());
    $this->expectException( ValidationException::class );
    $prop->validate( null );
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
      //..This will always fail without this line.      
      if ( $value == $this->getValue())
       return true;
      
      return false;      
    }), $this->getValue());
    
    $this->expectError();
    $prop->validate( $this->getValue2()); //..Validate "true" to fail
    
    PHPUnit_Framework_Error_Warning::$enabled = false;    
    $this->expectException( ValidationException::class );
    $prop->validate( $this->getValue2()); //..Validate "true" to fail
    PHPUnit_Framework_Error_Warning::$enabled = true;
  }
  
  
  /**
   * Tests the validate method.
   * 
   * Passes this->getValue() as the first value
   * Passing null without the SPropertyFlags::USE_NULL flag enabled throws an exception
   * PropertyBehavior validate method returns false and throws an exception
   * calls validateImplementation() to test implementation-specific validation
   * @return void
   */
  public function testValidate() : void
  {
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior(), $this->getValue());
    
    //..Should be fine.
    $this->assertNull( $prop->validate( $this->getValue()));
    
    //..Ensure null works when specified 
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags( SPropertyFlags::USE_NULL ), new PropertyBehavior(), $this->getValue());        
    $this->assertNull( $prop->validate( null ));
    
    
    //..Same as before, but with a successful callback 
    //..This should not fail 
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior( function( IProperty $prop, $value ) {      
       return true;
    }), $this->getValue());
    $this->assertNull( $prop->validate( $this->getValue()));        
  }
  
  
  /**
   * Tests that the default value is property assigned and returned when creating a property.
   * Also tests the IPropertyBehavior init callback where the default value can be modified.
   * @return void
   */
  public function testGetDefaultValue() : void
  {
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior(), $this->getValue());
    $this->assertEquals( $this->getValue(), $prop->getValue());

    //..Test with a callback that can override the specified default value 
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior( null, function( $value ) {
      return $this->getValue2();
    }), $this->getValue());
    
    $this->assertEquals( $this->getValue2(), $prop->getValue());
  }
  
  
  
  /**
   * Test that validating null on a property that does not accept null
   * throws a ValidationException 
   * @return void
   */
  public function testValidateNullThrowsException() : void
  {
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior(), $this->getValue());
    //..Test invalid value 
    $this->expectException( ValidationException::class );
    $prop->setValue( null );
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
    $this->assertEquals( $this->getValue(), $prop->getValue());    
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
    
    $this->assertSame((string)$this->getValue(), (string)$c->getValue());
    $this->assertSame((string)$this->getValue2(), (string)$prop->getValue());
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
   * Calls createProperty()->reset();
   * @param string $name
   * @param IPropertyType $type
   * @param IPropertyFlags $flags
   * @param IPropertyBehavior $behavior
   * @param type $defaultValue
   * @return IProperty
   * @final 
   */
  protected final function makeProperty( 
    string $name, 
    IPropertyType $type,
    IPropertyFlags $flags, 
    ?IPropertyBehavior $behavior, 
    $defaultValue 
  ) : IProperty 
  {
    return $this->createProperty( $name, $type, $flags, $behavior, $defaultValue )->reset();
  }
}
