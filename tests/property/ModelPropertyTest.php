<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2023 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */



declare( strict_types=1 );

use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\property\IModelProperty;
use buffalokiwi\magicgraph\property\IObjectPropertyBuilder;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\property\ModelProperty;



class ModelPropertyTest extends AbstractPropertyTest
{
  protected const defaultValue = null;
  protected const invalidValue = 'foobarbaz';  //..Invalid value used for validation tests 
  
  private $value1 = null;
  private $value2 = null;
  private $defaultValue = null;
  
  
  public function setUp() : void
  {
    parent::setUp();
    
    $m1 = new buffalokiwi\magicgraph\Databag();
    $m2 = new buffalokiwi\magicgraph\Databag();
    $md = new buffalokiwi\magicgraph\Databag();
    
    $this->value1 = $m1;
    $this->value2 = $m2;
    $this->defaultValue = $md;
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
  
  
  protected function getInstance( $pb, $useNull = false ) : IModelProperty
  {  
    return new ModelProperty( $pb );
  }  
    
    
  protected function getPropertyBuilderClassName() : string
  {
    return IObjectPropertyBuilder::class;
  }
 
  
  protected function getPropertyType() : string
  {
    return IPropertyType::TMODEL;
  }
  
  
  protected function createPropertyBuilderBase( $name = self::name, $caption = self::caption )
  {
    $b = parent::createPropertyBuilderBase( $name, $caption );
    $b->method( 'getClass' )->willReturn( \buffalokiwi\magicgraph\Databag::class );
    
    return $b;
  }  
}

