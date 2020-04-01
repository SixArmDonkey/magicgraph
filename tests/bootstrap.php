<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

declare( strict_types=1 );

require_once( __DIR__ . '/../vendor/autoload.php' );

return;

//..This is for other stuff.


/*********************/
/* IOC Container     */
/*********************/
global $ioc;
$ioc = new \buffalokiwi\buffalotools\ioc\IOC();



/*********************/
/* Money Factory     */
/*********************/

$ioc->addInterface( \buffalokiwi\magicgraph\money\IMoneyFactory::class, function() {
  
  $currencies = new Money\Currencies\ISOCurrencies();
  //..Money formatter 
  $intlFmt = new \Money\Formatter\IntlMoneyFormatter( 
    new \NumberFormatter( 'en_US', \NumberFormatter::CURRENCY ), 
    $currencies );

  $decFmt = new \Money\Formatter\DecimalMoneyFormatter( $currencies );

  //..Money factory 
  //..This is used to lock the system down to a certain type of currency, 
  // and to provide an abstract wrapper for the underlying money implementation.
  return new \buffalokiwi\magicgraph\money\MoneyFactory( function( string $amount ) use($intlFmt,$decFmt) : \buffalokiwi\magicgraph\money\IMoney {
    return new \buffalokiwi\magicgraph\money\MoneyProxy( \Money\Money::USD( $amount ), $intlFmt, $decFmt );
  });
});


/**********************/
/* Database           */
/**********************/

$ioc->addInterface(buffalokiwi\magicgraph\pdo\IConnectionFactory::class, function() {
  return new \buffalokiwi\magicgraph\pdo\PDOConnectionFactory( 
    new buffalokiwi\magicgraph\pdo\MariaConnectionProperties( 
      'localhost',              //..Host
      'root',      //..User
      '',      //..Pass
      '' ), //..Database 
   function(\buffalokiwi\magicgraph\pdo\IConnectionProperties $args  ) {
     return new buffalokiwi\magicgraph\pdo\MariaDBConnection( $args, function(buffalokiwi\magicgraph\pdo\IDBConnection $c ) { $this->closeConnection($c); });
   });                
});



/*********************/
/* Magic Graph Setup */
/*********************/

//..Converts IPropertyConfig config arrays into properties
//..If creating custom propeties, this must be replaced with a custom implementation.
$configMapper = new \buffalokiwi\retailrack\magicgraph\RRConfigMapper( $ioc );

//..Factory wraps the config mapper and can combine config arrays.  
//  Uses the config mapper to produce properties.
$propertyFactory = new \buffalokiwi\magicgraph\property\PropertyFactory( $configMapper );

//..Add the config mapper to the IOC in case it's required anywhere it shouldn't be.
$ioc->addInterface( IConfigMapper::class, function() use($configMapper) { return $configMapper; });

$db = $ioc->getInstance( \buffalokiwi\magicgraph\pdo\IConnectionFactory::class );    
/* @var $db \buffalokiwi\magicgraph\pdo\IConnectionFactory */

$dbc = $db->getConnection();
/* @var $dbc \buffalokiwi\magicgraph\pdo\IDBConnection */

//..Used for saving data based on the runnable type.
$ioc->addInterface( \buffalokiwi\magicgraph\persist\ITransactionFactory::class, function() {
  return new \buffalokiwi\magicgraph\persist\DefaultTransactionFactory();
});




