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

use buffalokiwi\magicgraph\property\DefaultPropertyConfig;


class DefaultPropertyConfigTest extends AbstractConstantTest
{
  
  protected function getClass() : string
  {
    return DefaultPropertyConfig::class;
  }
  
  protected function getConstants() : array
  {
    return [
      'VALUE',
      'SETTER',
      'GETTER',
      'TYPE',
      'FLAGS',
      'CLAZZ',
      'INIT',
      'MIN',
      'MAX',
      'VALIDATE',
      'PATTERN'
    ];
  }
}
