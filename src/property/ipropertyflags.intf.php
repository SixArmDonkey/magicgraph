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

namespace buffalokiwi\magicgraph\property;

use buffalokiwi\buffalotools\types\ISet;


interface IPropertyFlags extends ISet
{
  /**
   * This property may never be inserted
   */
  const NO_INSERT = 'noinsert';
  
  
  /**
   * This property may never be updated.
   * This can also be considered as "read only".
   */
  const NO_UPDATE = 'noupdate';
  
  
  /**
   * This property requires a value
   */
  const REQUIRED = 'required';
  
  
  /**
   * Property value may include null 
   */
  const USE_NULL = 'null';
  
  
  /**
   * Primary key (one per property set)
   */
  const PRIMARY = 'primary';  
  
  /**
   * The default implementation does not use this flag, but it is here in case
   * some property is loaded from some sub/third party config and you want to 
   * do something with those.
   */
  const SUBCONFIG = 'subconfig';

  
  /**
   * Calling setValue() on the model will throw a ValidationException if 
   * the stored value is not empty.
   */
  const WRITE_EMPTY = 'writeempty';
  
  /**
   * Set this flag to prevent the property from being 
   * printed during a call to IModel::toArray().
   * 
   * toArray() is used to copy and save models, and not all properties 
   * should be read.  ie: the property connects to some api on read and the 
   * returned value should not be saved anywhere.
   */
  const NO_ARRAY_OUTPUT = 'noarrayoutput';
  
  
  

}
  
