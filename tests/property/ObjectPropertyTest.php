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


class ObjectPropertyTest extends AbstractPropertyTest
{
  protected const defaultValue = null;
  protected const invalidValue = 'foobarbaz';  //..Invalid value used for validation tests 
  
  private $value1 = null;
  private $value2 = null;
  
  
  public function setUp() : void
  {
    parent::setUp();
    $this->value1 = new stdClass();
    $this->value1->value = 'value1'; 
    
    $this->value2 = new stdClass();
    $this->value2->value = 'value2'; 
  }
  
  
  protected function getConstValue1() : mixed
  {
    return $this->value1;
  }
  
  
  protected function getConstValue2() : mixed
  {
    return $this->value2;
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
