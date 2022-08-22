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

use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyBehavior;
use buffalokiwi\magicgraph\property\IPropertyBuilder;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\property\PropertyBuilder;
use buffalokiwi\magicgraph\ValidationException;
use PHPUnit\Framework\TestCase;



/**
 * This is the base test class for AbstractProperty
 * Extend this to test IProperty implementations 
 * 
 * getValue() and setValue() are used in many of these tests, and I guess therefore "tested".
 * 
 * The internals of getValue() and setValue() are tested in AbstractPropertyInternalsTest 
 * 
 * 
 * 
 * @todo Write tests for __clone()
 */
abstract class AbstractPropertyTest extends TestCase
{
  //..Override these in some subclass when testing subclasses of AbstractProperty 
  protected const name = 'name';
  protected const defaultValue = 'default';
  protected const caption = 'caption';
  protected const id = 1;
  protected const tag = 'tag';
  protected const config = [true];
  protected const prefix = 'prefix';
  protected const flagTotal = 12345;
  protected const value1 = 'test';
  protected const value2 = 'testtwo';
  protected const invalidValue = 1;  //..Invalid value used for validation tests 
    
  protected $propertyBuilder = null;
  protected $instance = null;
  
  protected abstract function getInstance( ?PropertyBuilder $pb ) : IProperty;
  
  
  public function setUp() : void
  {
    $this->propertyBuilder = $this->createPropertyBuilder();
    $this->instance = $this->getInstance( $this->propertyBuilder );
  }
  
  
  /**   
   * When name is empty, InvalidArgumentException is thrown 
   * Supplying a name not matching [a-zA-Z0-9_]+ throws an InvalidArgumentException 
   * Supplying the same invalid name not matching [a-zA-Z0-9_]+ a second time throws an InvalidArgumentException
   * 
   * @return void
   */
  public function testConstructor() : void
  {    
    try {
      $b = $this->createPropertyBuilder( '' );
      $this->getInstance( $b );
      $this->fail( 'When IPropertyBuilder::getName() returns an empty string, an InvalidArgumentException must be thrown' );
    } catch( InvalidArgumentException $e ) {
      //..Expected
    }
    
    
    try {
      $b = $this->createPropertyBuilder( ' ' );
      $this->getInstance( $b );
      $this->fail( 'When IPropertyBuilder::getName() returns an empty string, an InvalidArgumentException must be thrown' );
    } catch( InvalidArgumentException $e ) {
      //..Expected
    }    
    
    //..Just checking a few things here.  Obviously this is not an exhaustive list 
    $b = $this->createPropertyBuilder( 'The_Name1' );
    $this->assertSame( 'The_Name1', $this->getInstance( $b )->getName());
    
    $b = $this->createPropertyBuilder( 'a' );
    $this->assertSame( 'a', $this->getInstance( $b )->getName());

    
    try {
      $b = $this->createPropertyBuilder( 'na me' );
      $this->getInstance( $b );
      $this->fail( 'value returned by IPropertyBuilder::getName() must match [a-zA-Z0-9_]+' );
    } catch( InvalidArgumentException $e ) {
      //..Expected
    }
    

    try {
      $b = $this->createPropertyBuilder( '1' );
      $this->getInstance( $b );
      $this->fail( 'property names must start with a letter' );
    } catch( InvalidArgumentException $e ) {
      //..Expected
    }    

    
    try {
      $b = $this->createPropertyBuilder( '1a' );
      $this->getInstance( $b );
      $this->fail( 'property names must start with a letter' );
    } catch( InvalidArgumentException $e ) {
      //..Expected
    }    
     
    
    try {
      $b = $this->createPropertyBuilder( 'name-' ); 
      $this->getInstance( $b );
      $this->fail( 'value returned by IPropertyBuilder::getName() must match [a-zA-Z0-9_]+' );
    } catch( InvalidArgumentException $e ) {
      //..Expected
    }
    
    
    try {
      $b = $this->createPropertyBuilder( 'name#' );
      $this->getInstance( $b );
      $this->fail( 'value returned by IPropertyBuilder::getName() must match [a-zA-Z0-9_]+' );
    } catch( InvalidArgumentException $e ) {
      //..Expected
    }
    
    
    try {
      $b = $this->createPropertyBuilder( 'name\\' );
      $this->getInstance( $b );
      $this->fail( 'value returned by IPropertyBuilder::getName() must match [a-zA-Z0-9_]+' );
    } catch( InvalidArgumentException $e ) {
      //..Expected
    }
    
    
    //..Duplicated on purpose - checks validation cache 
    try {
      $b = $this->createPropertyBuilder( 'name\\' );
      $this->getInstance( $b );
      $this->fail( 'value returned by IPropertyBuilder::getName() must match [a-zA-Z0-9_]+' );
    } catch( InvalidArgumentException $e ) {
      //..Expected
    }    
    
    
    //..Duplicate on purpose 
    $b = $this->createPropertyBuilder( 'The_Name1' );
    $this->assertSame( 'The_Name1', $this->getInstance( $b )->getName());
        
    $this->expectError();
    $this->getInstance();
  }
  
  
  public function testGetTag() : void
  {
    $this->assertSame( static::tag, $this->getInstance( $this->createPropertyBuilder())->getTag());
  }
  
  
  public function testGetId() : void
  {
    $this->assertSame( static::id, $this->getInstance( $this->createPropertyBuilder())->getId());
  }
  
  
  public function testGetDefaultValue() : void
  {
    $this->assertSame( static::defaultValue, $this->getInstance( $this->createPropertyBuilder())->getDefaultValue());
  }
  
  
  public function testGetName() : void
  {
    $this->assertSame( static::name, $this->getInstance( $this->createPropertyBuilder())->getName());
  }
  
  
  public function testGetType() : void
  {
    $this->assertSame( $this->getPropertyType(), $this->getInstance( $this->createPropertyBuilder())->getType()->value());
  }
  
  
  public function testGetFlags() : void
  {
    $this->assertSame( static::flagTotal, $this->getInstance( $this->createPropertyBuilder())->getFlags()->getTotal());
  }
  
  
  public function testGetPropertyBehavior() : void
  {
    $b1 = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    
    $b = $this->createPropertyBuilder();
    $b->method( 'getBehavior' )->willReturn( [$b1] );
    
    $instance = $this->getInstance( $b );
    $this->assertSame( 1, sizeof( $instance->getPropertyBehavior()));
  }
  
  
  public function testGetCaptionReturnsCaptionOrNameWhenEmpty() : void
  {
    $this->assertSame( static::caption, $this->getInstance( $this->createPropertyBuilder())->getCaption());
    $this->assertSame( static::name, $this->getInstance( $this->createPropertyBuilder( static::name, '' ))->getCaption());
  }
  
  
  public function testGetConfig() : void
  {
    $this->assertSame( static::config, $this->getInstance( $this->createPropertyBuilder())->getConfig());
  }
  
  
  public function testGetPrefix() : void
  {
    $this->assertSame( static::prefix, $this->getInstance( $this->createPropertyBuilder())->getPrefix());
  }          
  
  
  public function testSetReadOnlyThrowsExceptionOnSetValue() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    $instance->setValue( static::value1 );
    $instance->setReadOnly();
    $this->expectException( ValidationException::class );
    $instance->setValue( static::value1 );    
  }
  
  
  public function testSetValueSetsAValueAndIsEdited() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    $this->assertFalse( $instance->isEdited());
    $this->assertSame( static::defaultValue, $instance->getValue());
    $instance->setValue( static::value1 );
    $this->assertSame( static::value1, $instance->getValue());
    $this->assertTrue( $instance->isEdited());
  }
  
  
  public function testIsEmptyReturnTrueBeforeResetOrWhenValueIsEqualToDefaultValue() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $this->assertTrue( $instance->isEmpty());
    $instance->reset();
    $this->assertTrue( $instance->isEmpty());
    $instance->setValue( static::value1 );
    $this->assertFalse( $instance->isEmpty());
  }
  
  
  public function testSetValueThrowsExceptionWhenWriteEmptyFlagIsSetAndPropertyIsNotEmpty() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilderWithFlags( static::name, IPropertyFlags::WRITE_EMPTY ));
    $instance->reset();
    
    $this->assertTrue( $instance->isEmpty());
    $this->assertTrue( $instance->getFlags()->hasVal( IPropertyFlags::WRITE_EMPTY ));
    
    $instance->setValue( static::value1 );
    $this->assertFalse( $instance->isEmpty());
    
    $this->expectException( ValidationException::class );
    $instance->setValue( static::value2 );
  }
  
  
  public function testEditedIsFalseWhenNoInsertFlagIsSet() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilderWithFlags( static::name, IPropertyFlags::NO_INSERT ));
    $instance->reset();
    $this->assertFalse( $instance->isEdited());
    $this->assertTrue( $instance->getFlags()->hasVal( IPropertyFlags::NO_INSERT ));
    $instance->setValue( static::value1 );
    $this->assertFalse( $instance->isEdited());
  }
  
  
  public function testEditedIsFalseWhenNoUpdateFlagIsSet() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilderWithFlags( static::name, IPropertyFlags::NO_UPDATE ));
    $instance->reset();
    $this->assertFalse( $instance->isEdited());
    $this->assertTrue( $instance->getFlags()->hasVal( IPropertyFlags::NO_UPDATE ));
    $instance->setValue( static::value1 );
    $this->assertFalse( $instance->isEdited());
  }

  
  public function testHydrateSetsValueAndEditedIsFalseAndThrowsExceptionWhenEditedIsTrue() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    $this->assertFalse( $instance->isEdited());
    $instance->hydrate( static::value1 );
    $this->assertFalse( $instance->isEdited());
    $this->assertSame( static::value1, $instance->getValue());
    
    $instance->hydrate( static::value1 );
    $this->assertFalse( $instance->isEdited());
    $this->assertSame( static::value1, $instance->getValue());
    
    $instance->setValue( static::value2 );
    $this->assertTrue( $instance->isEdited());
    $this->assertSame( static::value2, $instance->getValue());
    
    $this->expectException( UnexpectedValueException::class );
    $instance->hydrate( static::value1 );
  }
  
  
  /**
   * @return void
   */
  public function testEditedFlagIsFalseAfterReset() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    $this->assertFalse( $instance->isEdited());
  }
  
  
  /**
   * test that reset() then editing some stuff, then calling reset() resets everything and the edit flag
   */
  public function testEditedFlagIsFalseAfterResetThenEditThenReset() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    $this->assertFalse( $instance->isEdited());
    $instance->setValue( static::value1 );
    $this->assertTrue( $instance->isEdited());
    $instance->reset();
    $this->assertFalse( $instance->isEdited());    
  }  
  
  
  /**
   * 
   * @deprecated
   */
  public function testClearEditFlag() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    $this->assertFalse( $instance->isEdited());
    $instance->setValue( static::value1 );
    $this->assertTrue( $instance->isEdited());
    $instance->clearEditFlag();
    $this->assertFalse( $instance->isEdited());        
  }
  
  
  public function testValidate() : void
  {
    //..Without callbacks, the default validate callback returns true (is valid).
    //..Then protected method validatePropertyValue is called.
    
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    
    //..This should be fine
    $instance->validate( static::value1 );
    $this->expectException( ValidationException::class );
    $instance->validate( static::invalidValue );
  }
  

  public function testValidateThrowsExceptionWhenValueIsNullAndUseNulllFlagIsNotSet() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilderWithFlags( self::name, IPropertyFlags::USE_NULL ), true );
    $instance->reset();
    $instance->setValue( null );
    $this->assertNull( $instance->getValue());
    
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    
    try {
      $instance->setValue( null );
      $this->fail( 'When USE_NULL is not set, supplying null to setValue() must throw a ValidationException' );
    } catch( ValidationException $e ) {
      //..Expected
    }
    
    
    $this->expectException( ValidationException::class );
    $instance->validate( null );    
  }  
 
  
  public function setIsRetrievedIsTrueAfterGetValueAndFalseAfterReset() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    $this->assertFalse( $instance->isRetrieved());
    $instance->getValue();
    $this->assertTrue( $instance->isRetrieved());
    $instance->reset();
    $this->assertFalse( $instance->isRetrieved());
    $instance->getValue();
    $this->assertTrue( $instance->isRetrieved());
  }
  
  
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  
  
  /**
   * Creates a property builder using protected class constants 
   * @return type
   */
  protected function createPropertyBuilder( $name = self::name, $caption = self::caption ) 
  {
    $f = $this->getMockBuilder( IPropertyFlags::class )->getMock();
    $f->method( 'getTotal' )->willReturn( static::flagTotal );
    
    $b = $this->createPropertyBuilderBase( $name, $caption );
    $b->method( 'getFlags' )->willReturn( $f );
    
    return $b;
  }
  
  
  protected function createPropertyBuilderWithFlags( $name = self::name, string ...$flags )
  {
    $f = $this->getMockBuilder( IPropertyFlags::class )->getMock();
    
    $f->expects( $this->any())
      ->method( 'hasVal' )
      ->will( $this->returnCallback( fn( $p ) => in_array( $p, $flags )));
    
    
    $b = $this->createPropertyBuilderBase( $name );
    $b->method( 'getFlags' )->willReturn( $f );
    
    return $b;
  }
  
  
  protected function getPropertyBuilderClassName() : string
  {
    return IPropertyBuilder::class;
  }
  
  
  protected function getPropertyType() : string
  {
    return IPropertyType::TSTRING;
  }
  
  
  protected function createPropertyBuilderBase( $name = self::name, $caption = self::caption )
  {
    $b = $this->getMockBuilder( $this->getPropertyBuilderClassName())->getMock();
    
    $t = $this->getMockBuilder( IPropertyType::class )->getMock();
    $t->method( 'value' )->willReturn( $this->getPropertyType());
    $b->method( 'getType' )->willReturn( $t );
    
    $b->method( 'getName' )->willReturn( $name );
    $b->method( 'getDefaultValue' )->willReturn( static::defaultValue );
    $b->method( 'getCaption' )->willReturn( $caption );
    $b->method( 'getId' )->willReturn( static::id );
    $b->method( 'getTag' )->willReturn( static::tag );
    $b->method( 'getConfig' )->willReturn( static::config );
    $b->method( 'getPrefix' )->willReturn( static::prefix );
    
    return $b;
  }
  
}
