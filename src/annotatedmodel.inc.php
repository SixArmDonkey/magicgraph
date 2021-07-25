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

namespace buffalokiwi\magicgraph;

use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\property\QuickPropertySet;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Attribute;

  
/**
 * @todo Build this into defaultmodel?
 */
class AnnotatedModel extends DefaultModel
{  
  
  
  public function __construct( ?IPropertySet $properties = null )
  {
    if ( $properties == null )
      $properties = new QuickPropertySet([]);
    
    $r = new ReflectionClass( static::class );

    foreach( $r->getProperties() as $prop )
    {
      /* @var $prop ReflectionProperty */
      /* @var $t ReflectionNamedType */
      
      if ( $prop->isPublic()) 
      { 
        $val = $prop->getValue($this);
        if ( $val instanceof property\IProperty )
          $properties->addProperty( $val );
      }      
      else
      {   
        foreach( $prop->getAttributes() as $a )
        {
          /* @var $a Attribute */
          
          $args = $a->getArguments();
          
          //..Stinky assumption that name is always the first argument.
          if ( !isset( $args[0] ))
            $args[0] = $prop->getName();          
          else if ( $args[0] === '' )
            $args[0] = $prop->getName();
          
          $c = $a->getName();
          
          $p = new $c( ...$args );  
          
          if ( !( $p instanceof property\IProperty ))
          {
            throw new \Exception( 'Property ' . $prop->getName() . ' of ' . static::class . ' is not an instance of ' . IProperty::class . '.  Attribute type is invalid.' );
          }

          
          
          $properties->addProperty( $p );          
        }
      }
    }
    
    parent::__construct( $properties );
  }
}

