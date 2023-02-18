<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2023 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */

declare( strict_types=1 );

use buffalokiwi\magicgraph\property\IConfigMapper;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\MappedPropertyFactory;
use PHPUnit\Framework\TestCase;


class MappedPropertyFactoryTest extends TestCase 
{
  public function testGetMapperReturnsMapperSuppliedToConstructor()
  {
    $mockConfigMapper = $this->getMockBuilder( IConfigMapper::class )->getMock();
    
    $instance = new MappedPropertyFactory( $mockConfigMapper );
    $this->assertSame( $mockConfigMapper, $instance->getMapper());  
  }
  
  
  /**
   * Ensure that getProperties() iterates over $config and calls 
   * $this->mapper->map and passes $config->getConfig().
   */
  public function testGetPropertiesCallsMapAndSuppliesPropertyConfigArray()
  {
    $configArray = ['config' => 'array'];
    
    //..IPropertyConfig::getConfig() return $configArray 
    $mockPropertyConfig = $this->getMockBuilder( IPropertyConfig::class )->getMock();
    $mockPropertyConfig->expects( $this->once())
      ->method( 'getConfig' )
      ->willReturn( $configArray );
        
    //..IConfigMapper::map() expects arg0 = $configArray and returns [IProperty]
    $mockIProperty1 = $this->getMockBuilder( IProperty::class )->getMock();
    $mockConfigMapper = $this->getMockBuilder( IConfigMapper::class )->getMock();
    $mockConfigMapper->expects( $this->once())
      ->method( 'map' )
      ->with( $this->equalTo( $configArray ))
      ->willReturn( [$mockIProperty1] );
    
    $instance = new MappedPropertyFactory( $mockConfigMapper );
    
    $this->assertSame( [$mockIProperty1], $instance->getProperties( $mockPropertyConfig ));
  }
  
  
  public function testGetPropertiesThrowsExceptionWhenMapperMapThrowsException()
  {
    $mockMapper = $this->getMockBuilder( IConfigMapper::class )->getMock();
    $mockMapper->expects( $this->once())
      ->method( 'map' )
      ->will( $this->throwException( new Exception()));
    
    $instance = new MappedPropertyFactory( $mockMapper );
    
    $errorCount = 0;
    $origHandler = set_error_handler( fn() => $errorCount++ );
    
    $this->expectException( Exception::class );
    
    $instance->getProperties( $this->getMockBuilder( IPropertyConfig::class )->getMock());
    
    $this->assertSame( 1, $errorCount );
    
    if ( is_callable( $origHandler ))
      set_error_handler( $origHandler );    
  }
  
  
  public function testGetPropertiesInvokesModifyConfigOnEachPropertyConfigObject()
  {
    $mc1 = $this->getMockBuilder( IPropertyConfig::class )->getMock();
    $mc1->expects( $this->once())->method( 'modifyConfig' );
      
    $mc2 = $this->getMockBuilder( IPropertyConfig::class )->getMock();
    $mc2->expects( $this->once())->method( 'modifyConfig' );
    
    $instance = new MappedPropertyFactory( $this->getMockBuilder( IConfigMapper::class )->getMock());
    
    $instance->getProperties( $mc1, $mc2 );
  }
  
  
  public function testGetPropertiesReturnsMergedArrayOfIPropertyAfterMultipleMapCalls()
  {
    $r1 = ['config1'];
    $mc1 = $this->getMockBuilder( IPropertyConfig::class )->getMock();
    $mc1->expects( $this->once())->method( 'getConfig' )->willReturn( $r1 );
    
    $r2 = ['config2'];
    $mc2 = $this->getMockBuilder( IPropertyConfig::class )->getMock();
    $mc2->expects( $this->once())->method( 'getConfig' )->willReturn( $r2 );
    
    $mapper = $this->getMockBuilder( IConfigMapper::class )->getMock();
    $mapper->expects( $this->exactly( 2 ))->method( 'map' )->will( $this->returnArgument( 0 ));
    $instance = new MappedPropertyFactory( $mapper );
    
    $this->assertSame( array_merge( $r1, $r2 ), $instance->getProperties( $mc1, $mc2 ));
  }
}
