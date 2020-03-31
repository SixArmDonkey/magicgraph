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

use buffalokiwi\magicgraph\property\DefaultConfigMapper;
use buffalokiwi\magicgraph\property\DefaultPropertySet;
use buffalokiwi\magicgraph\property\EPropertyType;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertyFactory;
use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\property\PropertyFactory;
use buffalokiwi\magicgraph\property\QProperties;
use buffalokiwi\magicgraph\property\SPropertyFlags;
use buffalokiwi\buffalotools\types\ISet;
use PHPUnit\Framework\TestCase;


class InvalidPropertyFactory implements IPropertyFactory
{
  /**
   * Retrieve the config object used for this factory.
   * Cast the result to whatever input type 
   * @return array IPropertyConfig[] config 
   */
  public function getPropertyConfig() : array
  {
    return [IPropertyConfig::class];
  }
  
  
  /**
   * Retrieve a list of properties 
   * @return IProperty[] properties
   */
  public function getProperties( IPropertyConfig ...$config ) : array
  {
    return ['invalid', 'notIProperty'];
  }
}


/**
 * Tests the DefaultPropertySet class 
 * This is partially an integration test as DefaultPropertySet properties 
 * can't really be mocked.
 */
class DefaultPropertySetTest extends TestCase
{
  /**
   * Property set instance 
   * @var IPropertySet 
   */
  private $instance;
  
  /**
   * Test property config array 
   * @var array
   */
  private $props;
  
  
  public function setUp() : void
  {
    $this->props = [
      'id' => [
        'type' => 'int',
        'flags' => ['required','primary','noinsert'],
        'value' => 0
      ],
        
      'id2' => [
        'type' => 'int',
        'flags' => ['required','primary','noinsert'],
        'value' => 0
      ],        
        
      'name' => [
        'id' => 1,  //..OPtional unique id for this property 
        'type' => 'string',
        'flags' => ['required'],
        'value' => 'defaultname'
      ],
        
      'caption' => [
        'type' => 'string',
        'flags' => [],
        'value' => 'defaultcaption',
      ],
        
      'rtenumtest' => [
        'type' => 'rtenum',
        'flags' => [],
        'config' => [
          'key1' => 'value1',
          'key2' => 'value2'
        ],
        'value' => 'key1'
      ],
        
      'arraytest' => [
        'type' => 'array',
        'flags' => [],
        'value' => [],
      ]
    ];
    
    $this->instance = $this->createIPropertySet( $this->props );
  }
  
  
  protected function createIPropertySet( array $props ) : IPropertySet
  {
    return new DefaultPropertySet( new PropertyFactory( new DefaultConfigMapper()), new QProperties( $props ));
  }
  
  
  /**
   * Tests the DefaultPropertySet constructor.
   * This expects:
   * 1) The members of the set must match all property names in the config 
   * 2) The set must have a value of zero (zero bits set)
   * 3) Supplying a factory that returns something other than IProperty instances MUST throw an \InvalidArgumentException 
   * 
   * This tests with PropertyFactory and QuickProperties 
   * @return void
   * @see ISet
   */
  public function testContructor() : void
  {
    $c = $this->createIPropertySet( $this->props );
    $names = array_keys( $this->props );
    $this->assertEquals( sizeof( $names ), sizeof( $c->getMembers()));
    
    foreach( $c->getMembers() as $m )
    {
      $this->assertTrue( in_array( $m, $names ));
    }
    
    $this->assertEmpty( $c->getActiveMembers());
    
    $this->expectException( InvalidArgumentException::class );
    $c = new DefaultPropertySet( new InvalidPropertyFactory());
  }
  
  
  /**
   * Tests that the unique id property for some property works.
   * As per the config in this file, the name property is expected to have an id of 1, and
   * all other properties have an id of zero.
   * @return void
   */
  public function testUniqueId() : void
  {
    $prop = $this->instance->getProperty( 'name' );
    $this->assertInstanceOf( IProperty::class, $prop );
    $this->assertEquals( 1, $prop->getId());
    
    $prop = $this->instance->getProperty( 'id' );
    $this->assertInstanceOf( IProperty::class, $prop );
    $this->assertEquals( 0, $prop->getId());
  }
  
  
  /**
   * Test the DefaultPropertySet::__clone() magic method.
   * 
   * This expects the clone operator to clone all of the internal IProperty
   * instances.
   * @return void
   */
  public function testMagicClone() : void
  {
    $props1 = $this->instance->getProperties();
    $set2 = clone $this->instance;
    $props2 = $set2->getProperties();
    
    foreach( $props1 as $prop )
    {
      $this->assertInstanceOf( IProperty::class, $prop );
      foreach( $props2 as $prop2 )
      {
        $this->assertInstanceOf( IProperty::class, $prop2 );
        $this->assertFalse( $prop === $prop2 );
      }
    }    
  }
  
  
  /**
   * Test IPropertySet::getProperty()
   * When supplied with a property name that is a member of the property set,
   * this will return an IProperty instance.
   * If an invalid property name is supplied, this throws an InvalidArgumentException.
   * @return void
   */  
  public function testGetProperty() : void
  {
    $prop = $this->instance->getProperty( 'id' );
    $this->assertInstanceOf( IProperty::class, $prop );
    
    $this->expectException( InvalidArgumentException::class );
    $this->instance->getProperty( 'Invalid' );
  }
  
  
  /**
   * Test IPropertySet::getPrimaryKey()
   * Expects a single IProperty instance with the PRIMARY flag set on 
   * the IPropertyFlags instance.
   * If no primary key is defined, an Exception is expected 
   * @return void
   */
  public function testGetPrimaryKey() : void
  {
    //..Test with primary key set (There are 2 in the main set)
    $pri = $this->instance->getPrimaryKey();
    $this->assertInstanceOf( IProperty::class, $pri );
    
    //..Ensure that the first encountered primary key is returned as the primary key 
    $this->assertEquals( 'id', $pri->getName());
    
    
    //..Test without a primary key set 
    $c = $this->createIPropertySet([
      'id' => [
        'type' => 'int',
        'flags' => [],
        'value' => 0
    ]]);
    
    //..It'll throw an exception 
    $this->expectException( Exception::class );
    $c->getPrimaryKey();
  }
  
  
  /**
   * Test IPropertySet::getPrimaryKeys()
   * If more than one IProperty instance in the set has the IPropertyFlags::PRIMARY flag
   * set, this will return all of corresponding IProperty instances.
   * @return void
   */
  public function testGetPrimaryKeys() : void
  {
    $keys = $this->instance->getPrimaryKeys();
    $this->assertIsArray( $keys );
    $this->assertEquals( 2, sizeof( $keys ));
    
    /* @var $key1 IProperty */
    $key1 = reset( $keys );
    /* @var $key2 IProperty */
    $key2 = end( $keys );
    
    //..Ensure the correct order 
    $this->assertEquals( 'id', $key1->getName());
    $this->assertEquals( 'id2', $key2->getName());
    
    
    //..Test without a primary key set 
    $c = $this->createIPropertySet([
      'id' => [
        'type' => 'int',
        'flags' => [],
        'value' => 0
    ]]);
    
    $keys = $c->getPrimaryKeys();
    $this->assertIsArray( $keys );
    $this->assertEmpty( $keys );
  }
  
  
  /**
   * Test IPropertySet::getProperties()
   * Expects all IProperty instances to be returned that are contained in the 
   * set 
   * @return void
   */
  public function testGetProperties() : void 
  {
    $props = $this->instance->getProperties();
    $this->assertIsArray( $props );
    $this->assertEquals( sizeof( $this->props ), sizeof( $props ));
    foreach( $props as $prop )
    {
      $this->assertInstanceOf( IProperty::class, $prop );
    }
  }
  
  
  /**
   * Test IPropertySet::findProperty()
   * Expects a list of IProperty instances to be returned that have 
   * any of the supplied IPropertyFlags bits enabled.
   * @return void
   */
  public function testFindProperty() : void 
  {
    $props = $this->instance->findProperty( new SPropertyFlags( SPropertyFlags::REQUIRED ));
    $this->assertIsArray( $props );
    $this->assertEquals( 3, sizeof( $props ));
  }
  
  
  /**
   * Test IPropertySet::getPropertiesByType()
   * Expects a list of IProperty instances to be returned that have 
   * the same type as the supplied argument.
   * @return void
   */
  public function testGetPropertiesByType() : void
  {
    $props = $this->instance->getPropertiesByType( new EPropertyType( EPropertyType::TINTEGER ));
    $this->assertIsArray( $props );
    $this->assertEquals( 2, sizeof( $props ));
    foreach( $props as $prop )
    {
      $this->assertInstanceOf( IProperty::class, $prop );
    }
  }
}
