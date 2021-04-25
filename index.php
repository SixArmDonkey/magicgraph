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

error_reporting( E_ALL );
ini_set( 'display_errors', 'On' );

include_once( __DIR__ . '/vendor/autoload.php' );

//      'testenum' => [
//        self::TYPE => IPropertyType::TRTENUM,
//        self::VALUE => 'Test2',
//        self::CONFIG => ['Test1','Test2']
//      ]


use buffalokiwi\magicgraph\pdo\IConnectionProperties;
use buffalokiwi\magicgraph\pdo\MariaConnectionProperties;
use buffalokiwi\magicgraph\pdo\MariaDBConnection;
use buffalokiwi\magicgraph\pdo\PDOConnectionFactory;
use buffalokiwi\magicgraph\persist\InlineSQLRepo;
use buffalokiwi\magicgraph\persist\IRunnable;
use buffalokiwi\magicgraph\persist\ISQLRunnable;
use buffalokiwi\magicgraph\persist\MySQLRunnable;
use buffalokiwi\magicgraph\persist\MySQLTransaction;
use buffalokiwi\magicgraph\persist\TransactionFactory;
use buffalokiwi\magicgraph\property\DefaultStringProperty;
use buffalokiwi\magicgraph\property\PrimaryIntegerProperty;
use buffalokiwi\retailrack\payment\Transaction;

//..Create a database connection factory for some MySQL database
$dbFactory = new PDOConnectionFactory( 
  new MariaConnectionProperties( 
    'localhost',    //..Host
    'root',         //..User
    '',             //..Pass
    'retailrack' ), //..Database 
  function(IConnectionProperties $args  ) {
    return new MariaDBConnection( $args );
});


//..Create a quick test repository for a table named "inlinetest", with two columns id (int,primary,autoincrement) and name(varchar).
$repo = new InlineSQLRepo( 
  'inlinetest', 
  $dbFactory->getConnection(),
  new PrimaryIntegerProperty( 'id' ),
  new DefaultStringProperty( 'name' )
);

//..Create a new model and set the name property value to "test"
$model = $repo->create([]);
$model->name = 'test';


//..Create a new transaction factory
//..The supplied map is used within the TransactionFactory::createTransactions() method, and will generate ITransaction
//  instances of the appropriate type based on a predefined subclass of IRunnable 
//..Instances passed to TransactionFactory must be ordered so that the most generic IRunnable instances are last.
$tf = new TransactionFactory([
  //..Supplying ISQLRunnable instances will generate instaces of MySQLTransaction
  ISQLRunnable::class => function( IRunnable ...$tasks ) { return new MySQLTransaction( ...$tasks ); },
  //..Supplying instances of IRunnable will generate a Transaction instance
  IRunnable::class => function( IRunnable ...$tasks ) { return new Transaction( ...$tasks ); }
]);

//..Execute a mysql transaction
//..This will use a database transaction to save the model
//..If any exceptions are thrown by the supplied closure, then rollback is called.  Otherwise, commit is called 
//..upon successful completion of the closure
$tf->execute( new MySQLRunnable( $repo, function() use($repo, $model) {
  $repo->save( $model );  
}));

$tf->execute( new MySQLRunnable( $repo, function() use($repo, $model) {
  $repo->save( $model );  
  throw new \Exception( 'No save for you' );
}));


  
      
