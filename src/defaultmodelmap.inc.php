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

namespace buffalokiwi\magicgraph;


/**
 * Stores a map of model properties to persisted properties 
 */
class DefaultModelMap implements IModelMap
{
  /**
   * Class/interface name 
   * @var string
   */
  private string $className;
  
  /**
   * Map of model property names to persisted property names 
   * @var array
   */
  private array $map;
  
  
  /**
   * 
   * @param string $className Class/interface name 
   * @param array $map map of model property names to persisted property names 
   * @throws \InvalidArgumentException 
   */
  public function __construct( string $className, array $map )
  {
    if ( empty( $className ))
      throw new \InvalidArgumentException( 'className must not be empty' );
    else if ( empty( $map ))
      throw new \InvalidArgumentException( 'map must not be empty' );
    
    foreach( $map as $k => $v )
    {
      if ( !is_string( $k ) || !is_string( $v ))
        throw new \InvalidArgumenException( 'Map keys and values must be strings' );
      else if ( !preg_match( '/^[a-zA-Z0-9_]+$/', $k . $v ))
        throw new \InvalidArgumentException( 'Property names must match the pattern "/^[a-zA-Z0-9_]+$/"' );
      
      $this->map[$k] = $v;
    }
  }
  
  
  /**
   * Retrieve the class or interface name of some model 
   * @return string
   */
  public function getClassName() : string
  {
    return $this->className;
  }
  
  
  /**
   * Retrieve a map of model property names to persisted property names 
   * @return array [model property name => persisted property name]
   */
  public function getMap() : array
  {
    return $this->map;
  }
}
