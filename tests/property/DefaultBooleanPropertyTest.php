<?php

require_once( __DIR__ . '/AbstractPropertyTest.php' );

use buffalokiwi\magicgraph\property\DefaultBooleanProperty;
use buffalokiwi\magicgraph\property\EPropertyType;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyBehavior;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\property\SPropertyFlags;

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */
class DefaultBooleanPropertyTest extends AbstractPropertyTest
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
    return new DefaultBooleanProperty( $name, $defaultValue, $behavior, ...$flags->getActiveMembers());
  }
  
  
  /**
   * Retrieve the property type to test
   * @return IPropertyType type
   * @abstract
   */
  protected function getPropertyType() : IPropertyType
  {
    return new EPropertyType( IPropertyType::TBOOLEAN );
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
    return false;
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
    return true;
  }
  
  
  public function testToString() : void
  {
    $prop = $this->makeProperty( 'test', $this->getPropertyType(), new SPropertyFlags(), null, false );
    
    $this->assertEquals( "0", (string)$prop );
    $prop->setValue( true );
    $this->assertEquals( "1", (string)$prop );
  }

}
