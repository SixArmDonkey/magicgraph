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

namespace buffalokiwi\magicgraph\money;


/**
 * A factory for creating IMoney instances 
 */
interface IMoneyFactory
{  
  /**
   * Retrieve a new IMoney instance of a certain amount 
   * @param string $amount Amount 
   * @return \buffalokiwi\money\IMoney Money 
   */
  public function getMoney( string $amount ) : IMoney;  
}
