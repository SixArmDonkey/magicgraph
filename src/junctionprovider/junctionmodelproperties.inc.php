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

use buffalokiwi\magicgraph\property\BasePropertyConfig;


/**
 * Basic junction table model properties.
 */
class JunctionModelProperties extends BasePropertyConfig implements IJunctionModelProperties
{
  const ID = 'id';
  const PARENT_ID = 'link_parent';
  const TARGET_ID = 'link_target';
  
  
  /**
   * Constructor 
   * @param INamedPropertyBeavior $behavior Property behavior modifications 
   */
  public function __construct(\buffalokiwi\magicgraph\property\INamedPropertyBehavior ...$behavior )
  {
    parent::__construct( ...$behavior );
  }
  
  
  
  /**
   * Retrieve the primary key property name 
   * @return string name 
   */
  public function getId() : string
  {
    return self::ID;
  }
  
  
  /**
   * Retrieve the parent id property name 
   * @return string name 
   */
  public function getParentId() : string
  {
    return self::PARENT_ID;
  }
  
  
  /**
   * Retrieve the target id property name 
   * @return string name 
   */
  public function getTargetId() : string
  {
    return self::TARGET_ID;
  }
    
  
  /**
   * Get the config 
   * @return array config 
   */
  protected function createConfig() : array
  {
    return [
      self::ID        => self::FINTEGER_PRIMARY,
      self::PARENT_ID => self::FINTEGER_REQUIRED,
      self::TARGET_ID => self::FINTEGER_REQUIRED
    ];
  }
}
