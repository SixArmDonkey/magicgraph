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

use buffalokiwi\magicgraph\property\DefaultPropertyConfig;
use PHPUnit\Framework\TestCase;


/**
 * This isn't a test as much as it is an insurance policy against removing very important, and required, constants
 * from DefaultPropertyConfg
 */
class DefaultPropertyConfigTest extends TestCase
{
  const constantsToTest = [
    'CAPTION',
    'ID',
    'VALUE',
    'SETTER',
    'GETTER',
    'MSETTER',
    'MGETTER',
    'TOARRAY',
    'TYPE',
    'FLAGS',
    'CLAZZ',
    'INIT',
    'MIN',
    'MAX',
    'VALIDATE',
    'PATTERN',
    'CONFIG',
    'PREFIX',
    'CHANGE',
    'HTMLINPUT',
    'IS_EMPTY',
    'TAG'
  ];
  
  
  public function testConstantsExist() : void
  {
    $r = new ReflectionClass( new DefaultPropertyConfig());
    
    foreach( static::constantsToTest as $const )
    {
      $this->assertNotEmpty( $const );
      $this->assertTrue( is_string( $const ));
      $this->assertTrue( $r->getConstant( $const ) !== false );
    }
  }
}
