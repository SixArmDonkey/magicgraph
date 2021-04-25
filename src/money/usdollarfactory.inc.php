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

use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;


class USDollarFactory
{
  private static ?IMoneyFactory $factory = null;
  
  
  public static function getInstance() : IMoneyFactory
  {
    if ( self::$factory == null )
      self::$factory = self::createFactory();
    
    return self::$factory;    
  }
  
  
  private static function createFactory()
  {
    $currencies = new ISOCurrencies();
    //..Money formatter 
    $intlFmt = new IntlMoneyFormatter( 
      new NumberFormatter( 'en_US', NumberFormatter::CURRENCY ), 
      $currencies );

    $decFmt = new DecimalMoneyFormatter( $currencies );

    //..Money factory 
    //..This is used to lock the system down to a certain type of currency, 
    // and to provide an abstract wrapper for the underlying money implementation.
    return new MoneyFactory( function( string $amount ) use($intlFmt,$decFmt) : IMoney {
      return new MoneyProxy( Money::USD( $amount ), $intlFmt, $decFmt );
    });    
  }  
}
