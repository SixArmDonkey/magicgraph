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

use buffalokiwi\magicgraph\property\PropertyBehavior;
use PHPUnit\Framework\TestCase;


/**
 * This simply tests the DTO side of this class.  There is zero testing of the anonymous function parameters
 * or return types.
 */
class PropertyBehaviorTest extends TestCase
{
  /**
   * Ensure that passing nothing to the constructor causes each method to return null instead of a closure
   * 
   * ( ?Closure $validate = null, ?Closure $init = null, ?Closure $setter = null, 
   * ?Closure $getter = null, ?Closure $msetter = null, ?Closure $mgetter = null, ?Closure $onChange = null,
   * ?Closure $isEmpty = null, ?Closure $htmlInput = null, ?Closure $toArray = null )
   */
  public function testConstructorArgumentsMayBeNull() : void
  {
    $c = new PropertyBehavior();
    
    $this->assertNull( $c->getOnChangeCallback());
    $this->assertNull( $c->getIsEmptyCallback());
    $this->assertNull( $c->getValidateCallback());
    $this->assertNull( $c->getSetterCallback());
    $this->assertNull( $c->getGetterCallback());
    $this->assertNull( $c->getModelSetterCallback());
    $this->assertNull( $c->getModelGetterCallback());
    $this->assertNull( $c->getInitCallback());
    $this->assertNull( $c->getHTMLInputCallback());
    $this->assertNull( $c->getToArrayCallback());    
  }
  
  
  /**
   * Test passing a function to the constructor returns the same function 
   * @return void
   */
  public function testValidateCallback() : void
  {
    $this->assertTrue( is_callable((new PropertyBehavior( fn() => void ))->getValidateCallback()));
  }
  
  
  /**
   * Test passing a function to the constructor returns the same function 
   * @return void
   */
  public function testInitCallback() : void
  {
    $this->assertTrue( is_callable((new PropertyBehavior( null, fn() => void ))->getInitCallback()));
  }
  
  
  /**
   * Test passing a function to the constructor returns the same function 
   * @return void
   */
  public function testSetterCallback() : void
  {
    $this->assertTrue( is_callable((new PropertyBehavior( null, null, fn() => void ))->getSetterCallback()));
  }
  
  
  /**
   * Test passing a function to the constructor returns the same function 
   * @return void
   */
  public function testGetterCallback() : void
  {
    $this->assertTrue( is_callable((new PropertyBehavior( null, null, null, fn() => void ))->getGetterCallback()));
  }


  /**
   * Test passing a function to the constructor returns the same function 
   * @return void
   */
  public function testMSetterCallback() : void
  {
    $this->assertTrue( is_callable((new PropertyBehavior( null, null, null, null, fn() => void ))->getModelSetterCallback()));
  }


  /**
   * Test passing a function to the constructor returns the same function 
   * @return void
   */
  public function testMGetterCallback() : void
  {
    $this->assertTrue( is_callable((new PropertyBehavior( null, null, null, null, null, fn() => void ))->getModelGetterCallback()));
  }
  
  
  /**
   * Test passing a function to the constructor returns the same function 
   * @return void
   */
  public function testOnChangeCallback() : void
  {
    $this->assertTrue( is_callable((new PropertyBehavior( null, null, null, null, null, null, fn() => void ))->getOnChangeCallback()));
  }
  
  
  /**
   * Test passing a function to the constructor returns the same function 
   * @return void
   */
  public function testIsEmptyCallback() : void
  {
    $this->assertTrue( is_callable((new PropertyBehavior( null, null, null, null, null, null, null, fn() => void ))->getIsEmptyCallback()));
  }
  
  
  /**
   * Test passing a function to the constructor returns the same function 
   * @return void
   */
  public function testHTMLInputCallback() : void
  {
    $this->assertTrue( is_callable((new PropertyBehavior( null, null, null, null, null, null, null, null, fn() => void ))->getHTMLInputCallback()));
  }
  
  
  /**
   * Test passing a function to the constructor returns the same function 
   * @return void
   */
  public function testToArrayCallback() : void
  {
    $this->assertTrue( is_callable((new PropertyBehavior( null, null, null, null, null, null, null, null, null, fn() => void ))->getToArrayCallback()));
  }
  
  
  public function testClone() : void
  {
    //..Set up unique function return values for each callback 
    $c = new PropertyBehavior(
      fn() => 0,
      fn() => 1,
      fn() => 2,
      fn() => 3,
      fn() => 4,
      fn() => 5,
      fn() => 6,
      fn() => 7,
      fn() => 8,
      fn() => 9
    );
    
    
    //..Clone
    $c1 = clone $c;
    
    //..Test that every method returns a function 
    $this->assertTrue( is_callable( $c1->getValidateCallback()));
    $this->assertTrue( is_callable( $c1->getInitCallback()));
    $this->assertTrue( is_callable( $c1->getSetterCallback()));
    $this->assertTrue( is_callable( $c1->getGetterCallback()));
    $this->assertTrue( is_callable( $c1->getModelSetterCallback()));
    $this->assertTrue( is_callable( $c1->getModelGetterCallback()));
    $this->assertTrue( is_callable( $c1->getOnChangeCallback()));
    $this->assertTrue( is_callable( $c1->getIsEmptyCallback()));
    $this->assertTrue( is_callable( $c1->getHTMLInputCallback()));
    $this->assertTrue( is_callable( $c1->getToArrayCallback()));    
    
    //..Test that the expected return values are returned 
    
    $this->assertEquals( 0, $c1->getValidateCallback()());
    $this->assertEquals( 1, $c1->getInitCallback()());
    $this->assertEquals( 2, $c1->getSetterCallback()());
    $this->assertEquals( 3, $c1->getGetterCallback()());
    $this->assertEquals( 4, $c1->getModelSetterCallback()());
    $this->assertEquals( 5, $c1->getModelGetterCallback()());
    $this->assertEquals( 6, $c1->getOnChangeCallback()());
    $this->assertEquals( 7, $c1->getIsEmptyCallback()());
    $this->assertEquals( 8, $c1->getHTMLInputCallback()());
    $this->assertEquals( 9, $c1->getToArrayCallback()());         
  }
}
