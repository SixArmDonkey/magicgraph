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

use buffalokiwi\magicgraph\junctionprovider\IJunctionTargetProperties;


/**
 * Column/property names for the attribute model storage.
 */
interface IAttributeCols extends IJunctionTargetProperties
{
  /**
   * Retrieve the attribute id column name 
   * @return string column name 
   */
  public function getId() : string;
  
  
  /**
   * Retrieve the attribute caption column name 
   * @return string column name 
   */
  public function getCaption() : string;
  
  
  /**
   * Retrieve the attribute code column name 
   * @return string column name 
   */
  public function getCode() : string;  
  
  /**
   * Retrieve the attribute type column name 
   * @return string column name 
   */
  public function getType() : string;
  
  
  /**
   * Retrieve the attribute default value column name 
   * @return string column name 
   */
  public function getDefault() : string;
  
  
  /**
   * Retrieve the attribute flags column name 
   * @return string column name 
   */
  public function getFlags() : string;  
  

  /**
   * Retrieve the attribute additional behavior class name column name 
   * @return string column name 
   */
  public function getBehavior() : string;
  
  
  /**
   * Retrieve the attribute data class name column name 
   * @return string column name 
   */
  public function getClass() : string;
  
  
  /**
   * Retrieve the attribute validation regex pattern column name 
   * @return string column name 
   */
  public function getPattern() : string;
  
  
  /**
   * Retrieve the attribute min value/length column name 
   * @return string column name 
   */
  public function getMin() : string;
  
  
  /**
   * Retrieve the attribute max value/length column name 
   * @return string column name 
   */
  public function getMax() : string;
  
  
  /**
   * Retrieve the config column naem 
   * @return string name 
   */
  public function getConfigColumn() : string;
  
  /**
   * Retrieve the tag column name 
   * @return string name 
   */
  public function getTagColumn() : string;  
}
