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

namespace buffalokiwi\magicgraph\junctionprovider;

use buffalokiwi\magicgraph\property\INamedPropertyBehavior;
use buffalokiwi\magicgraph\property\QuickPropertyConfig;
use Closure;
use InvalidArgumentException;


/**
 * Used when creating property sets on the fly and you need a junction target for a junction table style relationship.
 */
class QuickJunctionPropertyConfig extends QuickPropertyConfig implements IJunctionTargetProperties
{
  /**
   * Primary key property name 
   * @var string
   */
  private string $idPropertyName;
  
  /**
   * Create a new IPropertyConfig instance 
   * @param array $config Config data array 
   * @param string $idPropertyName The property name of the primary key 
   * @param Closure $onValidate f( IModel ) throws ValidationException
   */
  public function __construct( array $config, string $idPropertyName, ?Closure $onValidate = null, INamedPropertyBehavior ...$behavior )
  {
    parent::__construct( $config );
            
    if ( empty( $idPropertyName ))
      throw new InvalidArgumentException( 'idPropertyName must not be empty' );
    
    $this->idPropertyName = $idPropertyName;
  }
  
  
  /**
   * Retrieve the primary key property name of the target model.
   * @return string name 
   */
  public function getId() : string
  {
    return $this->idPropertyName;
  }
}

