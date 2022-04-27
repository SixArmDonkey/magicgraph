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

use buffalokiwi\buffalotools\types\IEnum;
use buffalokiwi\magicgraph\property\IPropertyBehavior;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\property\PropertyBuilder;
use PHPUnit\Framework\TestCase;


class PropertyBuilderTest extends TestCase
{
  private ?PropertyBuilder $instance = null;
  
  public function setUp(): void
  {
    $mockType = $this->getMockBuilder( IPropertyType::class )->getMock();
    $mockType->method( 'value' )->willReturn( IPropertyType::TSTRING );
    
    $this->instance = new PropertyBuilder( $mockType );
  }
  
  
  /**
   * Instantiating with zero arguments raises an ArgumentCountError 
   * @return void
   */
  public function testContructorWithZeroParameters() : void
  {
    //..Expects an ArgumentCountError 
    $this->expectError();
    new PropertyBuilder();
  }
  
  
  /**
   * The constructor accepts several arguments.
   * IPropertyType is the only required argument
   * @return void
   */
  public function testConstructorWithDefaultParameters() : void
  {
    $mockType = $this->getMockBuilder( IPropertyType::class )->getMock();
    $mockType->method( 'value' )->willReturn( IPropertyType::TSTRING );
    
    $c = new PropertyBuilder( $mockType );
    $this->assertInstanceOf( IEnum::class, $c->getType());    
    $this->assertInstanceOf( IPropertyFlags::class, $c->getFlags());
    $this->assertEquals( '', $c->getName());    
    $this->assertNull( $c->getDefaultValue());
    $this->assertEquals( 0, sizeof( $c->getBehavior()));
  }
  
  
  
  /**
   * The constructor accepts all arguments.
   * Getter methods associated with constructor arguments returns the values supplied to the constructor 
   * @return void
   */
  public function testConstructorWithAllParameters() : void
  {
    $mockType = $this->getMockBuilder( IPropertyType::class )->getMock();
    $mockType->method( 'value' )->willReturn( IPropertyType::TSTRING );
    
    $mockFlags = $this->getMockBuilder( IPropertyFlags::class )->getMock();
    $mockFlags->expects( $this->any())->method( 'hasVal' )->with( $this->isType( 'string' ))->willReturn( true );
    
    $mockBehavior = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    
    $c = new PropertyBuilder( $mockType, $mockFlags, 'name', 'default', $mockBehavior );
    $this->assertInstanceOf( IEnum::class, $c->getType());    
    
    //..To ensure that we get the one passed to the constructor 
    $this->assertSame( IPropertyType::TSTRING, $c->getType()->value());
    
    $this->assertInstanceOf( IPropertyFlags::class, $c->getFlags());
    $this->assertTrue( $c->getFlags()->hasVal( 'value' ));
    
    $this->assertEquals( 'name', $c->getName());    
    $this->assertEquals( 'default', $c->getDefaultValue());    
    $this->assertEquals( 1, sizeof( $c->getBehavior()));
  }
  
  
  /**
   * Test id 
   * 1) is unsigned
   * 2) setId and getId 
   * @return void
   */
  public function testGetSetId() : void
  {
    $this->instance->setId( 1 );
    $this->assertSame( 1, $this->instance->getId());
    
    $this->instance->setId( -1 );
    $this->assertSame( -1, $this->instance->getId());
    
    $this->expectError();
    $this->instance->setId( '' );
  }
  
  
  /**
   * Test setTag accepts string
   * Test setTag does not accept null 
   * Test getTag returns value supplied to setTag 
   * @return void
   */
  public function testGetSetTag() : void
  {
    $this->instance->setTag( 'tag' );
    $this->assertSame( 'tag', $this->instance->getTag());
    
    $this->expectError();
    $this->instance->setTag( null );
  }
  
  
  /**
   * Test setCaption accepts string
   * Test setCaption does not accept null 
   * Test getCaption returns value supplied to setCaption 
   * @return void
   */
  public function testGetSetCaption() : void
  {
    $this->instance->setCaption( 'value' );
    $this->assertSame( 'value', $this->instance->getCaption());
    
    $this->expectError();
    $this->instance->setCaption( null );
  }
  

  /**
   * Test setName accepts string
   * Test setName does not accept null 
   * setName() arg#0 must be a non-empty string when trimmed 
   * Test getName returns value supplied to setName 
   * @return void
   */
  public function testGetSetName() : void
  {
    $this->instance->setName( 'value' );
    $this->assertSame( 'value', $this->instance->getName());
    
    try {
      $this->instance->setName( '' );
      $this->fail( 'setName() must be a non-empty string when trimmed.' );
    } catch ( \InvalidArgumentException $e ) {
      //..Expected
    }
    
    
    try {
      $this->instance->setName( '    ' );
      $this->fail( 'setName() must be a non-empty string when trimmed.' );
    } catch ( \InvalidArgumentException $e ) {
      //..Expected
    }
    
    
    $this->expectError();
    $this->instance->setName( null );
  }

  
  /**
   * Test setPrefix accepts string
   * Test setPrefix does not accept null 
   * Test getPrefix returns value supplied to setPrefix 
   * @return void
   */
  public function testGetSetPrefix() : void
  {
    $this->instance->setPrefix( 'value' );
    $this->assertSame( 'value', $this->instance->getPrefix());
    
    $this->expectError();
    $this->instance->setPrefix( null );
  }
  
  
  /**
   * Test:
   * 1) passing a new instance of IPropertyFlags to setFlags causes that same instance to be returned by getFlags()
   * 2) passing null raises an error 
   * @return void
   */
  public function testGetSetFlags() : void
  {
    $mockFlags = $this->getMockBuilder( IPropertyFlags::class )->getMock();
    $mockFlags->expects( $this->any())->method( 'hasVal' )->with( $this->isType( 'string' ))->willReturn( true );
    
    //..Test default 
    $this->assertInstanceOf( IPropertyFlags::class, $this->instance->getFlags());
    try {
      $this->assertFalse( $this->instance->getFlags()->hasVal( 'value' ));
      $this->fail( 'PropertyBuilder::getFlags()->hasVal with an invalid value is expected to return InvalidArgumentException' );
    } catch( InvalidArgumentException $e ) {
      //..Expected
    }
    
    $this->instance->setFlags( $mockFlags );
    $this->assertInstanceOf( IPropertyFlags::class, $this->instance->getFlags());
    $this->assertTrue( $this->instance->getFlags()->hasVal( 'value' ));
    
    $this->expectError();
    $this->setFlags( null );    
  }
  
  
  /**
   * 1) addBehavior correctly adds an additional IPropertyBehavior instance to the internal behavior array 
   * 2) getBehavior() returns the correct count 
   * 3) the supplied instance is returned by getBehavior()
   * 4) supplying null to addBehavior does not alter the count returned by getBehavior 
   * @return void
   */
  public function testAddAndGetBehavior() : void
  {
    $mockBehavior = $this->getMockBuilder( IPropertyBehavior::class )->getMock();
    $mockBehavior->method( 'getValidateCallback' )->willReturn( fn() => true );
    
    $this->assertSame( 0, sizeof( $this->instance->getBehavior()));
    $this->instance->addBehavior( $mockBehavior );
    
    $ba = $this->instance->getBehavior();
    $this->assertSame( 1, sizeof( $ba ));
    $b = reset( $ba );
    $this->assertInstanceOf( IPropertyBehavior::class, $b );
    
    $this->assertTrue( is_callable( $b->getValidateCallback()));
    $this->assertTrue( $b->getValidateCallback()());
    
    $this->expectError();
    $this->addBehavior( null );
  }
  
  
  
  /**
   * Tests getConfig and setConfig
   * 1) Array passed to setConfig() is returned by getConfig()
   * 2) passing anything except for an array to setConfig() raises an error 
   * @return void
   */
  public function testGetSetConfig() : void
  {
    $this->assertSame( 0, sizeof( $this->instance->getConfig()));
    $arr = [true];
    
    $this->instance->setConfig( $arr );
    $this->assertSame( $arr, $this->instance->getConfig());
    
    $this->expectError();
    $this->setConfig( null );
  }
  
  
  /**
   * setDefaultValue() accepts scalar, array, object and null 
   * getDefaultValue() returns the value supplied to setDefault Value 
   * @return void
   */
  public function testGetSetDefaultValue() : void
  {
    $scalarVal = 3.14;
    $objVal = new stdClass();
    $arrVal = [true];
    
    $i = $this->instance;
    
    $i->setDefaultValue( $scalarVal );
    $this->assertSame( $scalarVal, $i->getDefaultValue());
    
    $i->setDefaultValue( $objVal );
    $this->assertSame( $objVal, $i->getDefaultValue());
    
    $i->setDefaultValue( $arrVal );
    $this->assertSame( $arrVal, $i->getDefaultValue());
  }
}
