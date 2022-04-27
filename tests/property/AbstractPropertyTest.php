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
 * @todo Write tests for __clone()
 */
abstract class AbstractPropertyTest extends TestCase
{
  protected const name = 'name';
  protected const defaultValue = 'default';
  protected const caption = 'caption';
  protected const id = 1;
  protected const tag = 'tag';
  protected const config = [true];
  protected const prefix = 'prefix';
  protected const flagTotal = 12345;
    
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
    $this->assertSame( self::tag, $this->getInstance( $this->createPropertyBuilder())->getTag());
  }
  
  
  public function testGetId() : void
  {
    $this->assertSame( self::id, $this->getInstance( $this->createPropertyBuilder())->getId());
  }
  
  
  public function testGetDefaultValue() : void
  {
    $this->assertSame( self::defaultValue, $this->getInstance( $this->createPropertyBuilder())->getDefaultValue());
  }
  
  
  public function testGetName() : void
  {
    $this->assertSame( self::name, $this->getInstance( $this->createPropertyBuilder())->getName());
  }
  
  
  public function testGetType() : void
  {
    $this->assertSame( IPropertyType::TSTRING, $this->getInstance( $this->createPropertyBuilder())->getType()->value());
  }
  
  
  public function testGetFlags() : void
  {
    $this->assertSame( self::flagTotal, $this->getInstance( $this->createPropertyBuilder())->getFlags()->getTotal());
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
    $this->assertSame( self::caption, $this->getInstance( $this->createPropertyBuilder())->getCaption());
    $this->assertSame( self::name, $this->getInstance( $this->createPropertyBuilder( self::name, '' ))->getCaption());
  }
  
  
  public function testGetConfig() : void
  {
    $this->assertSame( self::config, $this->getInstance( $this->createPropertyBuilder())->getConfig());
  }
  
  
  public function testGetPrefix() : void
  {
    $this->assertSame( self::prefix, $this->getInstance( $this->createPropertyBuilder())->getPrefix());
  }          
  
  
  public function testSetReadOnlyThrowsExceptionOnSetValue() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    $instance->setValue( 'test' );
    $instance->setReadOnly();
    $this->expectException( ValidationException::class );
    $instance->setValue( 'test' );    
  }
  
  
  public function testSetValueSetsAValueAndIsEdited() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilder());
    $instance->reset();
    $this->assertFalse( $instance->isEdited());
    $this->assertSame( self::defaultValue, $instance->getValue());
    $instance->setValue( 'test' );
    $this->assertSame( 'test', $instance->getValue());
    $this->assertTrue( $instance->isEdited());
  }
  
  
  public function testEditedIsFalseWhenNoInsertFlagIsSet() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilderWithFlags( self::name, IPropertyFlags::NO_INSERT ));
    $instance->reset();
    $this->assertFalse( $instance->isEdited());
    $this->assertTrue( $instance->getFlags()->hasVal( IPropertyFlags::NO_INSERT ));
    $instance->setValue( 'test' );
    $this->assertFalse( $instance->isEdited());
  }
  
  
  public function testEditedIsFalseWhenNoUpdateFlagIsSet() : void
  {
    $instance = $this->getInstance( $this->createPropertyBuilderWithFlags( self::name, IPropertyFlags::NO_UPDATE ));
    $instance->reset();
    $this->assertFalse( $instance->isEdited());
    $this->assertTrue( $instance->getFlags()->hasVal( IPropertyFlags::NO_UPDATE ));
    $instance->setValue( 'test' );
    $this->assertFalse( $instance->isEdited());
  }

  
  public function testHydrateSetsValueAndEditedIsFalse() : void
  {
    
  }
  
  
  public function testHydrateThrowsUnexpectedValueExceptionWhenEditedIsTrue() : void
  {
    
  }
  
  
  
  
  
  
  
  /**
   * Creates a property builder using protected class constants 
   * @return type
   */
  protected function createPropertyBuilder( $name = self::name, $caption = self::caption ) 
  {
    $f = $this->getMockBuilder( IPropertyFlags::class )->getMock();
    $f->method( 'getTotal' )->willReturn( self::flagTotal );
    
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
  
  
  protected function createPropertyBuilderBase( $name = self::name, $caption = self::caption )
  {
    $t = $this->getMockBuilder( IPropertyType::class )->getMock();
    $t->method( 'value' )->willReturn( IPropertyType::TSTRING );
    
    $b = $this->getMockBuilder( IPropertyBuilder::class )->getMock();
    $b->method( 'getType' )->willReturn( $t );
    $b->method( 'getName' )->willReturn( $name );
    $b->method( 'getDefaultValue' )->willReturn( self::defaultValue );
    $b->method( 'getCaption' )->willReturn( $caption );
    $b->method( 'getId' )->willReturn( self::id );
    $b->method( 'getTag' )->willReturn( self::tag );
    $b->method( 'getConfig' )->willReturn( self::config );
    $b->method( 'getPrefix' )->willReturn( self::prefix );
    
    return $b;
  }
}
