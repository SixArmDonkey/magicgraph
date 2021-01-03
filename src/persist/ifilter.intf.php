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
 * Returns some string that can be used to filter some data set.
 * 
 * WARNING: This interface sucks and will likely go through significant changes
 * in a future release.  
 * 
 * @todo Figure out how to make IFilter make more sense.  There is no way to determine which type of filter to use when using IRepository::stream(), because we
 * have no idea what storage engine the repository represents (nor should we).
 * @deprecated To be removed.
 */
interface IFilter
{
  /**
   * Retrieve the filter string 
   * @return string filter 
   */
  public function getFilter() : string;
  
  
  /**
   * Retrieve a list of values.
   * @return array values 
   */
  public function getValues() : array;    
  
}
