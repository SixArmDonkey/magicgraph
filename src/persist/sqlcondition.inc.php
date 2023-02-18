<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\persist;


/**
 * A SQL Condition 
 * @deprecated Stupid
 */
class SQLCondition implements ISQLCondition
{
  /**
   * Property
   * @var string
   */
  private $property;
  
  /**
   * Operator 
   * @var ESQLOperator
   */
  private $operator;
  
  /**
   * Value 
   * @var mixed
   */
  private $value;
  
  
  public function __construct( string $property, ESQLOperator $operator, $value )
  {
    $this->property = str_replace( '`', '', $property );
    $this->operator = $operator;
    $this->value = $value;
  }
  
  
  /**
   * Retrieve the property 
   * @return string property
   */
  public function getProperty() : string
  {
    return $this->property;
  }
  
  
  /**
   * Retrieve the operator
   * @return ESQLOperator operator 
   */
  public function getOperator() : ESQLOperator
  {
    return $this->operator;
  }
  
  
  /**
   * Retrieve the value
   * @return mixed value 
   */
  public function getValue()
  {
    return $this->value;
  }
  
  
  /**
   * Retrieve the condition as a string
   * @return string condition
   */
  public function getCondition() : string
  {
    return '`' . $this->property . '` ' . $this->operator->getOperatorAndValue( $this->value );            
  }  
  
  
  /**
   * Retrieve a list of values 
   * @return array values 
   */
  public function getValues() : array
  {
    return [$this->value];
  }
  
  
  
  /**
   * Retrieve the condition as a string
   * @return string condition
   */
  public function __toString()
  {
    return $this->getCondition();
  }
}
