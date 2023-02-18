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

namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\magicgraph\property\BasePropertyConfig;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertyType;


/**
 * Property/column definitions for the AttributeGroup model.
 */
class AttributeGroupProperties extends BasePropertyConfig implements IPropertyConfig, IAttributeGroupPropertiesCols
{
  /**
   * Attribute id 
   */
  const ID = 'id';
  
  /**
   * Attribute name 
   */
  const NAME = 'name';
  
  /**
   * Attribute list 
   */
  const ATTRS = 'attributes';

  
  /**
   * Constructor 
   * @param INamedPropertyBsearchior $behavior Property behavior modifications 
   */
  public function __construct(\buffalokiwi\magicgraph\property\INamedPropertyBehavior ...$behavior )
  {
    parent::__construct( ...$behavior );
  }
  
  
  
  /**
   * Retrieve the id property name 
   * @return string name 
   */
  public function getIdColumn() : string
  {
    return self::ID;
  }
  
  
  /**
   * Retrieve the name property name 
   * @return string name 
   */
  public function getNameColumn() : string
  {
    return self::NAME;
  }
  
  
  /**
   * Get the attributes column name
   * @return array name 
   */
  public function getAttrColumn() : string
  {
    return self::ATTRS;
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
        self::FLAGS => [IPropertyFlags::PRIMARY, IPropertyFlags::NO_INSERT],
        self::VALUE => 0
      ],
        
      self::NAME => [
        self::TYPE => IPropertyType::TSTRING,
        self::FLAGS => [IPropertyFlags::REQUIRED],
        self::VALUE => ''
      ],
        
      self::ATTRS => [
        self::TYPE => IPropertyType::TARRAY,
        self::FLAGS => [IPropertyFlags::NO_INSERT],
        self::VALUE => [],
        self::CLAZZ => IAttribute::class 
      ]
    ];
  }
}
