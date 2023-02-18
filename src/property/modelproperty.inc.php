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
    
    //..Doing this will make relationship providers not save the model edits.
    //..I typicallly remove commented code, but I wanted to leave this here 
    //  in case I get a "good idea" one of these days.
    /*
    $value = $this->getValueAsModel();
    if ( $value instanceof IModel )
    {
      $value->clearEditFlags();
    }
    */
  }  

  
  /**
   * Test to see if some value is valid 
   * @param mixed $value
   * @throws ValidationException 
   */
  protected function validatePropertyValue( $value ) : void
  {
    if ( $this->isUseNull() && $value === null )
      return;
    
    if ( !is_a( $value, $this->getClass()))
      throw new ValidationException( sprintf( 'Value "%s" for property %s must be an instance of %s Got %s %s', (string)$value, $this->getName(), $this->getClass(), gettype( $value ), ( is_object( $value )) ? ' of class ' . get_class( $value ) : '' ));
       
    $cur = $this->getValueAsModel();
    
    if ( empty( $value ) || !is_a( $value, $this->getClass()))
    {
      throw new ValidationException( sprintf( 'Value "%s" for property %s must be an instance of %s Got %s %s', 
        (string)$value, $this->getName(), $this->getClass(), gettype( $value ), 
        ( is_object( $value )) ? ' of class ' . get_class( $value ) : '' ));
    }
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
  protected function initValue() : mixed
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
  protected function preparePropertyValue( $value ) : mixed
  {
    if ( $this->isUseNull() && $value === null )
    {
      return $value;
    }
    else if ( !( $value instanceof IModel ))
    {      
      throw new ValidationException( sprintf( 'Value for property %s must be an instance of %s Got %s %s', $this->getName(), $this->getClass(), gettype( $value ), ( is_object( $value )) ? ' of class ' . get_class( $value ) : '' ));
    }
    else
    {
      
      return $value;
    }
  }
}
