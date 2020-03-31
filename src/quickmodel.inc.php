<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph;

use buffalokiwi\magicgraph\property\QuickPropertySet;


class QuickModel extends DefaultModel
{
  public function __construct( array $config )
  {
    parent::__construct( new QuickPropertySet( $config ));
  }
}
