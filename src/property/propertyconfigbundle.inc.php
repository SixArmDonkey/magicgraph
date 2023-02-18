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
 * Not entirely sure if this is used.
 * @todo Find out what this is.
 */
class PropertyConfigBundle
{
  /**
   * A map of [interface name => IPropertyConfig]
   * @var array
   */
  private $data;
  
  public function __construct( PropertyConfigBundle ...$bundles )
  {
    if ( !empty( $bundles ))
      $this->bundle( $bundles );
  }
  
  
  /**
   * Adds a config instance to this bundle 
   * @param string $interface
   * @param \buffalokiwi\magicgraph\property\IPropertyConfig $config
   * @throws \InvalidArgumentException
   */
  public function setConfig( string $interface, IPropertyConfig $config )
  {
    if ( empty( $interface ))
      throw new \InvalidArgumentException( 'interface must not be empty' );
    
    $this->data[$interface] = $config;
  }
  
  
  public function getData() : array
  {
    return $this->data;
  }
  
  
  public function bundleWith( PropertyConfigBundle ...$that ) : void
  {
    $this->bundle( ...$that );
  }
  
  
  private function bundle( PropertyConfigBundle ...$that ) : void
  {
    foreach( $that as $bundle )
    {
      foreach( $bundle as $intf => $config )
      {
        $this->data[$intf] = $config;
      }
    }    
  }
}
