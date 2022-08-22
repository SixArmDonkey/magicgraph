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

use buffalokiwi\magicgraph\property\ArrayProperty;
use buffalokiwi\magicgraph\property\IObjectPropertyBuilder;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyType;


/**
 * 
 */
class ArrayPropertyTest extends AbstractPropertyTest
{
  protected const name = 'name';
  protected const defaultValue = ['default'];
  protected const caption = 'caption';
  protected const id = 1;
  protected const tag = 'tag';
  protected const config = [true];
  protected const prefix = 'prefix';
  protected const flagTotal = 12345;
  protected const value1 = ['test'];
  protected const value2 = ['testtwo'];
  protected const invalidValue = 1;  //..Invalid value used for validation tests 
  
  
  //..This was a weirdly specific feature that was used to convert json-strings to an array or to allow adding 
  // typed data into an existing array.  This was removed in favor of property decorators.
  /*
  public function testAddValuesToUntypedArray() : void
  {
    $this->instance->reset();
    $this->instance->setValue( 'foo' );
    $this->assertSame( ['foo'], $this->instance->getValue());
  }
  */
  
  
  protected function getInstance( $pb, $useNull = false ) : IProperty
  {
    return new ArrayProperty( $pb );
  }


  protected function getPropertyBuilderClassName() : string
  {
    return IObjectPropertyBuilder::class;
  }
  
  
  protected function getPropertyType() : string
  {
    return IPropertyType::TARRAY;
  }
}
