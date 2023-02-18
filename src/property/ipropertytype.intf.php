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


namespace buffalokiwi\magicgraph\property;

use buffalokiwi\buffalotools\types\IEnum;


/**
 * Defines an enumeration of property data types.
 */
interface IPropertyType extends IEnum
{
  /**
   * A boolean
   */
  const TBOOLEAN = 'bool';
  
  
  /**
   * An integer
   */
  const TINTEGER = 'int';
  
  
  /**
   * A decimal 
   */
  const TFLOAT = 'float';
  
  
  /**
   * A string 
   */
  const TSTRING = 'string';
  
  
  /**
   * An enum.
   * Column must use  class implementing the IEnum interface
   *
   */
  const TENUM = 'enum';
  
  /**
   * A runtime enum instance.
   * Enum members are configured via the "config" property and is backed by
   * a RuntimeEnum instance.
   */
  const TRTENUM = 'rtenum';  
  
  /**
   * An array property 
   */
  const TARRAY = 'array';
  
  /**
   * A set 
   * Column must use a class implementing the ISet interface
   */
  const TSET = 'set';
  
  /**
   * A date 
   */
  const TDATE = 'date';
  
  /**
   * Currency.
   * Column must use a class implementing the IMoney interface 
   */
  const TMONEY = 'money';
  
  /**
   * A property backed by some IModel instance 
   */
  const TMODEL = 'model';
  
  /**
   * A property that only accepts instances of a specified object type
   */
  const TOBJECT = 'object';
}
