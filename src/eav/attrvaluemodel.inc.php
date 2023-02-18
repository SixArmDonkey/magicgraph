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

use buffalokiwi\magicgraph\DefaultModel;
use buffalokiwi\magicgraph\property\IPropertySet;
use InvalidArgumentException;


/**
 * Attribute value model 
 */
class AttrValueModel extends DefaultModel implements IAttrValue
{
  /**
   * Columns 
   * @var IAttrValueCols
   */
  private $cols;
  
  
  /**
   * Constructor 
   * @param \buffalokiwi\magicgraph\eav\IPropertySet $props Properties
   */
  public function __construct( IPropertySet $props )
  {
    parent::__construct( $props );
    $this->cols = $props->getPropertyConfig( IAttrValueCols::class );
  }
  
  
  /**
   * Retrieve the entity id.  ie: product id or similar.
   * @return int id 
   */
  public function getEntityId() : int
  {
    return $this->getValue( $this->cols->getEntityId());
  }
  
  
  /**
   * Sets the entity id 
   * @param int $id id 
   * @return void
   * @throws InvalidArgumentException
   */
  public function setEntityId( int $id ) : void
  {
    $this->setValue( $this->cols->getEntityId(), $id );
  }
  
  
  /**
   * Retrieve the attribute id  
   * @return int id 
   */
  public function getAttributeId() : int
  {
    return $this->getValue( $this->cols->getAttributeId());
  }
  
  
  /**
   * Sets the attribute id 
   * @param int $id id 
   * @return void
   * @throws InvalidArgumentException
   */
  public function setAttributeId( int $id )
  {
    $this->setValue( $this->cols->getAttributeId(), $id );
  }
  
  
  /**
   * Retrieve the value 
   * @return string value 
   */
  public function getAttrValue() : string
  {
    return $this->getValue( $this->cols->getValue());
  }
  
  
  /**
   * Sets the value 
   * @param string $value value 
   */
  public function setAttrValue( string $value )
  {
    $this->setValue( $this->cols->getValue(), $value );
  }
}
