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

use buffalokiwi\magicgraph\DefaultModel;
use buffalokiwi\magicgraph\property\DefaultConfigMapper;
use buffalokiwi\magicgraph\property\DefaultPropertySet;
use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\property\PropertyFactory;


/**
 * A model representing a link between an attribute group and an attribute.
 * Contains 1 id for each.
 */
class AttrGroupLinkModel extends DefaultModel implements IAttrGroupLink
{
  /**
   * Column definitions
   * @var IAttrGroupLinkCols
   */
  private $cols;
  
  
  /**
   * Create a new AttrGroupLinkModel instance.   
   * @param \buffalokiwi\magicgraph\eav\IPropertySet $properties
   */
  public function __construct( IPropertySet $properties = null )
  {
    parent::__construct(( $properties == null ) 
      ? new DefaultPropertySet(( new PropertyFactory( new DefaultConfigMapper(), new AttrGroupLinkProperties()))->getProperties())
      : $properties );
    
    $this->cols = $this->getPropertySet()->getPropertyConfig( IAttrGroupLinkCols::class );
  }
  
  
  /**
   * Retrieve the link id 
   * @return int id 
   */
  public function getId() : int
  {
    return $this->getValue( $this->cols->getId());
  }
  
  
  /**
   * Get the attribute group id
   * @return int id 
   */
  public function getGroupId() : int
  {
    return $this->getValue( $this->cols->getGroupId());
  }
  
  
  /**
   * Get the attribute id 
   * @return int id 
   */
  public function getAttributeId() : int
  {
    return $this->getValue( $this->cols->getAttributeId());
  }
  
  
  /**
   * Sets the attribute group id
   * @param int $id id 
   * @return void
   */
  public function setGroupId( int $id ) : void
  {
    $this->setValue( $this->cols->getGroupId(), $id );
  }
  
  
  /**
   * Sets the attribute id  
   * @param int $id id 
   * @return void
   */
  public function setAttributeId( int $id ) : void
  {
    $this->setValue( $this->cols->getAttributeId(), $id );
  }
}
