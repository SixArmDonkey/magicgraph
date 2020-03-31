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

namespace buffalokiwi\magicgraph\misc\random;

use InvalidArgumentException;


/**
 * Generates a random code in base36
 */
class RandomCodeGenerator
{
  /**
   * Number of bytes to use.
   * @var int 
   */
  private $bytes;
  
  /**
   * A list of characters that are prohibited in generated codes.
   * This will generate a new code each time one of these characters is encountered
   * in a generated code.
   * @var string[]
   */
  private $invalidChars = ['1','I','0','O','S','5'];
  
  
  /**
   * Create a new Random Code Generator 
   * @param int $bytes number of bytes to use when generating a code.
   * Defaults to 5 bytes.
   * @param string $invalidChars One or more invalid characters. If a generated code
   * contains one of the listed characters, a new code is generated until one without 
   * an invalid character is generated.  Don't add too many characters here...
   * I've added an arbitrary limit of 8 characters that can be omitted.
   * This defaults to ['1','I','0','O']
   * 
   * @throws InvalidArgumentException if bytes is less than one
   */
  public function __construct( int $bytes = 5, string ...$invalidChars )
  {
    if ( $bytes < 1 )
      throw new InvalidArgumentException( 'bytes must be greater than zero' );
    
    $this->bytes = $bytes;
    
    if ( sizeof( $invalidChars ) > 8 )
      throw new InvalidArgumentException( 'No more than 8 characters can be omitted' );
    
    if ( !empty( $invalidChars ))
    {
      foreach( $invalidChars as $c )
      {
        if ( strlen( $c ) != 1 )
          throw new InvalidArgumentException( 'invalidChars values must be 1 character in length' );
      }
      $this->invalidChars = $invalidChars;
    }
  }
  
  
  /**
   * Generate a random base36 code.
   * @param int $split Split the generated code every $split characters with a 
   * hyphen.
   * @return string code 
   */
  public function generate( int $split = 3 )
  {
    $invalid = false;

    do 
    {
      //..5 bytes seems good for a decent number of returns.  But hey it's random.  
      //..Ran this in a loop of 1 million, and it normally doesn't collide until at least 100k numbers.
      //..Could do something like generate 5 of them, and then see which ones exist in some database.
      //  Pick one that doesn't, but then there's all that locking bs that needs to happen.
      //..Maybe just go with this for now and fix it later?
      $k = strtoupper( base_convert( bin2hex( random_bytes( $this->bytes )), 16, 36 ));
      foreach( $this->invalidChars as $c )
      {
        if ( strpos( $k, $c ) !== false )
        {
          $invalid = true;
          break;
        }
        else
          $invalid = false;
      }
    } while( $invalid );

    return ( $split > 0 ) ? implode( '-', str_split( $k, 3 )) : implode( '-', $k );
  }
}
