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
use buffalokiwi\magicgraph\property\IPropertyBuilder;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\property\PropertyTypeFactoryFactory;
use PHPUnit\Framework\TestCase;


class PropertyTypeFactoryFactoryTest extends TestCase
{
  const intValue = 1;
  const strValue = 'foo';
  const propertyTypeArray = [
    IPropertyType::TINTEGER,
    IPropertyType::TSTRING
  ];
  
  private $propertyTypeMock = null;
  private $intPropertyMock = null;
  private $strPropertyMock = null;
  private $factories = [];
  private $instance = null;
  
  
  public function setUp() : void
  {
    $this->propertyTypeMock = $this->createPropertyTypeMock( self::propertyTypeArray );
    $this->factories = $this->createFactories();
    $this->instance = $this->createInstance( $this->propertyTypeMock, $this->factories );  
  }
  
  
  public function testConstructorThrowsInvalidArgumentExceptionWhenFactoriesIncludesAKeyNotMatchedToAType() : void
  {
    $this->factories['invalidType'] = function() {};
    $this->expectException( InvalidArgumentException::class );
    new PropertyTypeFactoryFactory( $this->propertyTypeMock, $this->factories );
  }
  
  
  public function testConstructorThrowsInvalidArgumentExceptionWhenFactoriesValueIsNotAClosure() : void
  {
    $this->factories[IPropertyType::TBOOLEAN] = null;
    $this->expectException( InvalidArgumentException::class );
    new PropertyTypeFactoryFactory( $this->propertyTypeMock, $this->factories );
  }
  
  
  public function testGetTypeInstanceReturnsMapOfPropertyTypeToClosure() : void
  {
    $propType = $this->instance->getTypeInstance();
    $enumValues = $propType->getEnumValues();
    $this->assertTrue( is_array( $enumValues ));
    $this->assertSame( self::propertyTypeArray, $enumValues );
  }
  
  
  public function testGetTypesReturnsListOfPropertyTypeConstantValues() : void
  {
    $enumValues = $this->instance->getTypes();
    $this->assertTrue( is_array( $enumValues ));
    $this->assertSame( self::propertyTypeArray, $enumValues );
  }


  protected function createInstance( IPropertyType $propertyTypeMock, array $factories ) 
  {
    return new PropertyTypeFactoryFactory( $propertyTypeMock, $factories );  
  }
  
  
  protected function getInstance() 
  {
    return $this->instance;
  }
  
  
  protected function getPropertyTypeArray()
  {
    return self::propertyTypeArray;
  }
  
  
  protected function getPropertyTypeMock()
  {
    return $this->propertyTypeMock;
  }
  
  
  protected function getFunctionsArray()
  {
    return $this->factories;
  }
  
  
  protected function getMockPropertyBuilder( string $type )
  {
    if ( strlen( $type ) == 0 ) 
      throw new InvalidArgumentException( 'type must not be an empty' );
    
    $pt = $this->getMockBuilder( IPropertyType::class )->getMock();
    $pt->method( 'value' )->willReturn( $type );
    
    $mock = $this->getMockBuilder( IPropertyBuilder::class )->getMock();
    $mock->method( 'getType' )->willReturn( $pt );
    
    return $mock;
    
  }
  
  
  protected function createFactories()
  {
    $this->intPropertyMock = $this->getMockBuilder( IProperty::class )->getMock();
    $this->intPropertyMock->method( 'getValue' )->willReturn( self::intValue );
    
    $ipt = $this->getMockBuilder( IPropertyType::class )->getMock();
    $ipt->method( 'value' )->willReturn( IPropertyType::TINTEGER );
    $this->intPropertyMock->method( 'getType' )->willReturn( $ipt );
    
    $this->strPropertyMock = $this->getMockBuilder( IProperty::class )->getMock();
    $this->strPropertyMock->method( 'getValue' )->willReturn( self::strValue );

    $spt = $this->getMockBuilder( IPropertyType::class )->getMock();
    $spt->method( 'value' )->willReturn( IPropertyType::TSTRING );
    $this->strPropertyMock->method( 'getType' )->willReturn( $spt );

    
    return [
      IPropertyType::TINTEGER => fn( IPropertyBuilder $b ) : IProperty => $this->intPropertyMock,
      IPropertyType::TSTRING => fn( IPropertyBuilder $b ) : IProperty => $this->strPropertyMock
    ];        
  }
  
  
  protected function createPropertyTypeMock( array $types )
  {
    $m = $this->getMockBuilder( IPropertyType::class )->getMock();
    $m->method( 'getEnumValues' )->willReturn( $types );
    
    return $m;
  }
}
