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

use buffalokiwi\magicgraph\property\StringPropertyBuilder;


class StringPropertyBuilderTest extends BoundedPropertyBuilderTest
{
  public function getInstance( $type, $flags = null, $name = '', $defaultValue = null, ...$behavior )
  {
    return new StringPropertyBuilder( $type, $flags, $name, $defaultValue, ...$behavior );
  }  
  
  
  public function testGetSetPattern() : void
  {
    $this->assertSame( '', $this->instance->getPattern());
    $this->instance->setPattern( 'abc' );
    $this->assertSame( 'abc', $this->instance->getPattern());
  }
}
