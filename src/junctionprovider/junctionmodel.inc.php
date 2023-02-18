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

namespace buffalokiwi\magicgraph\junctionprovider;

use buffalokiwi\magicgraph\DefaultModel;
use buffalokiwi\magicgraph\property\IPropertySet;


/**
 * A model that contains an id of a parent and an id of some target.
 * Represents a record from a database junction table.
 */
class JunctionModel extends DefaultModel implements IJunctionModel
{
  /**
   * Junction model properties 
   * @var IJunctionModelProperties
   */
  private $cols;
  
  
  /**
   * Create a new JunctionModel instance 
   * @param \buffalokiwi\magicgraph\junctionprovider\IPropertySet $properties Property set 
   * @param string $intf property set interface 
   */
  public function __construct( IPropertySet $properties, string $intf = IJunctionModelProperties::class )
  {
    parent::__construct( $properties );
    $this->cols = $properties->getPropertyConfig( $intf );
  }
  
  
  /**
   * Retrieve the id of this entry 
   * @return int id 
   */
  public function getId() : int
  {
    return $this->getValue( $this->cols->getId());
  }
  
  
  /**
   * Retrieve the id of the parent model
   * @return int parent id 
   */
  public function getParentId() : int
  {
    return $this->getValue( $this->cols->getParentId());
  }
  
  
  /**
   * Retrieve the id of the target model 
   * @return int id 
   */
  public function getTargetId() : int
  {
    return $this->getValue( $this->cols->getTargetId());
  }
  
  
  /**
   * Sets the primary key 
   * @param int $value id 
   * @return void
   */
  public function setId( int $value ) : void
  {
    $this->setValue( $this->cols->getId(), $value );
  }
  
  
  /**
   * Sets the parent id 
   * @param int $value id 
   * @return void
   */
  public function setParentId( int $value ) : void
  {
    $this->setValue( $this->cols->getParentId(), $value );
  }
  
  
  /**
   * Sets the target id 
   * @param int $value id 
   * @return void
   */
  public function setTargetId( int $value ) : void
  {
    $this->setValue( $this->cols->getTargetId(), $value );
  }
}
