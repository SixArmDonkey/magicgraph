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

use buffalokiwi\magicgraph\property\GenericNamedPropertyBehavior;
use PHPUnit\Framework\TestCase;


class GenericNamedPropertyBehaviorTest extends TestCase
{
  public function testConstructorThrowsExceptionnWhenNameParamIsEmpty()
  {
    try {
      new GenericNamedPropertyBehavior( '' );
      $this->fail( 'Constructor must throw InvalidArgumentException when $name is empty' );
    } catch( InvalidArgumentException $e ) {
      //..Expected
    }
   
    
    $this->expectNotToPerformAssertions();
  }
  
  
  public function testGetPropertyNameReturnsValueSuppliedToConstructor()
  {
    $c = new GenericNamedPropertyBehavior( 'test' );
    $this->assertSame( 'test', $c->getPropertyName());
  }
}
