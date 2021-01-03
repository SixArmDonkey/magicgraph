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

namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\magicgraph\property\BasePropertyConfig;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertyType;


/**
 * AttrGroupLink model configuration 
 */
class AttrGroupLinkProperties extends \buffalokiwi\magicgraph\property\BasePropertyConfig implements IPropertyConfig, IAttrGroupLinkCols, \buffalokiwi\magicgraph\junctionprovider\IJunctionModelProperties
{
  const ID = "id";
  
  /**
   * Group id column 
   */
  const LINK_GROUP = 'link_group';
  
  /**
   * Attribute id column 
   */
  const LINK_ATTRIBUTE = 'link_attribute';
  
  
  /**
   * Retrieve the link id column name 
   * @return string id column
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
    return self::LINK_GROUP;
  }
  
  
  /**
   * Retrieve the target id property name 
   * @return string name 
   */
  public function getTargetId() : string
  {
    return self::LINK_ATTRIBUTE;
  }
  
  
  /**
   * Get the group id column name
   * @return string name 
   */
  public function getGroupId() : string
  {
    return self::LINK_GROUP;
  }
  
  
  /**
   * Get the attribute id column name 
   * @return string name 
   */
  public function getAttributeId() : string
  {
    return self::LINK_ATTRIBUTE;
  }
  
  
  /**
   * Get the model config array 
   * @return array config 
   */
  protected function createConfig() : array
  {
    return [
      self::ID => [
        self::TYPE => IPropertyType::TINTEGER,
        self::FLAGS => [IPropertyFlags::PRIMARY],
        self::VALUE => 0
      ],
        
      self::LINK_GROUP => [
        self::TYPE => IPropertyType::TINTEGER,
        self::FLAGS => [IPropertyFlags::REQUIRED],
        self::VALUE => 0
      ],
        
      self::LINK_ATTRIBUTE => [
        self::TYPE => IPropertyType::TINTEGER,
        self::FLAGS => [IPropertyFlags::REQUIRED],
        self::VALUE => 0
      ]        
    ];
  }
}
