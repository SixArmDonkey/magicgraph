<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph;

use Exception;

/**
 * Thrown when some record fails to validate prior to save.
 */
class ValidationException extends Exception 
{
  private string $propertyName;
  
  /**
   * Construct the exception
   * <p>Constructs the Exception.</p>
   * @param string $message <p>The Exception message to throw.</p>
   * @param int $code <p>The Exception code.</p>
   * @param \Throwable $previous <p>The previous exception used for the exception chaining.</p>
   * @return self
   * @link http://php.net/manual/en/exception.construct.php
   * @since PHP 5, PHP 7
   */
  public function __construct(string $message = "", int $code = 0, \Throwable $previous = NULL, string $propertyName = '' ) 
  {
    parent::__construct( $message, $code, $previous );
    if ( empty( $propertyName ))
    {
      //..Eh.
      $parts = explode( ' ', $message );
      $propertyName = str_replace( '"', '', reset( $parts ));
    }
    $this->propertyName = $propertyName;
    
    
  }  
  
  
  public function getPropertyName() : string
  {
    return $this->propertyName;
  }
}

