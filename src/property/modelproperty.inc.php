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

namespace buffalokiwi\magicgraph\property;

use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\ValidationException;


/**
 * Edited flag is controlled by the result of method IModel::hasEdits() of the underlying model.
 */
class ModelProperty extends ObjectProperty implements IModelProperty 
{
  public function __construct( IObjectPropertyBuilder $builder )
  {
    parent::__construct( $builder );
  }
  
  
  public function getValueAsModel() : ?IModel
  {
    return $this->getValue();
  }
  
  
  /**
   * Checks the internal edited flag.
   * This is set to true when setValue() is called 
   * @return bool is edited 
   */
  public function isEdited() : bool
  {
    $value = $this->getValueAsModel();
    
    //..This handles when the value is set via IProperty::setValue()
    if ( parent::isEdited())
      return true;
    
    //..Checking for individual property edits on the nested model
    if ( $value instanceof IModel )
    {
      return $value->hasEdits();
    }
    
    return false;
  }  
  
  
  /**
   * Sets the internal edited flag to false 
   * @return void
   */
  public function clearEditFlag() : void
  {
    parent::clearEditFlag();
    $value = $this->getValueAsModel();
    if ( $value instanceof IModel )
    {
      $value->clearEditFlags();
    }
  }  
  
  
  protected function validatePropertyValue( $value ) : void
  {
    $useNull = $this->getFlags()->hasVal( IPropertyFlags::USE_NULL );
    if ( $useNull && $value === null )
      return;
    
    $cur = $this->getValueAsModel();
    if ( $useNull && $cur == null )
      return;
    
    if ( empty( $value ) || !is_a( $value, $this->getClass()))
      throw new ValidationException( sprintf( 'Value "%s" for property %s must be an instance of %s Got %s %s', (string)$value, $this->getName(), $this->getClass(), gettype( $value ), ( is_object( $value )) ? ' of class ' . get_class( $value ) : '' ));
  }
  
  
  /**
   * Initialize the value property with some value.
   * This will be immediately overwritten by the initial call to reset(), but 
   * is useful for when value is some object type that must not be null. 
   * 
   * Returns null by default.
   * 
   * @return mixed value 
   * @throws \Exception 
   */
  protected function initValue()
  {
    return parent::initValue();
  }
  
  
  /**
   * Called after the behavior callback setter, and BEFORE validate.
   * Override this to prepare data for validation.
   * 
   * DO NOT USE THIS TO COMMIT DATA.
   * 
   * @param mixed $value Value being set.
   * @return mixed value to validate and set
   */
  protected function preparePropertyValue( $value )
  {
    if ( !( $value instanceof IModel ))
      return $this->getValueAsModel();
    else
      return $value;
  }
}
