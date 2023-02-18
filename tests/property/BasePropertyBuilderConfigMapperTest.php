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

use buffalokiwi\magicgraph\property\BasePropertyBuilderConfigMapper;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyBuilder;
use buffalokiwi\magicgraph\property\IPropertyBuilderFactory;
use buffalokiwi\magicgraph\property\IPropertyFactory;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\property\IStringPropertyBuilder;
use PHPUnit\Framework\TestCase;


class BasePropertyBuilderConfigMapperTest extends TestCase
{ 
  const CONFIG_KEYS = [
    'CAPTION' => 'caption',
    'ID' => 'id',
    'VALUE' => 'value',
    'SETTER' => 'setter',
    'GETTER' => 'getter',
    'MSETTER' => 'msetter',
    'MGETTER' => 'mgetter',
    'TOARRAY' => 'toarray',
    'TYPE' => 'type',
    'FLAGS' => 'flags',
    'CLAZZ' => 'clazz',
    'INIT' => 'init',
    'MIN' => 'min',
    'MAX' => 'max',
    'VALIDATE' => 'validate',
    'PATTERN' => 'pattern',
    'CONFIG' => 'config',
    'PREFIX' => 'prefix',
    'CHANGE' => 'change',
    'HTMLINPUT' => 'htmlinput',
    'IS_EMPTY' => 'isempty',
    'TAG' => 'tag'
  ];
  
  
  const CONFIG_TEST = [
    'stringprop' => [
      'type' => 'string'
    ],
      
    'intprop' => [
      'type' => 'int'
    ]
  ];
  
  
  private $builderInstance = null;
  private $propFactoryInstance = null;
  private $instance = null;
  
  public function setUp() : void
  {
    $this->builderInstance = $this->getMockBuilder( IPropertyBuilderFactory::class )->getMock();
    $this->builderInstance->method( 'getTypes' )->willReturn( array_values( self::CONFIG_KEYS ));
    
    $this->propFactoryInstance = $this->getMockBuilder( IPropertyFactory::class )->getMock();
    $this->propFactoryInstance->method( 'getTypes' )->willReturn( array_values( self::CONFIG_KEYS ));
    
    $propMock = $this->getMockBuilder( IProperty::class )->getMock();
    
    $this->propFactoryInstance->expects( $this->any())->method( 'createProperty' )->willReturn( $propMock );
    
    $this->instance = new BasePropertyBuilderConfigMapper( $this->builderInstance, $this->propFactoryInstance );
  }
  
  
  public function testBuilderAndPropertyFactoriesMustHaveMatchingPropertyTypeLists() : void
  {
    try {
      new BasePropertyBuilderConfigMapper( $this->builderInstance, $this->propFactoryInstance );
      //..Expected
    } catch( Exception $e ) {
      //..worst message ever
      $this->fail( 'Builder and property factory instances must return matching getType() lists' );
    }
    
    $this->propFactoryInstance = $this->getMockBuilder( IPropertyFactory::class )->getMock();
    $this->propFactoryInstance->method( 'getTypes' )->willReturn( [] );
    
    $this->expectException( InvalidArgumentException::class );
    new BasePropertyBuilderConfigMapper( $this->builderInstance, $this->propFactoryInstance );   
  }
  
  
  public function testMapThrowsExceptionWhenConfigArrayValueIsNotArray() : void
  {
    $config = self::CONFIG_TEST;
    $config['stringprop'] = 'not an array';
    $this->expectException( InvalidArgumentException::class );
    
    $this->instance->map( $config );
  }
  
  
  public function testMapThrowsExceptionWhenTypeKeyIsNotSet() : void
  {
    //..Type is the data type and determines which concrete property buildler and property are eventually instantiated
    $config = self::CONFIG_TEST;
    unset( $config['stringprop']['type'] );
    $this->expectException( InvalidArgumentException::class );
    
    $this->instance->map( $config );
  }
  
  
  public function testMapCreatesAndPassesNewBuilderToPropertyFactoryAndBuildlerSetNameIsInvoked() : void
  {
    $config = self::CONFIG_TEST;
    unset( $config['intprop'] );
    
    //..Mocking IPropertyBuilder::create to test BasePropertyBuilderConfigMapper::createBuilder
    $strBuilderMock = $this->getMockBuilder( IStringPropertyBuilder::class )->getMock();
    
    //..Builder wants create called once with a string property configuration array 
    $this->builderInstance
      ->expects( $this->once())
      ->method( 'create' )
      ->withConsecutive( ['stringprop', 'string'] )
      ->willReturnOnConsecutiveCalls( $strBuilderMock );

    //..Test that setName is called
    $strBuilderMock->expects( $this->once())
      ->method( 'setName' )
      ->with( 'stringprop' );
    
    //..Ensure that a builder of an appropriate type is passed to the property factory 
    $this->propFactoryInstance = $this->getMockBuilder( IPropertyFactory::class )->getMock();
    $this->propFactoryInstance->method( 'getTypes' )->willReturn( array_values( self::CONFIG_KEYS ));
    
    //..string property returned from property factory 
    $propMock = $this->getMockBuilder( IProperty::class )->getMock();
    
    //..Ensure that the property factory create event is invoked and the above builder is passed 
    $this->propFactoryInstance->expects( $this->once())
      ->method( 'createProperty' )
      ->with( $strBuilderMock )
      ->willReturn( $propMock );
    
    (new BasePropertyBuilderConfigMapper( $this->builderInstance, $this->propFactoryInstance ))->map( $config );
  }


  /**
   * Test that all behavior callbacks available to the config array are closures, passed to some IPropertyBehavior, 
   * and then passed as arg #0 to IPropertyBuilder::addBehavior.
   * 
   * This should be fun.
   * 
   * @return void
   */
  public function testMapCreateBuilderProperlyCreatesAndAddsPropertyBehaviorFromConfigArray() : void
  {   
    $config = self::CONFIG_TEST;
    unset( $config['intprop'] );
    
    $r =& $config['stringprop'];
    
    //..This is a valid array
    $r['getter'] = function() {};
    $r['setter'] = function() {};
    $r['mgetter'] = function() {};
    $r['msetter'] = function() {};
    $r['toarray'] = function() {};
    $r['init'] = function() {};
    $r['validate'] = function() {};
    $r['change'] = function() {};
    $r['htmlinput'] = function() {};
    $r['isempty'] = function() {};
    
    //..Expect nothing to happen
    (new BasePropertyBuilderConfigMapper( $this->builderInstance, $this->propFactoryInstance ))->map( $config );       
    
    //..Validate that each type of callback throws an exception when not a closure 
    foreach( $r as $propName => &$callback )
    {
      if ( $propName == 'type' )
        continue;
      
      try {
        $callback = 1;
        (new BasePropertyBuilderConfigMapper( $this->builderInstance, $this->propFactoryInstance ))->map( $config );       
        $this->fail( 'An exception must be thrown when Behavior callback array entry: "' . $propName . '" value is not a \Closure or null' );
      } catch (Exception $ex) {
        //..Expected
        $callback = function() {};
      }
    }
    
    //..Set all callbacks to null.  This must not throw exceptions 
    foreach( $r as $propName => &$callback )
    {
      if ( $propName == 'type' )
        continue;
      $callback = null;
    }
    
    //..Expect nothing to happen
    (new BasePropertyBuilderConfigMapper( $this->builderInstance, $this->propFactoryInstance ))->map( $config );
  }
  
  
  /**
   * Test that any array keys NOT associated with a behavior callback are passed to 
   * BasePropertyBuidlerConfigMapper::setProperty()
   * 
   * setProperty includes a switch listing each supported config key.  Each of the corresponding builder methods
   * must be called exactly once, and any unsupported properties must be passed to 
   * BasePropertyBuidlerConfigMapper::setCustomProperty
   * 
   * The second condition should probably be a separate test since this will need to subclass 
   * BasePropertyBuidlerConfigMapper to override setCustomProperty()
   * 
   * @return void
   */
  public function testMapCreateBuilderCallsSetPropertyForAllNonBehaviorConfigArrayKeys() : void
  {
    $instance = new class( $this->builderInstance, $this->propFactoryInstance ) extends BasePropertyBuilderConfigMapper {
      public $customProps = [];
      
      protected function setCustomProperty( IPropertyBuilder $b, string $name, string $k, $v ) {
        $this->customProps[] = func_get_args();
      }
    };
    
    $testConfig = [
      'test' => [
        'type' => IPropertyType::TSTRING,
        'custom1' => 'foo',
        'custom2' => 'bar'
      ]
    ];
    
    $instance->map( $testConfig );
    
    $this->assertCount( 2, $instance->customProps );
    
    $c1 = reset( $instance->customProps );
    $c2 = end( $instance->customProps );
    
    $this->assertIsArray( $c1 );
    $this->assertIsArray( $c2 );
    
    $this->assertCount( 4, $c1 );
    $this->assertCount( 4, $c2 );
    
    $this->assertInstanceOf( IPropertyBuilder::class, $c1[0] );
    $this->assertInstanceOf( IPropertyBuilder::class, $c2[0] );
    
    $this->assertSame( 'test', $c1[1] );
    $this->assertSame( 'test', $c2[1] );
    
    $this->assertSame( 'custom1', $c1[2] );
    $this->assertSame( 'custom2', $c2[2] );
    
    $this->assertSame( 'foo', $c1[3] );
    $this->assertSame( 'bar', $c2[3] );
    
  }
  
    
  /**
   * This just calls map().  I'm feeling lazy so no tests for you.
   * @return void
   * @todo Write this test
   */
  public function testCreateProperty() : void
  {
    $this->expectNotToPerformAssertions();
  }
}
