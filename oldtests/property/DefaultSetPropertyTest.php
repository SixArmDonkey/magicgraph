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
require_once( __DIR__ . '/../SampleSet.php' );

use buffalokiwi\magicgraph\property\DefaultSetProperty;
use buffalokiwi\magicgraph\property\EPropertyType;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyBehavior;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\property\PropertyBehavior;
use buffalokiwi\magicgraph\property\SPropertyFlags;
use buffalokiwi\magicgraph\ValidationException;


class DefaultSetPropertyTest extends AbstractPropertyTest
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
    if ( !is_array( $defaultValue ))
      $defaultValue = [$defaultValue];
    return new DefaultSetProperty( SampleSet::class, $name, $defaultValue, $behavior, ...$flags->getActiveMembers());
  }
  
  
  /**
   * Retrieve the property type to test
   * @return IPropertyType type
   */
  protected function getPropertyType() : IPropertyType
  {
    return EPropertyType::TSET();
  }
  
  
  /**
   * Returns some value to test.
   * This must be of the appropriate type.
   * DO NOT RETURN NULL.
   * @return mixed value 
   */
  protected function getValue()
  {
    return SampleSet::KEY1;
  }
  
  /**
   * Returns a second value to test.
   * This must be of the appropriate type.
   * DO NOT RETURN NULL.
   * @return mixed value 
   */
  protected function getValue2()
  {
    return SampleSet::KEY2;
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
      if ( $value == $other )
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
}
