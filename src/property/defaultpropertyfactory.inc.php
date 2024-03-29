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

use Exception;


class DefaultPropertyFactory extends PropertyTypeFactory implements IPropertyFactory
{
  public function create( IPropertyBuilder $builder ) : IProperty
  {
    /**
     * @todo getType() may be deprecated.  
     */
    $f = $this->getFactoryFunction( $builder->getType()->value());
    $prop = $f( $builder );
    if ( !( $prop instanceof IProperty ))
      throw new Exception( sprintf( 'Property factory function for property type %s does not return an instance of %s.', $type, IProperty::class ));
    
    return $prop;
  }

  public function createProperty( IPropertyBuilder $builder ): IProperty
  {
    
  }

  public function getTypes(): array
  {
    
  }

}
