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

namespace buffalokiwi\magicgraph\property\htmlproperty;


/**
 * An HTML input element 
 */
interface IElement
{
  /**
   * Get the element id property value
   * @return string id 
   */
  public function getId() : string;
  
  
  /**
   * Get the input "name" property value 
   * @return string name 
   */
  public function getName() : string;
  
  
  /**
   * Get the element name 
   * @return string name 
   */
  public function getElement() : string;  
  
  
  /** 
   * Get the element attributes list.
   * Key = value.
   * setting id or name here will have no effect.
   * @return array attributes map 
   */
  public function getAttributes() : array;  
  
  
  /**
   * Convert this element into HTML.
   * @return string html element 
   */
  public function build() : string;  
}
