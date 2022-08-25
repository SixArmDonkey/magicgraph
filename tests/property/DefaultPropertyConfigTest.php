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
  
  
  public function testConstantsExist() : void
  {
    $r = new ReflectionClass( new DefaultPropertyConfig());
    
    $this->assertTrue( $r->getConstant( 'CAPTION' ) !== false );
    $this->assertTrue( $r->getConstant( 'ID' ) !== false );    
    $this->assertTrue( $r->getConstant( 'VALUE' ) !== false );        
    $this->assertTrue( $r->getConstant( 'SETTER' ) !== false );
    $this->assertTrue( $r->getConstant( 'GETTER' ) !== false );    
    $this->assertTrue( $r->getConstant( 'MSETTER' ) !== false );        
    $this->assertTrue( $r->getConstant( 'MGETTER' ) !== false );
    $this->assertTrue( $r->getConstant( 'TOARRAY' ) !== false );    
    $this->assertTrue( $r->getConstant( 'TYPE' ) !== false );        
    $this->assertTrue( $r->getConstant( 'FLAGS' ) !== false );
    $this->assertTrue( $r->getConstant( 'CLAZZ' ) !== false );    
    $this->assertTrue( $r->getConstant( 'INIT' ) !== false );        
    $this->assertTrue( $r->getConstant( 'MIN' ) !== false );
    $this->assertTrue( $r->getConstant( 'MAX' ) !== false );    
    $this->assertTrue( $r->getConstant( 'VALIDATE' ) !== false );        
    $this->assertTrue( $r->getConstant( 'PATTERN' ) !== false );
    $this->assertTrue( $r->getConstant( 'CONFIG' ) !== false );    
    $this->assertTrue( $r->getConstant( 'PREFIX' ) !== false );        
    $this->assertTrue( $r->getConstant( 'CHANGE' ) !== false );
    $this->assertTrue( $r->getConstant( 'HTMLINPUT' ) !== false );    
    $this->assertTrue( $r->getConstant( 'IS_EMPTY' ) !== false );        
    $this->assertTrue( $r->getConstant( 'TAG' ) !== false );
  }
  
  
}
