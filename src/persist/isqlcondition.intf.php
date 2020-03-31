<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\persist;


/**
 * Defines a condition used for a sql repository.
 * 
 * Defines something like `property` = 'value'
 * @deprecated Stupid
 */
interface ISQLCondition extends ICondition
{
  /**
   * Retrieve the property 
   * @return string property
   */
  public function getProperty() : string;
  
  
  /**
   * Retrieve the operator
   * @return ESQLOperator operator 
   */
  public function getOperator() : ESQLOperator;
  
  
  /**
   * Retrieve the value
   * @return mixed value 
   */
  public function getValue();  
}
  
