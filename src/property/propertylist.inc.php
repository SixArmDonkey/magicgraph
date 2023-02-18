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

use InvalidArgumentException;


/**
 * A factory that accepts a list of IProperty instances.
 */
class PropertyList implements IMappedPropertyFactory
{
  /**
   * Properties list 
   * @var array
   */
  private $properties = [];
  
  
  /**
   * Create a new PropertyList 
   * @param array $properties List of IProperty instances 
   * @throws InvalidArgumentException 
   */
  public function __construct( array $properties )
  {
    foreach( $properties as $p )
    {
      if ( !( $p instanceof IProperty ))
        throw new InvalidArgumentException( 'All properties must be an instance of ' . IProperty::class );
      
      $this->properties[] = $p;
    }
  }
  
  /**
   * Retrieve the config object used for this factory.
   * Cast the result to whatever input type 
   * @return array IPropertyConfig[] config 
   */
  public function getPropertyConfig() : array
  {
    return [];
  }
  
  
  /**
   * Retrieve a list of properties 
   * @param IPropertyConfig $config One or more configuration instances.
   * @return IProperty[] properties
   */
  public function getProperties( IPropertyConfig ...$config ) : array
  {
    return $this->properties;
  }
}
