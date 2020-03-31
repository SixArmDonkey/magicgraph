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

use buffalokiwi\magicgraph\DBException;
use PHPUnit\Framework\TestCase;


/**
 * Tests the DBException class
 */
class DBExceptionTest extends TestCase
{
  const SQL = 'Some sql code';
  const OPTS = ['a','b'];
  
  
  /**
   * Creates a DBException instance with a default message, code of 1, 
   * no previous type, self::SQL and self::OPTS 
   * 
   * @return DBException instance
   */
  private function createDBException() : DBException
  {
    return new DBException( 'Message', 1, null, self::SQL, self::OPTS );
  }
  
  
  /**
   * Tests to ensure that some message is returned 
   */
  public function testToString()
  {
    $c = $this->createDBException();    
    $this->assertEquals( self::SQL, $c->getSQL());
    $this->assertEquals( self::OPTS, $c->getOpts());
  }
}
