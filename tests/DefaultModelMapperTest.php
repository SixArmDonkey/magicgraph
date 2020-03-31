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
use buffalokiwi\magicgraph\DefaultModelMapper;
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\property\QuickPropertySet;
use PHPUnit\Framework\TestCase;



/**
 * Tests for the DefaultModelMapper class 
 */
class DefaultModelMapperTest extends TestCase
{
  /**
   * Tests the constructor and map methods.
   * The constructor must accept a closure that returns an IModel instance.
   * The map method accepts a map of key,value pairs and calls IModel::setValue.
   * setValue is expected to be called once, and it is expected to receive 2 arguments
   * 'a' and 'b'.
   * The map method is expected to return the same IModel instance created by the 
   * factory passed to the constructor.
   * @return void
   */
  public function testConstructorAndMap() : void
  {    
    /*
    $m = new DefaultModelMapper( function() { 
      $c = $this->getMockBuilder(DefaultModel::class )
        ->disableOriginalConstructor()        
        ->getMock();
      
      $c->expects( $this->once())
        ->method( 'setValue' )
        ->with( $this->equalTo( 'a' ), $this->equalTo( 'b' ));
      
      $this->assertInstanceOf( IModel::class, $c, ' got ' . get_class( $c ));
      return $c;
    });
    */
    
    //..Screw object mocking for this.  It's complicated.
    //..This 1 line covers way more code than the above mock.
    $m = new DefaultModelMapper( function() {
      return new DefaultModel( new QuickPropertySet( ['a' => ['type' => 'string', 'value' => '']] ));
    }, DefaultModel::class );
    
    $model = $m->createAndMap( ['a' => 'b'] );
    $this->assertInstanceOf( IModel::class, $model );
    $this->assertEquals( 'b', $model->getValue( 'a' ));
  }
  
  
}
