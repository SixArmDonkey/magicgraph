<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */



namespace buffalokiwi\magicgraph\property;


class QuickPropertySet extends DefaultPropertySet
{
  /**
   * 
   * @param \buffalokiwi\magicgraph\property\IPropertyConfig|array $config Config array or IPropertyConfig instance 
   */
  public function __construct( $config )
  {
    if ( is_array( $config ))
      parent::__construct( new PropertyFactory( new DefaultConfigMapper()), new QProperties( $config ));
    else if ( $config instanceof IPropertyConfig )
      parent::__construct( new PropertyFactory( new DefaultConfigMapper()), $config );
    else
      throw new \InvalidArgumentException( 'config must be an array or instance of ' . IPropertyConfig::class );
  }
}
