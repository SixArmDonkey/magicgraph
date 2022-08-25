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

use buffalokiwi\buffalotools\types\Enum;
use buffalokiwi\buffalotools\types\IEnum;
use buffalokiwi\magicgraph\property\EnumProperty;
use buffalokiwi\magicgraph\property\IObjectProperty;
use buffalokiwi\magicgraph\property\IObjectPropertyBuilder;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\ValidationException;


class EnumPropertyTestEnum1 extends Enum
{
  const VALUE1 = 'value1';
  const VALUE2 = 'value2';
}


class EnumPropertyTest extends AbstractPropertyTest
{
  protected const invalidValue = 'foobarbaz';  //..Invalid value used for validation tests 
  
  private $value1 = null;
  private $value2 = null;
  private $defaultValue = null;

  
  
  public function setUp() : void
  {
    parent::setUp();
    $this->value1 = new EnumPropertyTestEnum1( EnumPropertyTestEnum1::VALUE1 );
    $this->value2 = new EnumPropertyTestEnum1( EnumPropertyTestEnum1::VALUE2 );    
    $this->defaultValue = new EnumPropertyTestEnum1( EnumPropertyTestEnum1::VALUE1 );
  }
  
  
  public function testGetValueAsEnumReturnsEnum() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    $instance->setValue( $this->value1 );
    $this->assertInstanceOf( IEnum::class, $instance->getValueAsEnum());
    $this->assertSame( $this->value1->value(), $instance->getValueAsEnum()->value());
  }
  
  
  
  public function testSetValueAcceptsEnumAndString() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    $instance->setValue( $this->value1 );
    $this->assertInstanceOf( IEnum::class, $instance->getValue());
    $this->assertSame( $this->value1->value(), $instance->getValue()->value());
    
    $instance->setValue( $this->value2->value());
    $this->assertInstanceOf( IEnum::class, $instance->getValue());
    $this->assertSame( $this->value2->value(), $instance->getValue()->value());
  }
  

  public function testSetValueSetsAValueAndIsEdited() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    $this->assertFalse( $instance->isEdited());
    
    $this->assertSame( EnumPropertyTestEnum1::VALUE1, $instance->getValue()->value());
    $instance->setValue( EnumPropertyTestEnum1::VALUE2 );
    $this->assertSame( EnumPropertyTestEnum1::VALUE2, $instance->getValue()->value());
    $this->assertTrue( $instance->isEdited());
  }
  
  
  public function testHydrateSetsValueAndEditedIsFalseAndThrowsExceptionWhenEditedIsTrue() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    $this->assertFalse( $instance->isEdited());
    $instance->hydrate( EnumPropertyTestEnum1::VALUE1 );
    $this->assertFalse( $instance->isEdited());
    
    $this->assertSame( EnumPropertyTestEnum1::VALUE1, $instance->getValue()->value());
    
    $instance->hydrate( EnumPropertyTestEnum1::VALUE2 );
    $this->assertFalse( $instance->isEdited());

    $this->assertSame( EnumPropertyTestEnum1::VALUE2, $instance->getValue()->value());
    
    $instance->setValue( EnumPropertyTestEnum1::VALUE2 );
    $this->assertTrue( $instance->isEdited());
    
    $this->assertSame( EnumPropertyTestEnum1::VALUE2, $instance->getValue()->value());
    
    $this->expectException( UnexpectedValueException::class );
    $instance->hydrate( EnumPropertyTestEnum1::VALUE1 );
  }  
  
  
  public function testValidate() : void
  {
    //..Without callbacks, the default validate callback returns true (is valid).
    //..Then protected method validatePropertyValue is called.
    
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    
    //..This should be fine
    $instance->validate( EnumPropertyTestEnum1::VALUE1 );
    $this->expectException( ValidationException::class );
    
    $instance->validate( static::invalidValue );
  }    
  
  
  //..Enum may never be empty
  public function testIsEmptyReturnTrueBeforeResetOrWhenValueIsEqualToDefaultValue() : void
  {
    $this->expectNotToPerformAssertions();
  }
  
  
  //..Enum may never be empty
  public function testSetValueThrowsExceptionWhenWriteEmptyFlagIsSetAndPropertyIsNotEmpty() : void
  {
    $this->expectNotToPerformAssertions();
  }  
  
  
  protected function getConstValue1() : mixed
  {
    return $this->value1;
  }
  
  
  protected function getConstValue2() : mixed
  {
    return $this->value2;
  }
  
  
  protected function getConstDefaultValue() : mixed
  {
    return $this->defaultValue;
  }
  
  
  protected function getInstance( $pb, $useNull = false ) : IObjectProperty
  {  
    return new EnumProperty( $pb );
  }  
    
    
  protected function getPropertyBuilderClassName() : string
  {
    return IObjectPropertyBuilder::class;
  }
 
  
  protected function getPropertyType() : string
  {
    return IPropertyType::TENUM;
  }
  
  
  protected function createPropertyBuilderBase( $name = self::name, $caption = self::caption )
  {
    $b = parent::createPropertyBuilderBase( $name, $caption );
    
    $b->method( 'getClass' )->willReturn( EnumPropertyTestEnum1::class );
    
    return $b;
  }
}
