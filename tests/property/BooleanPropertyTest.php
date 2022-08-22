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

use buffalokiwi\magicgraph\property\BooleanProperty;
use buffalokiwi\magicgraph\property\IBooleanProperty;
use buffalokiwi\magicgraph\property\IPropertyType;


class BooleanPropertyTest extends AbstractPropertyTest
{
  protected const name = 'name';
  protected const defaultValue = false;
  protected const caption = 'caption';
  protected const invalidValue = null;  //..Invalid value used for validation tests 

  //..value1 must be true and value2 must be false due to how empty tests are written in parent
  protected const value1 = true;
  protected const value2 = false;


  public function testGetValueAsBoolean() : void
  {
    $this->instance->reset();
    $this->instance->setValue( static::value1 );
    $this->assertSame( true, $this->instance->getValueAsBoolean());
    $this->instance->setValue( static::value2 );
    $this->assertSame( false, $this->instance->getValueAsBoolean());    
  }
  
  
  public function testTrueCanBeSetWithString() : void
  {
    $this->instance->reset();
    $this->instance->setValue( 'true' );
    $this->assertSame( true, $this->instance->getValue());
    
    $this->instance->setValue( 'yes' );
    $this->assertSame( true, $this->instance->getValue());

    $this->instance->setValue( 'y' );
    $this->assertSame( true, $this->instance->getValue());

    $this->instance->setValue( '1' );
    $this->assertSame( true, $this->instance->getValue());

    $this->instance->setValue( 'on' );
    $this->assertSame( true, $this->instance->getValue());
  }
  
  
  public function testFalseCanBeSetWithString() : void
  {
    $this->instance->reset();
    $this->instance->setValue( 'false' );
    $this->assertSame( false, $this->instance->getValue());
    
    $this->instance->setValue( 'no' );
    $this->assertSame( false, $this->instance->getValue());

    $this->instance->setValue( 'n' );
    $this->assertSame( false, $this->instance->getValue());

    $this->instance->setValue( '0' );
    $this->assertSame( false, $this->instance->getValue());

    $this->instance->setValue( 'off' );
    $this->assertSame( false, $this->instance->getValue());    
  }
  
  
  public function testToString() : void
  {
    $this->instance->reset();
    $this->instance->setValue( true );
    $this->assertSame( '1', $this->instance->__toString());
    
    $this->instance->setValue( false );
    $this->assertSame( '0', $this->instance->__toString());
    
  }
  
  
  protected function getInstance( $pb, $useNull = false ) : IBooleanProperty
  {
    return new BooleanProperty( $pb );
  }  
  
  
  protected function getPropertyType() : string
  {
    return IPropertyType::TBOOLEAN;
  }  
}
