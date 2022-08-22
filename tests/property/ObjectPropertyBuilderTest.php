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

use buffalokiwi\magicgraph\property\ObjectPropertyBuilder;


class ObjectPropertyBuilderTest extends PropertyBuilderTest 
{
  public function getInstance( $type, $flags = null, $name = '', $defaultValue = null, ...$behavior )
  {
    return new ObjectPropertyBuilder( $type, $flags, $name, $defaultValue, ...$behavior );
  }
  
  
  public function testGetSetClassTypeString() : void
  {
    $this->instance->setClass( 'testclass' );
    $this->assertSame( 'testclass', $this->instance->getClass());
  }
  
  
  public function testSetAndGetCreateClassClosure() : void
  {
    $f = fn() => true;    
    $this->instance->setCreateObjectFactory( $f );
    $this->assertSame( $f, $this->instance->getCreateClassClosure());
    $f1 = $this->instance->getCreateClassClosure();    
    $this->assertInstanceOf( \Closure::class, $f1 );    
    $this->assertTrue( $f1());    
  }
}
