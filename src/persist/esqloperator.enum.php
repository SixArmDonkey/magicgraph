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

use buffalokiwi\buffalotools\types\Enum;
use InvalidArgumentException;


/**
 * Valid SQL Comparison operators 
 */
class ESQLOperator extends Enum
{
  const GREATER_THAN = '>';
  const GREATER_THAN_EQUAL = '>=';
  const LESS_THAN = '<';
  const LESS_THAN_EQUAL = '<=';
  const EQUAL = '=';
  const NOT_EQUAL = '!=';
  const NOT_LIKE = 'not like';
  const LIKE = 'like';
  const IN = 'in';
  const NOT_IN = 'not in';
  const BETWEEN = 'between';
  const NOT_BETWEEN = 'not between';
  const IS_NULL = 'is null';
  const IS_NOT_NULL = 'is not null';
  
  
  protected array $enum = [
    self::GREATER_THAN,
    self::GREATER_THAN_EQUAL,
    self::LESS_THAN,
    self::LESS_THAN_EQUAL,
    self::EQUAL,
    self::NOT_EQUAL,
    self::NOT_LIKE,
    self::LIKE,
    self::IN,
    self::NOT_IN,
    self::BETWEEN,
    self::NOT_BETWEEN,
    self::IS_NULL,
    self::IS_NOT_NULL
  ];
  
  
  public function getOperatorAndValue( $value )
  {
    switch( $this->value())
    {
      case self::GREATER_THAN:
      case self::GREATER_THAN_EQUAL:
      case self::LESS_THAN:
      case self::LESS_THAN_EQUAL:
      case self::EQUAL:
      case self::NOT_EQUAL:
      case self::NOT_LIKE:
      case self::LIKE:
        return $this->value() . ' ?';
        
        
      case self::IN:
      case self::NOT_IN:
        if ( !is_array( $value ))
          throw new InvalidArgumentException( "value must be an array when using IN or NOT_IN" );
        return $this->value() . '(' . implode( ',', array_map(function() { return '?'; }, $value )) . ')';
        
      case self::BETWEEN:
      case self::NOT_BETWEEN:
        if ( !is_array( $value ) || sizeof( $value ) != 2 )
          throw new InvalidArgumentException( "value must be an array containing exactly 2 elements when using BETWEEN or NOT_BETWEEN" );
        
        return $this->value() . ' ? and ?';
        
      case self::IS_NULL:
      case self::IS_NOT_NULL:
        return $this->value();
      
    }
  }
  
}
