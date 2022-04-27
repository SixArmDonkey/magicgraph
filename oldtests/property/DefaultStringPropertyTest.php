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

use buffalokiwi\magicgraph\property\DefaultStringProperty;
use buffalokiwi\magicgraph\property\EPropertyType;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyBehavior;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\property\PropertyBehavior;
use buffalokiwi\magicgraph\property\SPropertyFlags;
use buffalokiwi\magicgraph\property\StringProperty;
use buffalokiwi\magicgraph\property\StringPropertyBuilder;
use buffalokiwi\magicgraph\ValidationException;


class DefaultStringPropertyTest extends AbstractPropertyTest
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
    return new DefaultStringProperty( $name, $defaultValue, $behavior, ...$flags->getActiveMembers());
  }
  
  
  /**
   * Retrieve the property type to test
   * @return IPropertyType type
   */
  protected function getPropertyType() : IPropertyType
  {
    return EPropertyType::TSTRING();
  }
  
  
  /**
   * Returns some value to test.
   * This must be of the appropriate type.
   * DO NOT RETURN NULL.
   * @return mixed value 
   */
  protected function getValue()
  {
    return 'value1';
  }
  
  
  /**
   * Returns a second value to test.
   * This must be of the appropriate type.
   * DO NOT RETURN NULL.
   * @return mixed value 
   */
  protected function getValue2()
  {
    return 'value2';
  }
  
  
  public function testPatternProperty() : void
  {
    $b = new StringPropertyBuilder( EPropertyType::TSTRING(), new SPropertyFlags(), 'test', 'value1', new PropertyBehavior());
    $b->setPattern( '/^' . $this->getValue() . '$/' );
    $prop = new StringProperty( $b );
    
    $prop->setValue( $this->getValue());
    
    $this->expectException( ValidationException::class );
    $prop->setValue( $this->getValue2());    
  }
  
  
  public function testMinProperty() : void 
  {
    $b = new StringPropertyBuilder( EPropertyType::TSTRING(), new SPropertyFlags(), 'test', 'value1', new PropertyBehavior());
    $b->setMin( 2 );
    $b->setMax( 5 );
    
    $prop = new StringProperty( $b );
    
    $prop->setValue( 'abc' );
    $this->assertEquals( 'abc', $prop->getValue());
    
    $this->expectException( ValidationException::class );
    $prop->setValue( 'a' );    
  }
  
  
  public function testMaxProperty() : void 
  {
    $b = new StringPropertyBuilder( EPropertyType::TSTRING(), new SPropertyFlags(), 'test', 'value1', new PropertyBehavior());
    $b->setMin( 2 );
    $b->setMax( 5 );
    
    echo '**************';
    $prop = new StringProperty( $b );
    
    $prop->setValue( 'abc' );
    $this->assertEquals( 'abc', $prop->getValue());
    
    $this->expectException( ValidationException::class );
    $prop->setValue( 'abcdefghij' );    
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
    $prop->setValue( null );
    
    //..Without the use_null flag, string properties convert null to an empty string.
    $this->assertEquals( '', $prop->getValue());
  }  
}
