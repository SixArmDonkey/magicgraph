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

use buffalokiwi\magicgraph\DefaultModel;
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\property\QuickPropertySet;
use buffalokiwi\magicgraph\QuickModel;
use buffalokiwi\magicgraph\ValidationException;
use buffalokiwi\buffalotools\types\IBigSet;
use buffalokiwi\buffalotools\types\IEnum;
use buffalokiwi\buffalotools\types\ISet;
use PHPUnit\Framework\TestCase;

require_once( __DIR__ . '/SampleEnum.php' );
require_once( __DIR__ . '/SampleSet.php' );


/**
 * Tests the DefaultModel class.
 * 
 * Due to how tightly coupled the IPropertySet instance is to the internal 
 * workings of IModel, this is more of an integration test than a unit test.
 * This uses buffalokiwi\magicgraph\QuickPropertySet as the property set instance for 
 * testing.
 */
class DefaultModelTest extends TestCase
{
  /**
   * Test model instance 
   * @var IModel
   */
  private $instance;
  
  /**
   * Properties config used for the model 
   * @var array 
   */
  private $props;
  
  
  /**
   * Creates a DefaultModel instance used for testing.
   * Properties are contained in $this->props.
   * @return IModel
   */
  protected function createIModelInstance() : IModel
  {
    $this->props = [
      'id' => [
        'type' => 'int',
        'flags' => ['required','primary','noinsert'], //..constants from SPropertyFlags 
        'value' => 0
      ],
      'name' => [
        'type' => 'string',
        'flags' => ['required'],
        'value' => 'defaultname'
      ],
        
      'caption' => [
        'type' => 'string',
        'flags' => [],
        'value' => 'defaultcaption',
        'getter' => function( IProperty $prop, $value ) {
          return $value . 'modified';
        }
      ],
              
      'testprepare' => [
        'type' => 'string',
        'flags' => [],
        'value' => '',
        'setter' => function( IProperty $prop, $value ) {
          return $value . 'prepared';
        }
      ],
              
              
      'testpreparevalidate' => [
        'type' => 'string',
        'flags' => [],
        'value' => '',
        'setter' => function( IProperty $prop, $value ) {
          return 'setter';
        }
      ],
              
      'validate' => [
        'type' => 'string',
        'flags' => [],  
        'value' => '',
        'validate' => function( IProperty $prop, $value ) {
          //..Validation can fail when setting the default value.
          //  Prevent this by returning true for an empty string.
          if ( empty( $value ))
            return true;
          
          //..return false to have a ValidationException thrown.
          return false;
        }
      ],
              
      'testenum' => [
        'type' => 'enum',
        'clazz' => SampleEnum::class,
        'value' => SampleEnum::KEY1
      ],
              
      'testset' => [
        'type' => 'set',
        'clazz' => SampleSet::class,
        'value' => ''
      ]
    ];
    
    return new DefaultModel( new QuickPropertySet( $this->props ));
  }
  
  
  public function setUp() : void
  {
    $this->instance = $this->createIModelInstance();
  }
  
  
  /**
   * Tests the __isset() magic method.
   * This simply calls IPropertySet::isMember()
   */
  public function testMagicIsset() : void
  {
    $this->assertTrue( isset( $this->instance->name ));
    $this->assertFalse( isset( $this->instance->invalid ));
  }
  
  
  /**
   * Tests the __get() and __set() magic methods.
   * This calls IModel::getValue()
   */
  public function testMagicGetSet() : void
  {
    $this->instance->name = 'testname';
    $this->assertEquals( 'testname', $this->instance->name );
  }
  
  
  
  /**
   * Tests the IModel::getValue() method.
   * Expects an InvalidArgumentException if the supplied property name is not a member of the supplied IPropertySet 
   * If the IProperty::getGetterCallback() result is an instance of \Closure, the result of that Closure is expected to 
   * be returned. Otherwise the stored value for the property is expected.
   */
  public function testGetValue() : void
  {
    $this->instance->name = 'testname';
    $this->instance->caption = 'testcaption';
    $this->assertEquals( 'testname', $this->instance->getValue( 'name' ));
    $this->assertEquals( 'testcaptionmodified', $this->instance->getValue( 'caption' ));
  }
  
  
  /**
   * Test that retrieving a property value for an invalid property name 
   * throws an exception 
   */
  public function testGetValueThrowsInvalidArgumentException()
  {
    $this->expectException( InvalidArgumentException::class );
    $this->instance->getValue( 'invalid' );
  }
  
  
  
  /**
   * Test that setting a value of some non-existing property throws an exception
   * @return void
   */
  public function testInvalidSetValueSilentlyFails() : void
  {
    //..setValue returns void, so this should keep phpunit happy while still testing for exceptions to NOT be thrown.
    $this->assertNull( $this->instance->setValue( 'invalid', 'invalidfoo' ));
  }
  
  
  /**
   * Test that setting an invalid value for the "validate" test property 
   * If validation fails from a property closure, an error is triggered and false is returned by the validate function.
   * The validate property has a behavior callback that will purposely 
   * fail for any non-empty value.
   * @return void
   */
  public function testSetValueValidationException() : void
  {
    //..Proves validate() is called on the property as the validate callback must be called 
    $this->expectError();
    $this->instance->setValue( 'validate', 'test' );
    
    PHPUnit_Framework_Error_Warning::$enabled = false;
    $this->expectException( ValidationExeption::class );
    $this->instance->setValue( 'validate', 'test' );
    PHPUnit_Framework_Error_Warning::$enabled = true;
  }
  
  
  /**
   * Test to ensure the setter behavior callback can modify the value
   * prior to validation being called.
   * The setter behavior callback on the "testpreparevalidate" test property
   * will convert any value to a \stdClass, which will cause a ValidationException
   * to be thrown.
   * @return void
   */
  public function testSetterBehaviorCallback() : void
  {
    //..Test that prepare is called before validate.
    //..The setter callback sets every value to "setter"
    $this->instance->setValue( 'testpreparevalidate', 'test' );
    $this->assertEquals( 'setter', $this->instance->getValue( 'testpreparevalidate' ));
  }
  
  
  /**
   * Test to ensure that setValue throws an InvalidArgumentException if the
   * passed value is an array
   * @return void
   */
  public function testSetValueAsArrayException() : void
  {
    //..Test storing an invalid array in a string property
    $this->expectException( ValidationException::class );
    $this->instance->setValue( 'name', ['name'] );
  }
  
  
  /**
   * Tests passing an object to setValue for a property that does not accept
   * object values.
   * @return void
   */
  public function testSetValueAsObjectException() : void
  {
    //..Test storing an invalid object in a string property
    $this->expectException( ValidationException::class );
    $this->instance->setValue( 'name', new stdClass());
  }
    
  
  
  
  /**
   * Tests the IModel::setValue() method.
   * Expects an InvalidArgumentException if the supplied property name is not a member of the supplied IPropertySet
   * if IProperty::getSetterCallback() returns a valid \Closure the result of that Closure is expected to be used as the value prior to validate being called 
   * IProperty::validate() is expected to be called 
   * If the value is an IEnum instance, it is expected to call IEnum::setValue() for the stored enum 
   * If the value is an ISet instance, it is expected to call the ISet::clear(), then ISet::add() methods for the stored set  
   * If the value is an object, it MUST match IProperty::getClass()
   * If the value is an object or array and the setter callback and IProperty::getClass() are undefined, then an InvalidArgumentException is expected to be thrown 
   * The value is expected to be stored in the model 
   */
  public function getSetValue() : void
  {
    //..Test invalid member 
      
    
    //..Test IProperty::getPrepare()
    $this->instance->setValue( 'testprepare', 'test' );
    $this->assertEquals( 'testpreparemodified', $this->instance->getValue( 'testprepare' ));
    
      
    
      
    
    //..Test IEnum values 
    $this->instance->setValue( 'testenum', 'value1' );
    $this->assertInstanceOf( IEnum::class, $this->instance->getValue( 'testenum' ));
    $this->assertEquals( 'value1', $this->instance->getValue( 'testenum' )->value());
    
    //..Test ISet values 
    $this->instance->setValue( 'testset', 'value2' );
    $this->assertInstanceOf( ISet::class, $this->instance->getValue( 'testset' ));
    $this->assertEquals( 'value2', $this->instance->getValue( 'testset' )->__toString());
    
      
    
    
  }
  
  
  /**
   * Tests IModel::getModifiedProperties()
   * Expected to return a cloned instance of the supplied IPropertySet instance with
   * enabled bits matching the enabled bits of the internal edited bitset.
   * Edited bits are expected to be enabled by calling setValue()
   */
  public function testGetModifiedProperties() : void
  {
    $m = $this->createIModelInstance();
    $m->setValue( 'name', 'value' );    
    $res = $m->getModifiedProperties();
    
    $this->assertInstanceOf( IBigSet::class, $res );
    $memberStrings = $res->getActiveMembers();
    $this->assertIsArray( $memberStrings );
    $this->assertTrue( in_array( 'name', $memberStrings ));
  }
  
  
  /**
   * Tests IModel::getPropertySet()
   * Expects the IPropertySet instance supplied to the constructor to be returned 
   */
  public function testGetPropertySet() : void
  {
    $this->assertInstanceOf( IPropertySet::class, $this->instance->getPropertySet());
  }
  
  
  /**
   * Tests IModel::toObject() 
   * Using the active bits in the IPropertySet instance supplied to the constructor, 
   * this expects a valid JSON object to be returned.
   * The JSON object must be an object with properties matching the property
   * names of the IPropertySet instance, and the values matching the stored
   * values in the IModel instance for those properties.
   * 
   * {"property":"value"}
   * 
   */
  public function testToJSON() : void
  {
    $m = $this->createIModelInstance();
    $m->setValue( 'name', 'value' );
    $props = $m->getPropertySet();
    $this->assertInstanceOf( IPropertySet::class, $props );
    
    $props->clear();
    $props->add( 'name' );
    $json = $m->toObject( $props );
    
    
    $this->assertIsObject( $json );
    $this->assertTrue( isset( $json->name ));
    $this->assertEquals( 'value', $json->name );
    
    $json = json_encode( $m );
    $this->assertIsString( $json );
    $this->assertEquals( "{\"name\":\"value\"}", $json );
  }
  
  
  /**
   * Tests IModel::toArray() 
   * Using the active bits in the IPropertySet instance supplied to the constructor, 
   * this expects an array to be returned containing key value pairs.
   * The array must have keys matching the property
   * names of the IPropertySet instance, and the values matching the stored
   * values in the IModel instance for those properties.
   * 
   * ["property" => "value"]
   * 
   */
  public function testToArray() : void
  {
    $m = $this->createIModelInstance();
    $m->setValue( 'name', 'value' );
    $props = $m->getPropertySet();
    $this->assertInstanceOf( IPropertySet::class, $props );
    
    $props->clear();
    $props->add( 'name' );
    $arr = $m->toArray( $props );
    
    $this->assertIsArray( $arr );
    $this->assertTrue( isset( $arr['name'] ));
    $this->assertEquals( 'value', $arr['name'] );
    $this->assertEquals( 1, sizeof( $arr ));
  }
  
  
  /**
   * Tests IModel::equals()
   * Let A and B being instances of IModel, and test the following:
   * 
   * get_class( A ) == get_class( B )
   * get_class( A::getPropertySet()) == get_class( B::getPropertySet())
   * A::hash() == B::hash()
   * 
   * Expects the class name of A to match the class name of B
   * Expects the IPropertySet instance passed to A to be the same class as The IPropertySet passed to B
   * Expects the result of the hash methods to match.
   */
  public function testEquals() : void
  {
    $this->assertTrue( $this->instance->equals( $this->createIModelInstance()));
    $this->assertFalse( $this->instance->equals( new QuickModel( ['name' => ['type' => 'string', 'value' => '']] )));
  }
  
  
  /**
   * Tests IModel::validate()
   * Expects IProperty::validate() to be called for each property listed within the 
   * IPropertySet instance supplied to the DefaultModel constructor
   */
  public function testValidate() : void
  {
    //..Set to some value other than an empty string, zero or false to test validation.
    //..In the test scenario, zero, false or an empty string is a valid value for this property.
    //..Not sure how to expand on this one
    $this->expectError();
    $this->instance->setValue( 'validate', '1' );
    $this->instance->validate();    
  }
  
  
  /**
   * Test IModel::hash()
   * Expects hash() to return an md5 hash of:
   * 
   * The Model's class name + The IPropertySet class name + a concatenated list of all included property names 
   */
  public function testHash() : void
  {
    $props = $this->instance->getPropertySet();
    $this->assertInstanceOf( IPropertySet::class, $props );
    $this->assertEquals( md5( get_class( $this->instance ) . get_class( $props ) . implode( '', $props->getMembers())), $this->instance->hash());
  }
  
  
  /**
   * Tests IModel::getInsertProperties()
   * Expects a clone of the supplied IPropertySet to be returned with the 
   * bits enabled that match any properties that DO NOT have the INSERT flag enabled.
   */
  public function testGetInsertProperties() : void
  {
    $props = $this->instance->getInsertProperties();
    $this->assertInstanceOf( IBigSet::class, $props );
    $this->assertEquals( 1, sizeof( $this->props ) - sizeof( $props->getActiveMembers())); //..There is only 1 noinsert property defined.
    $this->assertFalse( $props->hasVal( 'id' ));
    $this->assertTrue( $props->hasVal( 'name' ));
  }
}
