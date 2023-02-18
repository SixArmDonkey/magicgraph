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

namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\buffalotools\types\BigSet;
use buffalokiwi\magicgraph\IModelPropertyProvider;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\ServiceableModel;


abstract class ServiceableAttributeModel extends ServiceableModel implements IAttributeModel
{
  public function __construct( IPropertySet $properties, IModelPropertyProvider ...$providers )
  {
    parent::__construct( $properties, ...$providers );
  }
  
  
  /**
   * Retrieve a list of additional attributes attached to this product instance.
   * @param BigSet|null $names Optional set of property names to filter results by.
   * Any included names will be listed in the output, and anything not listed is 
   * omitted.  Set to null to output everything (default).
   * @return array [name => [caption,value]] properties 
   */
  public function getAttributes( ?BigSet $names = null ) : array
  {        
    /**
     * @todo What the fuck is this doing here????
     */
    if ( interface_exists( '\buffalokiwi\retailrack\magicgraph\IRRPropertyFlags' ))
      $flag = \buffalokiwi\retailrack\magicgraph\IRRPropertyFlags::NOT_EDITABLE;
    else
      $flag = '';
    
    $out = [];
    foreach( $this->getPropertySet()->getProperties() as $p )
    {
      /* @var $p IProperty */
      
      if ( $names != null && ( !$names->isMember( $p->getName()) || !$names->hasVal( $p )))
        continue;      
      else if ( $p->getFlags()->SUBCONFIG() && ( empty( $flag ) || !$p->getFlags()->hasVal( $flag )))
      {
        $out[$p->getName()] = [$p->getCaption(), $p->getValue()];
      }
    }
    
    return $out;
  }  
}

