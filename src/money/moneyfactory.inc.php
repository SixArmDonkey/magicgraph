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


namespace buffalokiwi\magicgraph\money;

use Closure;
use Exception;

/**
 * A factory for creating IMoney instances 
 */
class MoneyFactory implements IMoneyFactory 
{
  /**
   * Money supplier.
   * f( string $amount ) : IMoney
   * @var Closure
   */
  private $supplier;
  
  
  /**
   * Create a new MoneyFactory instance 
   * @param Closure $moneySupplier f( string $amount ) : IMoney
   */
  public function __construct( Closure $moneySupplier )
  {
    $this->supplier = $moneySupplier;
  }
  
  
  /**
   * Retrieve a new IMoney instance of a certain amount.
   * 
   * This expects $amount to be in cents or the smallest currency unit.
   * If a decimal point '.' is encountered, the amount is multiplied by 100 
   * before creating the Money object.  This expects a scale of 2 and any additional digits are rounded off.
   * 
   * @param string $amount Amount 
   * @return \buffalokiwi\money\IMoney Money 
   */
  public function getMoney( string $amount ) : IMoney
  {
    if ( strpos( $amount, '.' ) !== false )
    {
      $amount = (string)round(floatval( $amount ) * 100);
    }
    $c = $this->supplier;
    $m = $c( $amount );
    if ( !( $m instanceof IMoney ))
      throw new Exception( 'MoneyFactory supplier callback did not return an instance of IMoney.  got ' . gettype( $m ) . (( is_object( $m )) ? ' of class ' . get_class( $m ) : '' ) . '.' );
    
    return $m;
  }
}
