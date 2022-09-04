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

use buffalokiwi\magicgraph\property\IPropertyBuilder;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\property\PropertyBuilderFactory;


class PropertyBuilderFactoryTest extends PropertyTypeFactoryFactoryTest
{
  public function testCreateThrowsExceptionWhenMappedClosureDoesNotReturnIPropertyBuilder() : void
  {
    $factories = $this->getFunctionsArray();
    $factories[IPropertyType::TBOOLEAN] = fn() => 'invalid';
    
    $this->expectException( Exception::class );
    ( new PropertyBuilderFactory( $this->getPropertyTypeMock(), $factories ))
      ->create( $this->getMockPropertyBuilder( IPropertyType::TBOOLEAN ));
  }
  
  
  public function testCreatePropertyReturnsIProperty() : void
  {
    $this->assertInstanceOf( 
      IPropertyBuilder::class, 
      $this->getInstance()->create( 'name', IPropertyType::TSTRING )
    );
  }
  
  
  public function getCreateThrowsInvalidArgumentExceptionWhenSupplyingAnInvalidPropertyType() : void
  {
    $this->expectException( InvalidArgumentException::class );
    $this->getInstance()->create( $this->getMockPropertyBuilder( 'invalid' ));
  }
  
  
  public function getCreateThrowsExceptionWhenCreatedBuilderPropertyTypeDoesNotMatchSuppliedType() : void
  {
    //..The type returned by the generated property builder is purposely different than the type supplied to create()
    $pt = $this->getMockBuilder( IPropertyType::class )->getMock();
    $pt->method( 'value' )->willReturn( IPropertyType::TBOOLEAN );
    
    $pb = $this->getMockBuilder( IPropertyBuilder::class )->getMock();
    $pb->method( 'getType' )->willReturn( $pt );
    
    $propertyTypeMock = $this->getMockBuilder( IPropertyType::class )->getMock();
    $propertyTypeMock->method( 'getEnumValues' )->willReturn([ 
      IPropertyType::TSTRING
    ]);
    
    $factories = [
      IPropertyType::TSTRING => fn() => $pb
    ];
    
    $this->expectException( Exception::class );
    ( new PropertyBuilderFactory( $propertyTypeMock, $factories ))->create( 'name', IPropertyType::TSTRING );
  }
    
  
  protected function createInstance( IPropertyType $propertyTypeMock, array $factories ) 
  {
    return new PropertyBuilderFactory( $propertyTypeMock, $factories );
  }
  

  protected function createFactories()
  {
    $this->propertyTypeMock = $this->getMockBuilder( IPropertyType::class )->getMock();
    $this->propertyTypeMock->method( 'getEnumValues' )->willReturn( self::propertyTypeArray );
    
    $this->intPropertyMock = $this->getMockBuilder( IPropertyBuilder::class )->getMock();
    
    $ipt = $this->getMockBuilder( IPropertyType::class )->getMock();
    $ipt->method( 'value' )->willReturn( IPropertyType::TINTEGER );
    $this->intPropertyMock->method( 'getType' )->willReturn( $ipt );
    
    $this->strPropertyMock = $this->getMockBuilder( IPropertyBuilder::class )->getMock();

    $spt = $this->getMockBuilder( IPropertyType::class )->getMock();
    $spt->method( 'value' )->willReturn( IPropertyType::TSTRING );
    $this->strPropertyMock->method( 'getType' )->willReturn( $spt );

    return [
      IPropertyType::TINTEGER => fn() : IPropertyBuilder => $this->intPropertyMock,
      IPropertyType::TSTRING => fn() : IPropertyBuilder => $this->strPropertyMock
    ];        
  }  
}
