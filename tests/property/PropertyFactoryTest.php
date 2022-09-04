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
use buffalokiwi\magicgraph\property\PropertyFactory;



class PropertyFactoryTest extends PropertyTypeFactoryFactoryTest
{
  public function testCreatePropertyThrowsExceptionWhenMappedClosureDoesNotReturnIProperty() : void
  {
    $factories = $this->getFunctionsArray();
    $factories[IPropertyType::TBOOLEAN] = fn() => 'invalid';
    
    $this->expectException( Exception::class );
    ( new PropertyFactory( $this->getPropertyTypeMock(), $factories ))
      ->createProperty( $this->getMockPropertyBuilder( IPropertyType::TBOOLEAN ));
  }
  
  
  public function testCreatePropertyReturnsIProperty() : void
  {
    $this->assertInstanceOf( 
      IProperty::class, 
      $this->getInstance()->createProperty( $this->getMockPropertyBuilder( IPropertyType::TSTRING ))
    );
  }
  
  
  public function testCreatePropertyThrowsInvalidArgumentExceptionWhenSupplyingAnInvalidPropertyType() : void
  {

    $this->expectException( InvalidArgumentException::class );
    $this->getInstance()->createProperty( $this->getMockPropertyBuilder( 'invalid' ));
  }
  
  
  public function testCreateThrowsExceptionWhenCreatedBuilderPropertyTypeDoesNotMatchSuppliedType() : void
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
    ( new PropertyFactory( $propertyTypeMock, $factories ))->createProperty( $pb );
  }
  

  protected function createInstance( IPropertyType $propertyTypeMock, array $factories ) 
  {
    return new PropertyFactory( $propertyTypeMock, $factories );
  }
}
