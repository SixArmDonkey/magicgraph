<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

abstract class AbstractConstantTest extends TestCase 
{
    
  /**
   * Returns the class name of the class being tested 
   */
  protected abstract function getClass() : string;
  
  /**
   * Retrieves a list of constants that must be in the class defined by 
   * getClass()
   */
  protected abstract function getConstants() : array;

  
  /**
   * There are quite a few constants that must exist, and this simply 
   * checks to see that they all exist, are not empty strings, and
   * are unique.
   */
  public function testRequiredConstantsExist() : void
  {
    $cls = $this->getClass();
    $constants = $this->getConstants();
    
    $vals = [];
    
    foreach( $constants as $c )
    {
      if ( !defined( $cls . '::' . $c ))
        $this->fail( $cls . ' must have a class constant named ' . $c );
      
      
      $val = constant( $cls . '::' . $c );
      
      $this->assertIsString( $val, $cls . '::' . $c . ' must be a string' );
      $this->assertNotEmpty( $val, $cls . '::' . $c . ' must not be empty' );
      
      if ( isset( $vals[$val] ))
        $this->fail( $cls . '::' . $c . ' is a duplicate of ' . $cls . '::' . $vals[$val] );
      
      $vals[$val] = $c;
    }
    
  }
}
