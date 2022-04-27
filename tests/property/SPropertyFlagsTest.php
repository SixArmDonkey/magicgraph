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

use buffalokiwi\magicgraph\property\SPropertyFlags;
use PHPUnit\Framework\TestCase;



class SPropertyFlagsTest extends TestCase
{
  /**
   * Test that the property flags set contains the constants required by MagicGraph 
   * @return void
   */
  public function testSetContainsConstants() : void
  {
    $c = SPropertyFlags::constants();
    
    $this->assertArrayHasKey( 'NO_INSERT', $c );
    $this->assertArrayHasKey( 'NO_UPDATE', $c );
    $this->assertArrayHasKey( 'REQUIRED', $c );
    $this->assertArrayHasKey( 'USE_NULL', $c );
    $this->assertArrayHasKey( 'PRIMARY', $c );
    $this->assertArrayHasKey( 'SUBCONFIG', $c );
    $this->assertArrayHasKey( 'WRITE_EMPTY', $c );
    $this->assertArrayHasKey( 'NO_ARRAY_OUTPUT', $c );
  }
}
