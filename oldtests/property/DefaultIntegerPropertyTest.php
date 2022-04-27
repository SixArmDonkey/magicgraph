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

use buffalokiwi\magicgraph\property\BoundedPropertyBuilder;
use buffalokiwi\magicgraph\property\DefaultIntegerProperty;
use buffalokiwi\magicgraph\property\EPropertyType;
use buffalokiwi\magicgraph\property\IntegerProperty;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyBehavior;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\property\PropertyBehavior;
use buffalokiwi\magicgraph\property\SPropertyFlags;
use buffalokiwi\magicgraph\ValidationException;



class DefaultIntegerPropertyTest extends AbstractPropertyTest
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
    return new DefaultIntegerProperty( $name, $defaultValue, $behavior, ...$flags->getActiveMembers());
  }
  
  
  /**
   * Retrieve the property type to test
   * @return IPropertyType type
   */
  protected function getPropertyType() : IPropertyType
  {
    return EPropertyType::TINTEGER();
  }
  
  
  /**
   * Returns some value to test.
   * This must be of the appropriate type.
   * DO NOT RETURN NULL.
   * @return mixed value 
   */
  protected function getValue()
  {
    return 0;
  }
  
  /**
   * Returns a second value to test.
   * This must be of the appropriate type.
   * DO NOT RETURN NULL.
   * @return mixed value 
   */
  protected function getValue2()
  {
    return 1;
  }
  
  
  public function testMinimum() : void
  {
    $b = new BoundedPropertyBuilder( EPropertyType::TINTEGER(), new SPropertyFlags(), 'test', 0, new PropertyBehavior());
    $b->setMin( -1 );
    $b->setMax( 1 );
    
    $prop = new IntegerProperty( $b );
    $prop->setValue( 0 );
    $this->assertEquals( 0, $prop->getValue());
    
    $this->expectException( ValidationException::class );
    $prop->setValue( -10 );
  }
  
  
  public function testMaximum() : void
  {
    $b = new BoundedPropertyBuilder( EPropertyType::TINTEGER(), new SPropertyFlags(), 'test', 0, new PropertyBehavior());
    $b->setMin( -1 );
    $b->setMax( 1 );
    
    $prop = new IntegerProperty( $b );
    $prop->setValue( 0 );
    $this->assertEquals( 0, $prop->getValue());
    
    $this->expectException( ValidationException::class );
    $prop->setValue( 10 );
  }  
}
