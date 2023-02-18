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

use buffalokiwi\magicgraph\property\IObjectProperty;
use buffalokiwi\magicgraph\property\IObjectPropertyBuilder;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\property\ObjectProperty;

//..A test class 
class ObjectPropertyTestTestClass
{
  public $foo = 'bar';
}


class ObjectPropertyTest extends AbstractPropertyTest
{
  protected const defaultValue = null;
  protected const invalidValue = 'foobarbaz';  //..Invalid value used for validation tests 
  
  private $value1 = null;
  private $value2 = null;
  private $defaultValue = null;
  
  
  public function setUp() : void
  {
    parent::setUp();
    $this->value1 = new stdClass();
    $this->value1->value = 'value1'; 
    
    $this->value2 = new stdClass();
    $this->value2->value = 'value2'; 
    
    $this->defaultValue = new stdClass();
  }
  
  
  /**
   * We want to ensure that the create class closure/factory in the object builder 
   * is preferred over calling new object
   */
  public function testBuilderFactoryIsInvokedPriorToNew()
  {
    $anon = new ObjectPropertyTestTestClass();
    
    $b = parent::createPropertyBuilderBase( 'test', 'caption' );
    $b->method( 'getClass' )->willReturn( ObjectPropertyTestTestClass::class );
    $b->method( 'getCreateClassClosure' )->willReturn( fn() => $anon );
    
    $instance = $this->getInstance( $b )->reset();
    $this->assertSame( $anon, $instance->getValue());
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
    return new ObjectProperty( $pb );
  }  
    
    
  protected function getPropertyBuilderClassName() : string
  {
    return IObjectPropertyBuilder::class;
  }
 
  
  protected function getPropertyType() : string
  {
    return IPropertyType::TOBJECT;
  }
  
  
  protected function createPropertyBuilderBase( $name = self::name, $caption = self::caption )
  {
    $b = parent::createPropertyBuilderBase( $name, $caption );    
    $b->method( 'getClass' )->willReturn( stdClass::class );
    
    return $b;
  }
}
