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
use buffalokiwi\magicgraph\ValidationException;


/**
 * The internals are explicitly tested because all of the properties shipped with magic graph 
 * descend from AbstractProperty.  Subclasses depend on the functionality included within protected 
 * methods and behavior callbacks managed by AbstractProperty.
 * 
 * The property builder utilized within these tests is based on a string property and MUST always be based on 
 * strings, or not have a defined type.
 * 
 * Use AbstractPropertyTest as the base class for testing public methods and subclasses of AbstractProperty.
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
class AbstractPropertyInternalsTest extends AbstractPropertyTest
{
  protected function getInstance( $pb, $useNull = false ) : IProperty
  {
    $dv = static::defaultValue;
    $val1 = static::value1;
    $val2 = static::value2;
    
    return new class( $pb, $dv, $val1, $val2, $useNull ) extends AbstractProperty {
      private $dv;
      private $v1;
      private $v2;
      private $useNull;
      
      public function __construct( $pb, $dv, $v1, $v2, $un ) {
        parent::__construct( $pb );
        $this->dv = $dv;
        $this->v1 = $v1;
        $this->v2 = $v2;
        $this->useNull = $un;
      }
      
      protected function validatePropertyValue( $value ) : void {
        if ( $this->useNull && $value === null )
          return;        
        else if ( $value !== $this->dv && $value !== $this->v1 && $value !== $this->v2 )
        {
          throw new ValidationException( 'value must be equal to static::value1 or static::value2' );;
        }
      }
      
      public function __toString() : string {
        return (string)$this->getValue();
      }
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
      public function __toString() : string {
        return (string)$this->getValue();
      }
      
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
    $instance = $this->getInstance( $this->createPropertyBuilder());
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
    $builder = $this->createPropertyBuilder();
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
    $builder = $this->createPropertyBuilder();
    
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
    $builder = $this->createPropertyBuilder();
    
    $b1 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b1->method( 'getInitCallback' )->willReturn( fn( $val ) => $val . '1' );
   
    $b2 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b2->method( 'getInitCallback' )->willReturn( fn( $val ) => $val . '2' );
    $builder->method( 'getBehavior' )->willReturn( [$b1, $b2] );
    
    //..Need to customize a bit 
    $instance = new class( $builder ) extends AbstractProperty {
      public function __toString() : string {
        return (string)$this->getValue();
      }
      
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
   * When calling reset, test that preparePropertyValue is called and the result is passed to 
   * setPropertyValue and then that result becomes the property value
   * @return void
   */
  public function testResetCallsProtectedFunctionsSetAndPreparePropertyValue() : void
  {
    //..Need to customize a bit 
    $instance = new class( $this->createPropertyBuilder()) extends AbstractProperty {
      public function __toString() : string {
        return (string)$this->getValue();
      }
      
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
  
  
  public function testSetValueCallsPreparePropertyValueBeforeSetterAndThenValidateAndFinallySetPropertyValue() : void
  {
    //..Need to customize a bit 
    
    $b = $this->createPropertyBuilder();
    $b1 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    
    $expectedValue = self::value1 . '1s';
    
    $b1->method( 'getValidateCallback' )->willReturn( static function( IProperty $prop, mixed $value ) use($expectedValue) : bool {
      return $expectedValue === $value;
    });
    
    $b1->method( 'getSetterCallback' )->willReturn( static fn( IProperty $prop, mixed $value ) : mixed => $value . 's' );
    
  
    $b->method( 'getBehavior' )->willReturn( [$b1] );
    
    $instance = new class( $b ) extends AbstractProperty {
      public function __toString() : string {
        return (string)$this->getValue();
      }
      
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
    
    $instance->setValue( self::value1 );
    $this->assertSame( self::value1 . '1s' . '2', $instance->getValue());    
  }  
  
  
  public function testSetValueCallsSetterBehaviorChain() : void
  {
    $b = $this->createPropertyBuilder();
    $b1 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b1->method( 'getSetterCallback' )->willReturn( static fn( IProperty $prop, mixed $value ) : mixed => $value . '1' );
    
    $b2 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b2->method( 'getSetterCallback' )->willReturn( static fn( IProperty $prop, mixed $value ) : mixed => $value . '2' );
  
    $b->method( 'getBehavior' )->willReturn( [$b1, $b2] );
    
    $instance = new class( $b ) extends AbstractProperty {
      public function __toString() : string {
        return (string)$this->getValue();
      }
      
      protected function validatePropertyValue( $value ) : void {}
    };
    
    $instance->reset();
    $instance->setValue( self::value1 );
    $this->assertSame( self::value1 . '1' . '2', $instance->getValue());    
  }
  
  

  public function testSetValueCallsOnChangeBehaviorChain() : void
  {
    //..Change event result 1 and 2 
    $c1 = '';
    $c2 = '';
    $order = [];
    
    
    $b = $this->createPropertyBuilder();
    $b1 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b1->method( 'getOnChangeCallback' )->willReturn( static function( IProperty $prop, mixed $oldValue, mixed $newValue ) use(&$c1, &$order) : void {
      $c1 = $oldValue . $newValue . '1';
      $order[] = '1';
    });
    
    $b2 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b2->method( 'getOnChangeCallback' )->willReturn( static function( IProperty $prop, mixed $oldValue, mixed $newValue ) use(&$c2, &$order) : void {
      $c2 = $oldValue . $newValue . '2';
      $order[] = '2';
    });
  
    $b->method( 'getBehavior' )->willReturn( [$b1, $b2] );
    
    $instance = new class( $b ) extends AbstractProperty {
      public function __toString() : string {
        return (string)$this->getValue();
      }
      
      protected function validatePropertyValue( $value ) : void {}
    };
    
    $instance->reset();
    $instance->setValue( self::value1 );
    
    $this->assertSame( self::defaultValue . self::value1 . '1', $c1 );
    $this->assertSame( self::defaultValue . self::value1 . '2', $c2 );
    $this->assertSame( '12', implode( '', $order ));
  }
  
 
  public function testGetValueCallsProtectedFunctionGetPropertyValue() : void
  {
    $instance = new class( $this->createPropertyBuilder()) extends AbstractProperty {
      public function __toString() : string {
        return (string)$this->getValue();
      }
      
      protected function validatePropertyValue( $value ) : void {}
      
      protected function getPropertyValue( $value ) : mixed {
        return $value . '1';
      }
    };
    
    $instance->reset();    
    $this->assertSame( self::defaultValue . '1', $instance->getValue());
  }  
  
  
  public function testGetValueCallsGetterChain() : void
  {
    $b = $this->createPropertyBuilder();
    $b1 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b1->method( 'getGetterCallback' )->willReturn( static fn( IProperty $prop, mixed $value ) : mixed => $value . '1' );
    
    $b2 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b2->method( 'getGetterCallback' )->willReturn( static fn( IProperty $prop, mixed $value ) : mixed => $value . '2' );
  
    $b->method( 'getBehavior' )->willReturn( [$b1, $b2] );
    
    $instance = new class( $b ) extends AbstractProperty {
      public function __toString() : string {
        return (string)$this->getValue();
      }
      
      protected function validatePropertyValue( $value ) : void {}
    };
    
    $instance->reset();
    $instance->setValue( self::value1 );
    $this->assertSame( self::value1 . '1' . '2', $instance->getValue());        
  }
  
  
  public function testGetValueContextArgumentIsPassedToGetterChain() : void
  {
    $context = ['1'];
    
    $c1Context = null;
    $c2Context = null;
    
    $b = $this->createPropertyBuilder();
    $b1 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b1->method( 'getGetterCallback' )->willReturn( static function( IProperty $prop, mixed $value, array $context ) use(&$c1Context) : mixed {
      $c1Context = $context;
      return $value;
    });
    
    $b2 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b2->method( 'getGetterCallback' )->willReturn( static function( IProperty $prop, mixed $value, array $context ) use(&$c2Context) : mixed {
      $c2Context = $context;
      return $value;
    });
  
    $b->method( 'getBehavior' )->willReturn( [$b1, $b2] );
    
    $instance = new class( $b ) extends AbstractProperty {
      public function __toString() : string {
        return (string)$this->getValue();
      }
      
      protected function validatePropertyValue( $value ) : void {}
    };
    
    $instance->reset();
    $instance->setValue( self::value1 );
    $this->assertSame( self::value1, $instance->getValue( $context ));        
    $this->assertSame( $context, $c1Context );
    $this->assertSame( $context, $c2Context );
  }
  
  
  public function testValidateIsCalledWhenGettersAreUsedInGetValue() : void
  {
    $b = $this->createPropertyBuilder();
    $b1 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b1->method( 'getGetterCallback' )->willReturn( static fn( IProperty $prop, mixed $value ) : mixed => $value );
  
    $b->method( 'getBehavior' )->willReturn( [$b1] );
    
    $instance = new class( $b ) extends AbstractProperty {
      public function __toString() : string {
        return (string)$this->getValue();
      }
      
      protected function validatePropertyValue( $value ) : void {
        throw new ValidationException();        
      }
    };
    
    $instance->reset();
    
    try {
      $instance->setValue( self::value1 );
      $this->fail( 'setValue() should be calling validatePropertyValue()' );
    } catch( ValidationException $e ) {
      //..Expected
    }
    
    $this->expectException( ValidationException::class );
    $this->assertSame( self::value1, $instance->getValue());
  }
  
  
  public function testValidateCallsValidationCallbackChain() : void
  {
    $b = $this->createPropertyBuilder();
    $b1 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b2 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    
    $callChain = [];
    
    $b1->method( 'getValidateCallback' )->willReturn( static function( IProperty $prop, mixed $value ) use(&$callChain) : bool {
      $callChain[] = '1';
      return true;      
    });
    
    $b2->method( 'getValidateCallback' )->willReturn( static function( IProperty $prop, mixed $value ) use(&$callChain) : bool {
      $callChain[] = '2';
      return true;      
    });
    
    $b->method( 'getBehavior' )->willReturn( [$b1, $b2] );
    
    $instance = new class( $b ) extends AbstractProperty {
      protected function validatePropertyValue( $value ) : void {}
    };
    
    $this->assertNull( $instance->getValue());
    $instance->reset();
    $instance->validate( self::value1 );
    $this->assertSame( '12', implode( '', $callChain ));
  }
  
  
  public function testValidateThrowsValidationExceptionWhenCallbackReturnsFalse() : void
  {
    $b = $this->createPropertyBuilder();
    $b1 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    
    $b1->method( 'getValidateCallback' )->willReturn( static function( IProperty $prop, mixed $value ) : bool {
      return false;
    });
    
    $b->method( 'getBehavior' )->willReturn( [$b1] );
    
    $instance = new class( $b ) extends AbstractProperty {
      protected function validatePropertyValue( $value ) : void {}
    };
    
    $instance->reset();
    
    try {
      $this->expectError();
      $instance->validate( self::value1 );
      $this->fail( 'When a property validation callback fails, validate() must throw ValidationException' );
    } catch( ValidationException $e ) {
      //..expected 
    }
  }
  
  
  public function testIsEmptyCallbackChainIsCalledWhenIsEmptyIsCalled() : void
  {
    $b = $this->createPropertyBuilder();
    $b1 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $b2 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    
    $callChain = [];
    
    $b1->method( 'getIsEmptyCallback' )->willReturn( static function( IProperty $prop, mixed $value ) use(&$callChain) : bool {
      $callChain[] = '1';
      return true;      
    });
    
    $b2->method( 'getIsEmptyCallback' )->willReturn( static function( IProperty $prop, mixed $value ) use(&$callChain) : bool {
      $callChain[] = '2';
      return true;      
    });
    
    $b->method( 'getBehavior' )->willReturn( [$b1, $b2] );
    
    $instance = new class( $b ) extends AbstractProperty {
      protected function validatePropertyValue( $value ) : void {}
    };
    
    $instance->reset();
    $instance->isEmpty();
    $this->assertSame( '12', implode( '', $callChain ));    
  }
  
    
  public function testIsEmptyReturnsFalseWhenCallbackReturnsFalse() : void
  {
    $b = $this->createPropertyBuilder();
    $b1 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    
    //..Callback always returns false 
    $b1->method( 'getIsEmptyCallback' )->willReturn( static function( IProperty $prop, mixed $value ) use(&$callChain) : bool {
      return false;
    });
    
    $b->method( 'getBehavior' )->willReturn( [$b1] );
    
    $instance = new class( $b ) extends AbstractProperty {
      protected function validatePropertyValue( $value ) : void {}
      
      //..Standard empty check is always true
      protected function isPropertyEmpty( $value ) : bool {
        return true;
      }
    };
    
    $instance->reset();
    $this->assertFalse( $instance->isEmpty());
  }
  
  
  public function testIsEmptyCallsIsPropertyEmptyWhenNoCallbacksAreSupplied() : void
  {
    $b = $this->createPropertyBuilder();
    
    
    $instance = new class( $b, static::value1 ) extends AbstractProperty {
      private $val1;
      
      public function __construct( $b, $v1 )
      {
        parent::__construct( $b );
        $this->val1 = $v1;
      }
      
      protected function validatePropertyValue( $value ) : void {}
      
      //..Standard empty check is always true
      protected function isPropertyEmpty( $value ) : bool {
        return ( $value == $this->val1 );
      }
    };
    
    $instance->reset();
    $this->assertFalse( $instance->isEmpty());
    $instance->setValue( static::value1 );
    $this->assertTrue( $instance->isEmpty());
    $instance->setValue( static::value2 );
    $this->assertFalse( $instance->isEmpty());
  }
}
