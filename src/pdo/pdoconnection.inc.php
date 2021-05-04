<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 *  DB
 * @author John Quinn
 */

declare( strict_types=1 );

namespace buffalokiwi\magicgraph\pdo;

use buffalokiwi\magicgraph\DBException;
use Closure;
use Exception;
use Generator;
use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;




/**
 * The PDO Database connection wrapper.
 * @todo Write tests.
 */
abstract class PDOConnection extends PDO implements IPDOConnection
{
  /**
   * Connection properties
   * @var IConnectionProperties
   */
  protected $props;

  /**
   * The last sql statement used
   * @var string
   */
  protected $lastStatement = '';

  /**
   * Last options used for a prepared statement
   * @var array
   */
  protected $lastOpts = [];
  
  /**
   * OnClose 
   * f( IDBConnection )
   * @var Closure 
   */
  private $onClose;

  /**
   * Unique id for this instance 
   * @var string
   */
  private $uniqueId;
  
  /**
   * The autocommit driver "feature" isn't really doing what I want here.
   * 
   * If PDO::ATTR_AUTOCOMMIT is false, then a transaction is automatically started.  This behavior is different in 
   * php8 than it was in prior version.  Previously, setting autocommit to false required beginTransaction() to be called
   * to start the initial tranaction.  In Retail Rack, there is the unit of work and transaction factory, which requires
   * an accurate result from inTransaction().  When autocommit is false, inTransaction() returns true.  This is totally bullshit, and potentially untrue.
   * 
   * 
   * @var type 
   */
  private $inTransaction = false;
  
  
  /**
   * Select the current database
   * @param string $db Database name
   * @abstract 
   * @deprecated Remove this or move to SQL specific connection
   */
  public abstract function selectdb( string $db ) : void;


  /**
   * Returns the current database being used
   * @return string Current database name
   * @abstract
   * @deprecated Remove this or move to SQL specific connection
   */
  public abstract function curdb() : string;

  
  /**
   * Execute a delete query for a record using a compound key.
   * @param string $table table name
   * @param array $pkPairs primary key to value pairs 
   * @param int $limit limit
   * @return int affected rows
   * @throws InvalidArgumentExcepton if table or col or id are empty or if col
   * contains invalid characters or if limit is not an integer or is less than
   * one
   * @throws DBException if there is a problem executing the query
   * @abstract
   */
  public abstract function delete( string $table, array $pkCols, int $limit = 1 ) : int;

  
  /**
   * Build an update query using a prepared statement.
   * @param string $table Table name
   * @param array $pkPairs list of [primary key => value] for locating records to update.
   * @param array $pairs Column names and values map
   * @param int $limit Limit to this number
   * @return int the number of affected rows
   * @throws InvalidArgumentException
   * @throws DBException
   * @abstract
   */
  public abstract function update( string $table, array $pkPairs, array $pairs, int $limit = 1 ) : int;
  
  
  /**
   * Build an insert query using a prepared statement.
   * This will work for most queries, but if you need to do something
   * super complicated, write your own sql...
   *
   *
   * @param string $table Table name
   * @param array $pairs Column names and values map
   * @return int last insert id for updates
   * @throws InvalidArgumentException
   * @throws DBException
   * @abstract
   */
  public abstract function insert( string $table, array $pairs ) : string;  
  
  
  
  
  /**
   * Create a new PDOConnection
   * @param IConnectionProperties $args properties
   * @param Closure|null $onClose A function assigned by the connection manager
   * that will mark the connection as closed in the factory.
   * f( IDBConnection )
   * @throws PDOException if there is an issue
   */
  public function __construct( IConnectionProperties $args, ?Closure $onClose = null )
  {
    parent::__construct(
      $args->dsn(),
      $args->getUser(),
      $args->getPassword(),
      $args->getOptions()
    );
    
    $this->onClose = $onClose;
    
    $this->props = $args;
   
    //..This should be unique enough. I hope.
    $this->uniqueId = bin2hex( random_bytes( 16 ));    
  }
  
  
  /**
   * A unique connection id generated each time a new instance of some IDBConnection is
   * created.  Implementations MUST ensure the returned value is unique.
   * @return string id
   */
  public function getConnectionId() : string
  {
    return $this->uniqueId;
  }
  
  
  
  /**
	 * Initiates a transaction
	 * <p>Turns off autocommit mode. While autocommit mode is turned off, changes made to the database via the PDO object instance are not committed until you end the transaction by calling <code>PDO::commit()</code>. Calling <code>PDO::rollBack()</code> will roll back all changes to the database and return the connection to autocommit mode.</p><p>Some databases, including MySQL, automatically issue an implicit COMMIT when a database definition language (DDL) statement such as DROP TABLE or CREATE TABLE is issued within a transaction. The implicit COMMIT will prevent you from rolling back any other changes within the transaction boundary.</p>
	 * @return bool <p>Returns <b><code>TRUE</code></b> on success or <b><code>FALSE</code></b> on failure.</p>
	 * @link http://php.net/manual/en/pdo.begintransaction.php
	 * @see PDO::commit(), PDO::rollBack()
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.1.0
	 */
	public function beginTransaction(): bool
  {
    try {
      return parent::beginTransaction();
    } finally {
      $this->inTransaction = true;
    }
  }
  
  
	/**
	 * Commits a transaction
	 * <p>Commits a transaction, returning the database connection to autocommit mode until the next call to <code>PDO::beginTransaction()</code> starts a new transaction.</p>
	 * @return bool <p>Returns <b><code>TRUE</code></b> on success or <b><code>FALSE</code></b> on failure.</p>
	 * @link http://php.net/manual/en/pdo.commit.php
	 * @see PDO::beginTransaction(), PDO::rollBack()
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.1.0
	 */
	public function commit(): bool
  {
    try {
      return parent::commit();
    } finally {
      $this->inTransaction = false;
    }
  }
  

	/**
	 * Fetch the SQLSTATE associated with the last operation on the database handle
	 * @return string <p>Returns an SQLSTATE, a five characters alphanumeric identifier defined in the ANSI SQL-92 standard. Briefly, an SQLSTATE consists of a two characters class value followed by a three characters subclass value. A class value of 01 indicates a warning and is accompanied by a return code of SQL_SUCCESS_WITH_INFO. Class values other than '01', except for the class 'IM', indicate an error. The class 'IM' is specific to warnings and errors that derive from the implementation of PDO (or perhaps ODBC, if you're using the ODBC driver) itself. The subclass value '000' in any class indicates that there is no subclass for that SQLSTATE.</p><p><b>PDO::errorCode()</b> only retrieves error codes for operations performed directly on the database handle. If you create a PDOStatement object through <code>PDO::prepare()</code> or <code>PDO::query()</code> and invoke an error on the statement handle, <b>PDO::errorCode()</b> will not reflect that error. You must call <code>PDOStatement::errorCode()</code> to return the error code for an operation performed on a particular statement handle.</p><p>Returns <b><code>NULL</code></b> if no operation has been run on the database handle.</p>
	 * @link http://php.net/manual/en/pdo.errorcode.php
	 * @see PDO::errorInfo(), PDOStatement::errorCode(), PDOStatement::errorInfo()
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.1.0
	 */
	public function errorCode(): string
  {
    return parent::errorCode();
  }
  
  

	/**
	 * Checks if inside a transaction
	 * <p>Checks if a transaction is currently active within the driver. This method only works for database drivers that support transactions.</p>
	 * @return bool <p>Returns <b><code>TRUE</code></b> if a transaction is currently active, and <b><code>FALSE</code></b> if not.</p>
	 * @link http://php.net/manual/en/pdo.intransaction.php
	 * @since PHP 5 >= 5.3.3, Bundled pdo_pgsql, PHP 7
	 */
	public function inTransaction(): bool
  {
    //..The behavior of inTransaction does not seem reliable.
    //return parent::inTransaction();
    
    return $this->inTransaction;
  }
  

	/**
	 * Fetch extended error information associated with the last operation on the database handle
	 * @return array <p><b>PDO::errorInfo()</b> returns an array of error information about the last operation performed by this database handle. The array consists of the following fields:</p>   Element Information     0 SQLSTATE error code (a five characters alphanumeric identifier defined in the ANSI SQL standard).   1 Driver-specific error code.   2 Driver-specific error message.   <p><b>Note</b>:</p><p>If the SQLSTATE error code is not set or there is no driver-specific error, the elements following element 0 will be set to <b><code>NULL</code></b>.</p> <p><b>PDO::errorInfo()</b> only retrieves error information for operations performed directly on the database handle. If you create a PDOStatement object through <code>PDO::prepare()</code> or <code>PDO::query()</code> and invoke an error on the statement handle, <b>PDO::errorInfo()</b> will not reflect the error from the statement handle. You must call <code>PDOStatement::errorInfo()</code> to return the error information for an operation performed on a particular statement handle.</p>
	 * @link http://php.net/manual/en/pdo.errorinfo.php
	 * @see PDO::errorCode(), PDOStatement::errorCode(), PDOStatement::errorInfo()
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.1.0
	 */
	public function errorInfo(): array
  {
    return parent::errorInfo();
  }
  
  


	/**
	 * Returns the ID of the last inserted row or sequence value
	 * <p>Returns the ID of the last inserted row, or the last value from a sequence object, depending on the underlying driver. For example, PDO_PGSQL requires you to specify the name of a sequence object for the <code>name</code> parameter.</p><p><b>Note</b>:</p><p>This method may not return a meaningful or consistent result across different PDO drivers, because the underlying database may not even support the notion of auto-increment fields or sequences.</p>
	 * @param string $name <p>Name of the sequence object from which the ID should be returned.</p>
	 * @return string <p>If a sequence name was not specified for the <code>name</code> parameter, <b>PDO::lastInsertId()</b> returns a string representing the row ID of the last row that was inserted into the database.</p><p>If a sequence name was specified for the <code>name</code> parameter, <b>PDO::lastInsertId()</b> returns a string representing the last value retrieved from the specified sequence object.</p><p>If the PDO driver does not support this capability, <b>PDO::lastInsertId()</b> triggers an <i>IM001</i> SQLSTATE.</p>
	 * @link http://php.net/manual/en/pdo.lastinsertid.php
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.1.0
	 */
	public function lastInsertId( $seqname = null )
  {
    return parent::lastInsertId( $seqname );
  }
  
  
	/**
	 * Prepares a statement for execution and returns a statement object
	 * <p>Prepares an SQL statement to be executed by the <code>PDOStatement::execute()</code> method. The SQL statement can contain zero or more named (:name) or question mark (&#63;) parameter markers for which real values will be substituted when the statement is executed. You cannot use both named and question mark parameter markers within the same SQL statement; pick one or the other parameter style. Use these parameters to bind any user-input, do not include the user-input directly in the query.</p><p>You must include a unique parameter marker for each value you wish to pass in to the statement when you call <code>PDOStatement::execute()</code>. You cannot use a named parameter marker of the same name more than once in a prepared statement, unless emulation mode is on.</p><p><b>Note</b>:</p><p>Parameter markers can represent a complete data literal only. Neither part of literal, nor keyword, nor identifier, nor whatever arbitrary query part can be bound using parameters. For example, you cannot bind multiple values to a single parameter in the IN() clause of an SQL statement.</p><p>Calling <b>PDO::prepare()</b> and <code>PDOStatement::execute()</code> for statements that will be issued multiple times with different parameter values optimizes the performance of your application by allowing the driver to negotiate client and/or server side caching of the query plan and meta information, and helps to prevent SQL injection attacks by eliminating the need to manually quote the parameters.</p><p>PDO will emulate prepared statements/bound parameters for drivers that do not natively support them, and can also rewrite named or question mark style parameter markers to something more appropriate, if the driver supports one style but not the other.</p>
	 * @param string $statement <p>This must be a valid SQL statement template for the target database server.</p>
	 * @param array $driver_options <p>This array holds one or more key=&gt;value pairs to set attribute values for the PDOStatement object that this method returns. You would most commonly use this to set the <i>PDO::ATTR_CURSOR</i> value to <i>PDO::CURSOR_SCROLL</i> to request a scrollable cursor. Some drivers have driver specific options that may be set at prepare-time.</p>
	 * @return PDOStatement <p>If the database server successfully prepares the statement, <b>PDO::prepare()</b> returns a PDOStatement object. If the database server cannot successfully prepare the statement, <b>PDO::prepare()</b> returns <b><code>FALSE</code></b> or emits PDOException (depending on error handling).</p><p><b>Note</b>:</p><p>Emulated prepared statements does not communicate with the database server so <b>PDO::prepare()</b> does not check the statement.</p>
	 * @link http://php.net/manual/en/pdo.prepare.php
	 * @see PDO::exec(), PDO::query(), PDOStatement::execute()
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.1.0
	 */
	public function prepareStatement(string $statement, array $driver_options = [] ): PDOStatement
  {
    return parent::prepare( $statement, $driver_options );
  }
  
  
  /**
   * Access the args
   * @return IConnectionProperties args
   */
  public function getProperties() : IConnectionProperties
  {
    return $this->props;
  }
    
  
  /**
   * Set auto commit if supported by the driver.
   * @param bool $on on or off 
   * @return void
   */
  public function setAutoCommit( bool $on ) : void
  {
    if ( $this->inTransaction )
      throw new \Exception( 'A transaction is currently in progress.  Please commit or rollback the current transaction prior to changing the autocommit value' );
    
    $this->setAttribute( PDO::ATTR_AUTOCOMMIT, $on );
  }


  /**
   * Close the database connection
   * Calls the onClose callback.
   * Kind of pointless?
   */
  public function close() : void
  {
    if ( $this->onClose instanceof Closure )
    {
      //..clear the reference
      $this->onClose->call( $this );
    }
  }

  

	/**
	 * Rolls back a transaction
	 * <p>Rolls back the current transaction, as initiated by <code>PDO::beginTransaction()</code>.</p><p>If the database was set to autocommit mode, this function will restore autocommit mode after it has rolled back the transaction.</p><p>Some databases, including MySQL, automatically issue an implicit COMMIT when a database definition language (DDL) statement such as DROP TABLE or CREATE TABLE is issued within a transaction. The implicit COMMIT will prevent you from rolling back any other changes within the transaction boundary.</p>
	 * @return bool <p>Returns <b><code>TRUE</code></b> on success or <b><code>FALSE</code></b> on failure.</p>
	 * @link http://php.net/manual/en/pdo.rollback.php
	 * @see PDO::beginTransaction(), PDO::commit()
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.1.0
	 */
	public function rollBack(): bool
  {
    try {
      return parent::rollBack();
    } finally {
      $this->inTransaction = false;
    }      
  }
  



  /**
   * Query the database
   * @param string $statement statement to query
   * @return int affected rows
   * @throws DBException
   */
  public function exec( $statement ) : int
  {
    $this->lastStatement = $statement;

    try {

      $res = parent::exec( $statement );
    } catch( PDOException $e ) {
      throw new DBException( $e->getMessage(), $e->getCode(), $e, $this->lastStatement );
    }

    return $res;
  }


  /**
   * Used to execute a query that returns a result set
   * @param $statement SQL statement to use
   * @return Generator yielded results 
   * @throws DBException if there is one
   */
  public function executeQuery( string $statement ) : Generator
  {
    $this->lastStatement = $statement;

    try {
      $res = parent::query( $statement );
    } catch( PDOException $e ) {
      throw new DBException( $e->getMessage(), $e->getCode(), $e, $this->lastStatement );
    }

    if ( !$res )
    {
      throw new DBException( 'This query did not return a result set.  you may need to use exec().  '
        . $this->errorInfo(), $this->errorCode());
    }
    
    while( $row = $res->fetch( PDO::FETCH_ASSOC ))
    {
      yield $row;
    }
  }


  /**
   * Execute a sql statement that has multiple result sets
   * ie: a stored procedure that has multiple selects, or one of those snazzy
   * subquery statements
   * @param string $sql SQL statement to execute
   * @return Generator array results
   * @throws DBException if there is one
   */
  public function multiSelect( string $sql, array $bindings = [] ) : Generator
  {
    try {
      if ( !empty( $bindings ))
        $stmt = $this->prepareAndExec( $sql, $this->prepareOptions( $bindings ));
      else
        $stmt = parent::query( $sql );

      $out = array();
      do
      {
        $rowset = $stmt->fetchAll( PDO::FETCH_ASSOC );
        if ( $rowset )
          yield $rowset;

      } while ( $stmt->nextRowset());

    } catch( PDOException $e ) {
      throw new DBException( $e->getMessage(), $e->getCode(), $e, $this->lastStatement );
    }

    return $out;
  }


  /**
   * Retrieve the last sql statement that was used
   * @return string last statement
   */
  public function getLastStatement() : string
  {
    return $this->lastStatement;
  }


  /**
   * Retrieve the last set of options used
   * @return array opts
   */
  public function getLastOpts() : array 
  {
    return $this->lastOpts;
  }

  
  public function forwardCursor( string $statement, $options = null, $scroll = false ) : Generator
  {
    if ( empty( $statement ))
      throw new InvalidArgumentException( '$statement must not be empty' );
    
    $opt = $this->prepareOptions( $options );
    
    $this->lastStatement = $statement;
    $this->lastOpts = $opt;

    try {
      $stmt = parent::prepare( $statement, array( PDO::ATTR_CURSOR => ( $scroll ) ? PDO::CURSOR_SCROLL : PDO::CURSOR_FWDONLY ));
      $stmt->execute( $opt );

      while (( $row = $stmt->fetch( PDO::FETCH_ASSOC, PDOConnection::FETCH_ORI_NEXT )) !== false )
      {
        yield $row;
      }

      $stmt->closeCursor();
      
    } catch( PDOException $e ) {
      $this->lastStatement = $statement . '(' . implode( ',', $opt ) . ')';
      $this->lastOpts = $opt;
      throw new DBException( $e->getMessage(), $e->getCode(), $e, $this->lastStatement, $opt );
    }    
  }

  /**
   * Execute a prepared statement.
   * @param string $statement sql
   * @param array|object $opt a map of values to bind to the statement
   * @return mixed if fetch and multi are true, this returns an array with everything in it.
   * If multi is false and fetch is true, this returns a generator.  if fetch is false, this
   * returns the row count.
   */
  protected function prepareAndExec( string $statement, $options = null ) : PDOStatement
  {
    $this->lastStatement = $statement;
    $this->lastOpts = $options;
    try {
      $stmt = parent::prepare( $statement, array( PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ));
      
      $stmt->execute( $options );
      
      return $stmt;
    } catch( PDOException $e ) {
      $this->lastStatement = $statement . '(' . implode( ',', $options ) . ')';
      $this->lastOpts = $options;

      $code = ( !is_int( $e->getCode())) ? 0 : $code;
        
      throw new DBException( $e->getMessage() . '. code: ' . $e->getCode(), $code, $e, $this->lastStatement, $options );
    }
  }
  
  
  /**
   * Select some stuff from some database
   * @param string $statement sql statement
   * @param type $opt Bindings for prepared statement.  This can be an object or an array 
   */ 
  public function select( string $statement, $opt = null ) : \Generator
  {
    //trigger_error( $statement . ' :: ' . implode( ',', $opt ), E_USER_NOTICE );
    $stmt = $this->prepareAndExec( $statement, $this->prepareOptions( $opt ));
    while (( $row = $stmt->fetch( PDO::FETCH_ASSOC, PDOConnection::FETCH_ORI_NEXT )) !== false )
    {
      yield $row;
    }
  }

  
  public function selectMulti( string $statement, $opt = null )
  {
    $stmt = $this->prepareAndExec( $statement, $this->prepareOptions( $opt ));
    return $this->getMultipleBufferedResultSets( $stmt );
  }  
  
  
  public function execute( string $statement, $opt = null ) : int
  {
    return $this->prepareAndExec( $statement, $this->prepareOptions( $opt ))->rowCount();
  }
  
  

  /**
   * Implode an array for a prepared statement
   * @param array $val value to implode
   * @return string imploded value (value will be wrapped in parentheses)
   */
  protected function pimplode( array $val ) : string
  {
    return '(' . implode( ',', array_fill( 0, sizeof( $val ), '?' )) . ')';
  }


  /**
   * Turns a column with functions in it into a column name and function wrapper for the value
   * ie:
   * col:UNHEX:MD5 will output
   * "col" for the column name
   * and
   * "UNHEX(MD5(?))" for the value (without quotes)
   *
   * @param string $col Column name
   * @param mixed $aVal the value to be used
   * @return array( col, val, (bool)has functions in value )
   * @throws InvalidArgumentException if col is not a string or empty
   */
  protected final function getColFunc( string $col, $aVal ) : array 
  {
    if ( empty( $col ))
      throw new InvalidArgumentException( 'Invalid column' );

    //..Split
    $colData = explode( ':', $col );
    //..Buffer
    $val = '';

    //..Number of functions (>1)
    $size = sizeof( $colData );

    //..check for functions
    if ( $size > 1 )
    {
      //..There are

      //..Number of parenthesess
      $ct = 0;

      //..Loop the functions
      for ( $j = 1; $j < $size; $j++ )
      {
        //..Add an opening
        $val .= $colData[$j] . '(';

        //..Increment the total
        $ct++;
      }

      //..Add the placeholder
      if ( !empty( $aVal ))
        $val .= '?';

      //..Close the parentheses
      for ( $j = 0; $j < $ct; $j++ )
      {
        $val .= ')';
      }
    }
    else
    {
      //..Just a single value
      $val .= '?';
    }

    //..Return the column and value
    return array( $colData[0], $val, $size > 1 );
  }


  /**
   * Get keys, values and params for a prepared statement being built.
   * @param array $pairs pairs from a build method
   * @return array( array, array, array ) keys,vals,params
   * @throws InvalidArgumentException
   */
  protected final function getKVP( array $pairs ) : array 
  {
    if ( empty( $pairs ))
      return array( array(), array(), array());

    //..Check the columns
    //..And i guess make the lists while we're looping
    $keys = array();
    $vals = array();

    //..An array of prepared statement parameters to bind
    $params = array();

    foreach( $pairs as $col => $val )
    {
      //..Check for functions in the column name
      list( $col, $newVal, $hasFunc ) = $this->getColFunc( $col, $val );

      //..Check the column name to see if it's valid
      if ( !$this->isSafe( $col ))
        throw new InvalidArgumentException( $col . ' is an invalid column value' );

      $keys[] = $col;

      if ( $hasFunc )
      {
        if ( empty( $val ))
          $vals[] = $newVal;
        else
        {
          $vals[] = $newVal;
          $params[] = $val;
        }
      }
      else
      {
        $vals[] = '?';
        $params[] = $val;
      }
    }

    return array( $keys, $vals, $params );
  }
  
  
  /**
   * Detect if a string is only [a-zA-Z0-9_]
   * @param string $s String to check
   * @return boolean is letters
   */
  protected final function isSafe( string $s ) : bool
  {
    return ( preg_match( '/^([a-zA-Z0-9_]+)$/', $s ) == 1 );
  }
  

  /**
   * Prepare the options argument for use in a prepared statement 
   * @param array|object $options
   * @return array
   */
  protected function prepareOptions( $options ) : array 
  {
    if ( !is_array( $options ) && ( !is_object( $options )))
      $options = array();
    else if ( is_object( $options ))
    {
      $o = array();
      foreach( $options as $k => $v )
      {
        $o[$k] = $v;
      }
      return $o;
    }
    
    return $options;
  }
  
  
  private function getMultipleBufferedResultSets( PDOStatement $stmt ) : array 
  {
    $out = [];
    do
    {
      $rowset = $stmt->fetchAll( PDO::FETCH_ASSOC );
      if ( $rowset )
        $out[] = $rowset;
    } while ( $stmt->nextRowset());

    return $out;    
  }
}
