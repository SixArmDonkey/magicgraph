<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\magicgraph\IModel;


/**
 * Defines an attribute group entry.
 */
interface IAttributeGroup extends IModel
{
  
  /**
   * Retrieve the attribute group id
   * @return int id 
   */
  public function getId() : int;
  
  
  /**
   * Retrieve the attribute group name/caption
   * @return string name/caption
   */
  public function getName() : string;
  
  
  /**
   * Retrieve a list of attribute codes 
   * @return [id => code]
   */
  public function getAttributeCodes() : array;
  
  /**
   * Retrieve a list of attribute codes (column/property names) contained within this group
   * @return IAttribute[] Attributes 
   */
  public function getAttributes() : array;

  
  /**
   * Simply overwrite all attribute codes with the supplied list.
   * @param array $codes new codes
   * @return void
   */
  public function setAttributes( IAttribute ...$attributes ) : void;

  
  /**
   * Sets the attribute group name/caption
   * @param string $value caption
   */
  public function setName( string $value ) : void;
  
  
  /**
   * Retrieve the configuration array used to initialize 
   * @return array
   */
  public function getAttributeConfig() : array;  
}
