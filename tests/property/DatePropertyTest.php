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

use buffalokiwi\buffalotools\date\DateTimeWrapper;
use buffalokiwi\magicgraph\property\DateProperty;
use buffalokiwi\magicgraph\property\IDateProperty;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\ValidationException;


class DatePropertyTest extends AbstractPropertyTest
{
  protected const value1 = '2020-01-01';
  protected const value2 = '2021-01-01 12:00:00';
  protected const defaultValue = null;
  protected const invalidValue = 'foobarbaz';  //..Invalid value used for validation tests 
  
  
  public function testSetValueSetsAValueAndIsEdited() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    $this->assertFalse( $instance->isEdited());
    
    
    $this->assertSame( static::defaultValue, $instance->getValue());
    $instance->setValue( static::value1 );
    $value = $instance->getValue();
    /* @var $value DateTimeWrapper */
    $this->assertSame( static::value1, $value->getUTC()->format( 'Y-m-d' ));
    $this->assertTrue( $instance->isEdited());
  }
  
  
  public function testHydrateSetsValueAndEditedIsFalseAndThrowsExceptionWhenEditedIsTrue() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    $this->assertFalse( $instance->isEdited());
    $instance->hydrate( static::value1 );
    $this->assertFalse( $instance->isEdited());
    
    $value = $instance->getValue();
    /* @var $value DateTimeWrapper */
    
    $this->assertSame( static::value1, $instance->getValue()->getUTC()->format( 'Y-m-d' ));
    
    $instance->hydrate( static::value1 );
    $this->assertFalse( $instance->isEdited());

    $value = $instance->getValue();
    /* @var $value DateTimeWrapper */

    $this->assertSame( static::value1, $instance->getValue()->getUTC()->format( 'Y-m-d' ));
    
    $instance->setValue( static::value2 );
    $this->assertTrue( $instance->isEdited());
    
    $value = $instance->getValue();
    /* @var $value DateTimeWrapper */
    
    $this->assertSame( static::value2, $instance->getValue()->getUTC()->format( 'Y-m-d H:i:s' ));
    
    $this->expectException( UnexpectedValueException::class );
    $instance->hydrate( static::value1 );
  }  
  
  
  public function testValidate() : void
  {
    //..Without callbacks, the default validate callback returns true (is valid).
    //..Then protected method validatePropertyValue is called.
    
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    
    //..This should be fine
    $instance->validate( static::value1 );
    $this->expectException( ValidationException::class );
    
    $instance->validate( static::invalidValue );
  }  
  
  
  protected function getInstance( $pb, $useNull = false ) : IDateProperty
  {
    return new DateProperty( $pb );
  }  
  
  
  protected function getPropertyType() : string
  {
    return IPropertyType::TDATE;
  }
}
