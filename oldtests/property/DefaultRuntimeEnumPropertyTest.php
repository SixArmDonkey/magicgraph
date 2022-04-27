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

require_once( __DIR__ . '/AbstractEnumPropertyTest.php' );
require_once( __DIR__ . '/../ValuedEnum.php' );

use buffalokiwi\magicgraph\property\DefaultEnumProperty;
use buffalokiwi\magicgraph\property\EPropertyType;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyBehavior;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\property\PropertyBehavior;
use buffalokiwi\magicgraph\property\SPropertyFlags;
use buffalokiwi\magicgraph\ValidationException;
use buffalokiwi\buffalotools\types\RuntimeEnum;


/**
 * 
 */
class DefaultRuntimeEnumPropertyTest extends AbstractEnumPropertyTest
{
  /**
   * Creates a property to test
   * @param string $name Property name
   * @param IPropertyType $type Property type 
   * @param IPropertyFlags $flags Property flag set 
   * @param IPropertyBehavior $behavior Property behavior callbacks 
   * @param mixed $defaultValue Default property value 
   * @return IProperty instance to test
   */
  protected function createProperty( 
    string $name, 
    IPropertyType $type,
    IPropertyFlags $flags, 
    ?IPropertyBehavior $behavior, 
    $defaultValue 
  ) : IProperty
  {
    return new DefaultEnumProperty( ValuedEnum::class, $name, $defaultValue->value(), $behavior, ...$flags->getActiveMembers());
  }
  
  
  /**
   * Retrieve the property type to test
   * @return IPropertyType type
   */
  protected function getPropertyType() : IPropertyType
  {
    return EPropertyType::TENUM();
  }
  
  
  /**
   * Returns some value to test.
   * This must be of the appropriate type.
   * DO NOT RETURN NULL.
   * @return mixed value 
   */
  protected function getValue()
  {
    return ValuedEnum::KEY1();
  }
  
  /**
   * Returns a second value to test.
   * This must be of the appropriate type.
   * DO NOT RETURN NULL.
   * @return mixed value 
   */
  protected function getValue2()
  {
    return ValuedEnum::KEY2();
  }


  /**
   * Tests that setValue throws a ValidationException when supplied
   * with an invalid value
   * @return void
   */
  public function testSetValueThrowsValidationException() : void
  {
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior(), $this->getValue());
    $this->expectException( ValidationException::class );
    $prop->setValue( 'badvalue' );
  }
  
  
  /**
   * Tests that setValue throws a ValidationException when supplied with 
   * an instance of IEnum that does not match the type contained within the 
   * property.
   * @return void
   */
  public function testSetValueThrowsValidationExceptionWhenSuppliedWithOtherEnumInstance() : void
  {
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior(), $this->getValue());
    $this->expectException( ValidationException::class );
    $prop->setValue( new RuntimeEnum( ['test'] ));
  }
  
  
  /**
   * Tests that setValue accepts an instance of IEnum that matches the 
   * type stored in the property.
   */
  public function testSetValueAcceptsEnumInstanceOfSameType()
  {    
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior(), ValuedEnum::KEY1());
    $prop->setValue( ValuedEnum::KEY2());
    $this->assertEquals( ValuedEnum::KEY2, (string)$prop->getValue());
  }
  
  
  public function testGetEnumValue()
  {
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), new PropertyBehavior(), ValuedEnum::KEY1());
    $this->assertInstanceOf( buffalokiwi\magicgraph\property\IEnumProperty::class, $prop );
    
    $prop->setValue( ValuedEnum::KEY2());
    
    /* @var $prop buffalokiwi\magicgraph\property\IEnumProperty */
    $this->assertEquals( ValuedEnum::KEY2, (string)$prop->getValue());
    $this->assertEquals( ValuedEnum::VALUE2, $prop->getValueAsEnum()->getStoredValue());
  }
}
