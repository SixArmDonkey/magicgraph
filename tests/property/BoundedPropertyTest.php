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

use buffalokiwi\magicgraph\property\BoundedPropertyBuilder;
use buffalokiwi\magicgraph\property\IBoundedProperty;
use buffalokiwi\magicgraph\property\IBoundedPropertyBuilder;



abstract class BoundedPropertyTest extends AbstractPropertyTest 
{
  //..Override these in some subclass when testing subclasses of AbstractProperty 
  protected const min = PHP_FLOAT_MIN;
  protected const max = PHP_FLOAT_MAX;
  
  
  public function testGetMinMax() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $this->assertSame( static::min, $instance->getMin());
    $this->assertSame( static::max, $instance->getMax());
  }

  
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  
  protected function getPropertyBuilderClassName() : string
  {
    return IBoundedPropertyBuilder::class;
  }
  
  
  protected function createPropertyBuilderBase( $name = self::name, $caption = self::caption )
  {
    $b = parent::createPropertyBuilderBase( $name, $caption );
    $b->method( 'getMin' )->willReturn( static::min );
    $b->method( 'getMax' )->willReturn( static::max );
    
    return $b;
  }
}
