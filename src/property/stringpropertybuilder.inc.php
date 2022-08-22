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

namespace buffalokiwi\magicgraph\property;


class StringPropertyBuilder extends BoundedPropertyBuilder implements IStringPropertyBuilder
{
  /**
   * Value validation pattern
   * @var string
   */
  private $pattern = '';

  public function __construct( IPropertyType $type, IPropertyFlags $flags = null, string $name = '', 
    $defaultValue = null, IPropertyBehavior ...$behavior )
  {
    parent::__construct( $type, $flags, $name, $defaultValue, ...$behavior );
  }
  
  
  /**
   * Sets the validation pattern to use.  
   * @param string $pattern Regex 
   * @return PropertyBuilder this
   */
  public function setPattern( string $pattern ) : void 
  {
    $this->pattern = $pattern;
  }
    
  
  /**
   * Retrieve the pattern used to validate the value against.
   * @return string pattern 
   */
  public function getPattern() : string
  {
    return $this->pattern;
  } 
}
