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

namespace buffalokiwi\magicgraph\junctionprovider;

use buffalokiwi\magicgraph\property\BasePropertyConfig;


/**
 * Property configuration for some model that is the target of a junction table.
 * You don't need to use this as a base class, but you can if you want.
 */
class JunctionTargetProperties extends BasePropertyConfig implements IJunctionTargetProperties
{
  const ID = 'id';
  
  
  
  /**
   * Constructor 
   * @param INamedPropertyBeavior $behavior Property behavior modifications 
   */
  public function __construct(\buffalokiwi\magicgraph\property\INamedPropertyBehavior ...$behavior )
  {
    parent::__construct( ...$behavior );
  }
  
  
  /**
   * Retrieve the primary key property name of the target model.
   * @return string name 
   */
  public function getId() : string
  {
    return self::ID;
  }
  
  
  /**
   * Get the property config array 
   * @return array config 
   */
  protected function createConfig() : array
  {
    return [
      self::ID => self::FINTEGER_PRIMARY
    ];
  }
}
