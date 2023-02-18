<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */

declare( strict_types=1 );

namespace buffalokiwi\magicgraph\property;


/**
 * Property Builder used for string properties.
 */
interface IStringPropertyBuilder extends IBoundedPropertyBuilder
{
  /**
   * Sets the validation pattern to use.  
   * @param string $pattern Regex 
   * @return PropertyBuilder this
   */
  public function setPattern( string $pattern ) : void;
  
  
  /**
   * Retrieve the pattern used to validate the value against.
   * @return string pattern 
   */
  public function getPattern() : string;    
}
