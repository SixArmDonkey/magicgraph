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

use buffalokiwi\magicgraph\property\FloatProperty;
use buffalokiwi\magicgraph\property\IBoundedPropertyBuilder;
use buffalokiwi\magicgraph\property\IFloatProperty;


class FloatPropertyTest extends BoundedPropertyTest
{
  protected const min = 2.0;
  protected const max = 10.0;
  protected const defaultValue = 3.0;
  protected const value1 = 4.1;
  protected const value2 = 5.3;
  protected const invalidValue = 1.0;  //..Invalid value used for validation tests 

  
  protected function getInstance( $pb, $useNull = false ) : IFloatProperty
  {
    return new FloatProperty( $pb );
  }
  
  
  protected function getPropertyBuilderClassName() : string
  {
    return IBoundedPropertyBuilder::class;
  }
  
  
  protected function createPropertyBuilderBase( $name = self::name, $caption = self::caption )
  {
    return parent::createPropertyBuilderBase( $name, $caption );
  }  
}
