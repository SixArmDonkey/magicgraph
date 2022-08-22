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


class BoundedPropertyBuilderTest extends PropertyBuilderTest
{
  public function getInstance( $type, $flags = null, $name = '', $defaultValue = null, ...$behavior )
  {
    return new BoundedPropertyBuilder( $type, $flags, $name, $defaultValue, ...$behavior );
  }
  
  
  public function testGetSetMin() : void
  {
    $this->assertSame( PHP_FLOAT_MIN, $this->instance->getMin());
    $this->instance->setMin((float)1 );
    $this->assertSame((float)1, $this->instance->getMin());
  }
  
  
  public function testGetSetMax() : void
  {
    $this->assertSame( PHP_FLOAT_MAX, $this->instance->getMax());
    $this->instance->setMax((float)100 );
    $this->assertSame((float)100, $this->instance->getMax());
  }
}
