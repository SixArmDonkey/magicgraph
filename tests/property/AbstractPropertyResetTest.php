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

include_once( __DIR__ . '/AbstractPropertyTest.php' );

use buffalokiwi\magicgraph\property\AbstractProperty;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyBehavior;


/**
 * Testing internals is strange, but this behavior is very important.
 * 
 * This tests AbstractProperty::reset()
 * 
 * 1) test default reset() config sets the value equal to default value
 * 2) test supplied property behavior instances (zero, one or two) init callbacks are invoked when reset() is called
 * 3) test that initCallback result is fed into each subsequent init callback and then that result is passed to preparePropertyValue
 * 4) test that preparePropertyValue is called and the result is passed to setPropertyValue and then that result becomes the property value
 * 5) test that the edited flag is false after reset() 
 * 6) test that reset() then editing some stuff, then calling reset() resets everything and the edit flag
 * 7) test initValue() can write some arbitrary object to the property value, and overriding setPropertyValue can then set properties of that new value object
 */
class AbstractPropertyResetTest extends AbstractPropertyTest
{
  protected function getInstance( $pb ) : IProperty
  {
    return new class( $pb ) extends AbstractProperty {
      protected function validatePropertyValue( $value ) : void {}
    };
  }
  
  
  /**
   * test protected method initValue() is invoked and the result is used to set the property value
   * This is sort of a hack to allow properties to be backed by other objects.
   * 
   * This is going to be a bit of a weird test.  There will be several anonymous classes created to 
   * test various configurations.
   *  
   * @return void
   */
  public function testResetInitValue() : void
  {
    //..Basic test object property implementation
    //..initValue() returns an instance of stdClass with property "value"
    //..When settings the property value, AbstractProperty invokes the protected method setPropertyValue(), which 
    //..may be used to prevent directly setting the property value.  
    //  ie: when the value is an object, we can set properties of that object
    $instance = new class( $this->createPropertyBuilder( 'test' )) extends AbstractProperty {
      protected function validatePropertyValue( $value ) : void {}
      
      //..Initialize is called during reset(), and before defaultValue is used.
      //..This can be used to prepare somewhere for the default value to be written      
      //..In this example, this is a stdClass "{value: null}"
      protected function initValue() : mixed {
        $cls = new stdClass();
        $cls->value = null;
        return $cls;
      }      
      
      //..When the property value is set, this can be used to override that value
      //..The result will become the new property value
      //..In this exampple $curValue contains the value returned by initValue()
      //..$value contains the default property value, (string) "default"
      //..and when IProperty::setValue() is called, the internal value stdClass->value will equal whatever is supplied.
      protected function setPropertyValue( $value, $curValue ) : mixed {
        //..If initValue() works, then $curValue will be an instance of stdClass go
        assert( $curValue instanceof stdClass );
        $curValue->value = $value;
        return $curValue;
      }
    };
    
    //..Prior to reset(), everything is null
    $this->assertNull( $instance->getValue());
    
    //..Reset sets up the property
    $instance->reset();
    
    //..The property value is now a stdClass 
    $v = $instance->getValue();
    $this->assertInstanceOf( stdClass::class, $v );
    $this->assertTrue( isset( $v->value ));
    $this->assertSame( self::defaultValue, $v->value );
  }
  
  
  /**
   * test default reset() config sets the value equal to default value
   * @return void
   */
  public function testResetSetsValueToDefaultValue() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder( 'name' ));
    $this->assertNull( $instance->getValue());
    $instance->reset();
    $this->assertSame( self::defaultValue, $instance->getValue());
  }
  
  
  /**
   * Tests that reset() will invoke getInitCallback on any IPropertyBehavior instances supplied to the property builder.
   * This tests one.
   */
  public function testResetInvokesInitCallback() : void
  {
    $builder = $this->createPropertyBuilder( 'name' );
    $b1 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b1->method( 'getInitCallback' )->willReturn( fn( $val ) => $val . '1' );
    $builder->method( 'getBehavior' )->willReturn( [$b1] );
    
    $instance = $this->getInstance( $builder );
    $this->assertNull( $instance->getValue());
    $instance->reset();
    $this->assertSame( self::defaultValue . '1', $instance->getValue());
  }
  
  
  /**
   * Tests that reset() will invoke getInitCallback on any IPropertyBehavior instances supplied to the property builder.
   * This tests two.
   * 
   * The init callback looks like this:  fn( $value ) : mixed 
   * 
   * The reset() chain looks like this:
   * 
   * 1) uninitialized property value
   * 2) property value is set to result of initValue()
   * 3) Default value is passed to first property behavior init callback
   * 4) Result of previous init callback is supplied to the next init callback until end of list
   * 5) property value is set to result of last init callback
   * 6) value is passed to preparePropertyValue
   * 7) result of preparePropertyValue is passed to setPropertyValue
   * 8) property value is set to result of setPropertyValue 
   * 9) edit flag is set to true
   */
  public function testResetInvokesInitCallbackChain() : void
  {
    $builder = $this->createPropertyBuilder( 'name' );
    
    $b1 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b1->method( 'getInitCallback' )->willReturn( fn( $val ) => $val . '1' );
   
    $b2 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b2->method( 'getInitCallback' )->willReturn( fn( $val ) => $val . '2' );
    $builder->method( 'getBehavior' )->willReturn( [$b1, $b2] );
    
    $instance = $this->getInstance( $builder );
    $this->assertNull( $instance->getValue());
    $instance->reset();
    $this->assertSame( self::defaultValue . '1' . '2', $instance->getValue());
  }

  

  public function testResetInvokesInitCallbackChainAndPreparePropertyValue() : void
  {
    $builder = $this->createPropertyBuilder( 'name' );
    
    $b1 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b1->method( 'getInitCallback' )->willReturn( fn( $val ) => $val . '1' );
   
    $b2 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b2->method( 'getInitCallback' )->willReturn( fn( $val ) => $val . '2' );
    $builder->method( 'getBehavior' )->willReturn( [$b1, $b2] );
    
    //..Need to customize a bit 
    $instance = new class( $builder ) extends AbstractProperty {
      protected function validatePropertyValue( $value ) : void {}
      
      protected function preparePropertyValue( $value ) : mixed {        
        return $value . '3';
      }
    };
    
    $this->assertNull( $instance->getValue());
    $instance->reset();
    $this->assertSame( self::defaultValue . '1' . '2' . '3', $instance->getValue());
  }   
  
  
  
  /**
   * When calling reset, test that preparePropertyValue is called and the result is passed to setPropertyValue and then that result becomes the property value
   * @return void
   */
  public function testResetCallsSetAndPreparePropertyValue() : void
  {
    //..Need to customize a bit 
    $instance = new class( $this->createPropertyBuilder( 'name' )) extends AbstractProperty {
      protected function validatePropertyValue( $value ) : void {}
      
      protected function preparePropertyValue( $value ) : mixed {        
        return $value . '1';
      }
      
      protected function setPropertyValue( $value, $curValue ) : mixed {
        return $value . '2';
      }
    };
    
    $this->assertNull( $instance->getValue());
    $instance->reset();
    $this->assertSame( self::defaultValue . '1' . '2', $instance->getValue());
  }
  
  
  /**
   * @return void
   */
  public function testEditedFlagIsFalseAfterReset() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder( 'name' ));
    $instance->reset();
    $this->assertFalse( $instance->isEdited());
  }
  
  
  /**
   * test that reset() then editing some stuff, then calling reset() resets everything and the edit flag
   */
  public function testEditedFlagIsFalseAfterResetThenEditThenReset() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder( 'name' ));
    $instance->reset();
    $this->assertFalse( $instance->isEdited());
    $instance->setValue( 'test' );
    $this->assertTrue( $instance->isEdited());
    $instance->reset();
    $this->assertFalse( $instance->isEdited());
    
  }
}
