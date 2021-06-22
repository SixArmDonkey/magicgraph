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

use buffalokiwi\buffalotools\types\IBitSet;
use buffalokiwi\buffalotools\types\ISet;
use buffalokiwi\buffalotools\types\SetDecorator;
use Closure;
use InvalidArgumentException;


class PropertyFlagAfterChangeHandler extends SetDecorator implements IPropertyFlags
{
  private IPropertyFlags $flags;
  private Closure $afterChange;
  
  public function __construct( IPropertyFlags $flags, Closure $afterChange )
  {
    parent::__construct( $flags );
    $this->flags = $flags;
    $this->afterChange = $afterChange;
  }
  

  /**
   * This will set all of the flags to 1 in the set
   * @return ISet $this
   */
  public function setAll() : void
  {
    parent::setAll();
    $this->dispatchAfterChange();
  }

  
  /**
   * Sets variables in the set to true
   * @param string $const Variables to set. 
   * @throws InvalidArgumentException if const is not a member if the set
   */
  public function add( string ...$const ) : void
  {
    parent::add( ...$const );
    $this->dispatchAfterChange();
  }

  
  /**
   * Sets variables in the set to false
   * @param string $const members 
   * @throws InvalidArgumentException if const is not a member if the set
   */
  public function remove( string ...$const ) : void
  {
    parent::remove( ...$const );
    $this->dispatchAfterChange();
  }

  
  /**
   * Toggle bits by member 
   * @param string ...$const One or more set member names or constants 
   * @throws InvalidArgumentException if const is not a member if the set
   */
  public function toggleMember( string ...$const ) : void
  {
    parent::toggleMember( ...$const );
    $this->dispatchAfterChange();
  }
  
   
  /**
   * Toggle some bit by index position 
   * @param int $position position 
   * @return void
   */
  public function toggleAt( int $position ) : void
  {
    parent::toggleAt( $position );
    $this->dispatchAfterChange();
  }
  
  
  /**
   * Enable some bit by index position 
   * @param int $position position 
   * @return void
   */
  public function enableAt( int $position ) : void
  {
    parent::enableAt( $position );
    $this->dispatchAfterChange();
  }
  
  
  /**
   * Disable some bit by index position 
   * @param int $position position 
   * @return void
   */
  public function disableAt( int $position ) : void
  {
    parent::disableAt( $position );
    $this->dispatchAfterChange();
  }
  
  
  /**
   * Set the internal value to a new value
   * @param int $value Value to set the mask to
   */
  public function setValue( int $value ) : void
  {
    parent::setValue( $value );
    $this->dispatchAfterChange();
  }


  /**
   * Set the value of the bitset to the value of a different bitset.
   * @param IBitSet $that Other bitset
   */
  public function setValueOf( IBitSet $that ) : void
  {
    parent::setValueOf( $that );
    $this->dispatchAfterChange();
  }


  /**
   * Clear the BitSet (sets internal value to zero)
   */
  public function clear() : void
  {
    parent::clear();
    $this->dispatchAfterChange();
  }
  

  /**
   * Toggle a permission
   * @param int $const Permission to toggle
   * @throws InvalidArgumentException if $const is not base2 
   */
  public function toggle( int $const ) : void
  {
    parent::toggle( $const );
    $this->dispatchAfterChange();
  }


  /**
   * Enables a bit in the mask
   * @param int $const bit to enable
   * @throws InvalidArgumentException if $const is not base2 
   */
  public function enable( int $const ) : void
  {
    parent::enable( $const );
    $this->dispatchAfterChange();
  }


  /**
   * Disables a bit in the mask
   * @param int $const bit to disable
   * @throws InvalidArgumentException if $const is not base2 
   */
  public function disable( int $const ) : void
  {
    parent::disable( $const );
    $this->dispatchAfterChange();
  }  
  
  
  private function dispatchAfterChange() : void
  {
    $f = $this->afterChange;
    $f();
  }
}
