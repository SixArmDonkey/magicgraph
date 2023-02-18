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

namespace buffalokiwi\magicgraph\property\annotation;

use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyBehavior;
use buffalokiwi\magicgraph\property\PropertyBehavior;
use buffalokiwi\magicgraph\ValidationException;


class EmailBehavior extends PropertyBehavior implements IPropertyBehavior
{
  public function __construct()
  {
    parent::__construct( function( IProperty $prop, $value ) {
        if ( !empty( $value ) && !filter_var( $value, FILTER_VALIDATE_EMAIL ))
        {
          throw new ValidationException( 'Invalid email address' );
        }

        return true;
      }
    );
  }
}


