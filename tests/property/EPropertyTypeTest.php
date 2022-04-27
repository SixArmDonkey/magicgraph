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

use buffalokiwi\magicgraph\property\EPropertyType;
use PHPUnit\Framework\TestCase;



class EPropertyTypeTest extends TestCase
{
  /**
   * Simply ensures that constants required by MagicGraph exist
   * @return void
   */
  public function testEnumContainsConstants() : void
  {
    $c = EPropertyType::constants();
    
    $this->assertArrayHasKey( 'TBOOLEAN', $c );
    $this->assertArrayHasKey( 'TINTEGER', $c );
    $this->assertArrayHasKey( 'TFLOAT', $c );
    $this->assertArrayHasKey( 'TSTRING', $c );
    $this->assertArrayHasKey( 'TENUM', $c );
    $this->assertArrayHasKey( 'TRTENUM', $c );
    $this->assertArrayHasKey( 'TARRAY', $c );
    $this->assertArrayHasKey( 'TSET', $c );
    $this->assertArrayHasKey( 'TDATE', $c );
    $this->assertArrayHasKey( 'TMONEY', $c );
    $this->assertArrayHasKey( 'TMODEL', $c );
    $this->assertArrayHasKey( 'TOBJECT', $c );
  }
}
