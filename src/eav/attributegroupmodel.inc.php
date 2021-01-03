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

namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\IModelPropertyProvider;
use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\ServiceableModel;
use InvalidArgumentException;


class AttributeGroupModel extends ServiceableModel implements IAttributeGroup
{
  /**
   * Column names
   * @var IAttributeGroupPropertiesCols
   */
  private $cols;

  
  /**
   * Create a new AttributeGroupModel instance.   
   * @param IModel $model Model instance 
   * @param IModelPropertyProvider $providers A list of service providers for this model
   */
  public function __construct( IPropertySet $properties, IModelPropertyProvider ...$providers )  
  {
    parent::__construct( $properties, ...$providers );
    
    $this->cols = $this->getPropertySet()->getPropertyConfig( IAttributeGroupPropertiesCols::class );
  }
  
  
  /**
   * Retrieve the attribute group id
   * @return int id 
   */
  public function getId() : int
  {
    return $this->getValue( $this->cols->getIdColumn());
  }
  
  
  /**
   * Retrieve the attribute group name/caption
   * @return string name/caption
   */
  public function getName() : string
  {
    return $this->getValue( $this->cols->getNameColumn());
  }
  
  
  /**
   * Retrieve a list of attribute codes 
   * @return [id => code]
   */
  public function getAttributeCodes() : array
  {
    $out = [];
    foreach( $this->getAttributes() as $a )
    {
      $out[$a->getId()] = $a->getCode();
    }
    return $out;
  }
  
  
  /**
   * Retrieve a list of attribute codes (column/property names) contained within this group
   * @return IAttribute[] Attributes 
   */
  public function getAttributes() : array
  {
    return $this->getValue( $this->cols->getAttrColumn());
  }

  
  /**
   * Simply overwrite all attribute codes with the supplied list.
   * @param array $codes new codes
   * @return void
   */
  public function setAttributes( IAttribute ...$attributes ) : void
  {
    $this->setValue( $this->cols->getAttrColumn(), $attributes );
  }

  
  /**
   * Sets the attribute group name/caption
   * @param string $value caption
   */
  public function setName( string $value ) : void
  {
    $this->setValue( $this->cols->getNameColumn(), $value );
  }
  
  
  /**
   * Retrieve the configuration array used to initialize 
   * @return array
   */
  public function getAttributeConfig() : array
  {
    return $this->cols->getConfig();
  }
}
