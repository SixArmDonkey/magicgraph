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

use buffalokiwi\magicgraph\property\IStringProperty;
use buffalokiwi\magicgraph\property\IStringPropertyBuilder;
use buffalokiwi\magicgraph\property\StringProperty;
use buffalokiwi\magicgraph\ValidationException;


class StringPropertyTest extends BoundedPropertyTest
{
  protected const pattern = '/^[a-zA-Z]+$/';
  protected const min = 2.0;
  protected const max = 10.0;
  protected const invalidValue = '12345';
  
  
 
  public function testMinStringLengthOutOfBoundsThrowsValidationException() : void
  {
    $this->instance->reset();
    $this->assertSame( static::min, $this->instance->getMin());
    $this->expectException( ValidationException::class );
    $this->instance->setValue( 'a' );
  }
  
  
  public function testMaxStringLengthOutOfBoundsThrowsValidationException() : void
  {
    $this->instance->reset();
    $this->assertSame( static::max, $this->instance->getMax());
    $this->expectException( ValidationException::class );
    $this->instance->setValue( str_pad( '', (int)static::max + 1, 'a' ));
  }
  
  
  public function testValueMustMatchPattern() : void
  {
    $this->instance->reset();
    $this->expectException( ValidationException::class );
    $this->instance->setValue( static::invalidValue );
  }
  
  
  public function testSetValueWithNonStrinThrowsValidationException() : void
  {
    $this->instance->reset();
    $this->expectException( ValidationException::class );
    $this->instance->setValue( false );
  }
  
  
  protected function getInstance( $pb, $useNull = false ) : IStringProperty
  {
    return new StringProperty( $pb );
  }
  
  
  protected function getPropertyBuilderClassName() : string
  {
    return IStringPropertyBuilder::class;
  }
  
  
  protected function createPropertyBuilderBase( $name = self::name, $caption = self::caption )
  {
    $b = parent::createPropertyBuilderBase( $name, $caption );
    $b->method( 'getPattern' )->willReturn( static::pattern );    
    return $b;
  }  
}
