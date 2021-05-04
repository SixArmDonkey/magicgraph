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
namespace buffalokiwi\magicgraph\pdo;

use buffalokiwi\magicgraph\DBException;
use Generator;
use PDO;
use PDOStatement;


/**
 * A copy of the method declarations from the PDO class.
 */
interface IPDO
{
	/**
	 * Initiates a transaction
	 * <p>Turns off autocommit mode. While autocommit mode is turned off, changes made to the database via the PDO object instance are not committed until you end the transaction by calling <code>PDO::commit()</code>. Calling <code>PDO::rollBack()</code> will roll back all changes to the database and return the connection to autocommit mode.</p><p>Some databases, including MySQL, automatically issue an implicit COMMIT when a database definition language (DDL) statement such as DROP TABLE or CREATE TABLE is issued within a transaction. The implicit COMMIT will prevent you from rolling back any other changes within the transaction boundary.</p>
	 * @return bool <p>Returns <b><code>TRUE</code></b> on success or <b><code>FALSE</code></b> on failure.</p>
	 * @link http://php.net/manual/en/pdo.begintransaction.php
	 * @see PDO::commit(), PDO::rollBack()
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.1.0
	 */
	public function beginTransaction(): bool;

	/**
	 * Commits a transaction
	 * <p>Commits a transaction, returning the database connection to autocommit mode until the next call to <code>PDO::beginTransaction()</code> starts a new transaction.</p>
	 * @return bool <p>Returns <b><code>TRUE</code></b> on success or <b><code>FALSE</code></b> on failure.</p>
	 * @link http://php.net/manual/en/pdo.commit.php
	 * @see PDO::beginTransaction(), PDO::rollBack()
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.1.0
	 */
	public function commit(): bool;

	/**
	 * Fetch the SQLSTATE associated with the last operation on the database handle
	 * @return string <p>Returns an SQLSTATE, a five characters alphanumeric identifier defined in the ANSI SQL-92 standard. Briefly, an SQLSTATE consists of a two characters class value followed by a three characters subclass value. A class value of 01 indicates a warning and is accompanied by a return code of SQL_SUCCESS_WITH_INFO. Class values other than '01', except for the class 'IM', indicate an error. The class 'IM' is specific to warnings and errors that derive from the implementation of PDO (or perhaps ODBC, if you're using the ODBC driver) itself. The subclass value '000' in any class indicates that there is no subclass for that SQLSTATE.</p><p><b>PDO::errorCode()</b> only retrieves error codes for operations performed directly on the database handle. If you create a PDOStatement object through <code>PDO::prepare()</code> or <code>PDO::query()</code> and invoke an error on the statement handle, <b>PDO::errorCode()</b> will not reflect that error. You must call <code>PDOStatement::errorCode()</code> to return the error code for an operation performed on a particular statement handle.</p><p>Returns <b><code>NULL</code></b> if no operation has been run on the database handle.</p>
	 * @link http://php.net/manual/en/pdo.errorcode.php
	 * @see PDO::errorInfo(), PDOStatement::errorCode(), PDOStatement::errorInfo()
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.1.0
	 */
	public function errorCode(): string;

	/**
	 * Fetch extended error information associated with the last operation on the database handle
	 * @return array <p><b>PDO::errorInfo()</b> returns an array of error information about the last operation performed by this database handle. The array consists of the following fields:</p>   Element Information     0 SQLSTATE error code (a five characters alphanumeric identifier defined in the ANSI SQL standard).   1 Driver-specific error code.   2 Driver-specific error message.   <p><b>Note</b>:</p><p>If the SQLSTATE error code is not set or there is no driver-specific error, the elements following element 0 will be set to <b><code>NULL</code></b>.</p> <p><b>PDO::errorInfo()</b> only retrieves error information for operations performed directly on the database handle. If you create a PDOStatement object through <code>PDO::prepare()</code> or <code>PDO::query()</code> and invoke an error on the statement handle, <b>PDO::errorInfo()</b> will not reflect the error from the statement handle. You must call <code>PDOStatement::errorInfo()</code> to return the error information for an operation performed on a particular statement handle.</p>
	 * @link http://php.net/manual/en/pdo.errorinfo.php
	 * @see PDO::errorCode(), PDOStatement::errorCode(), PDOStatement::errorInfo()
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.1.0
	 */
	public function errorInfo(): array;

	/**
	 * Execute an SQL statement and return the number of affected rows
	 * <p><b>PDO::exec()</b> executes an SQL statement in a single function call, returning the number of rows affected by the statement.</p><p><b>PDO::exec()</b> does not return results from a SELECT statement. For a SELECT statement that you only need to issue once during your program, consider issuing <code>PDO::query()</code>. For a statement that you need to issue multiple times, prepare a PDOStatement object with <code>PDO::prepare()</code> and issue the statement with <code>PDOStatement::execute()</code>.</p>
	 * @param string $statement <p>The SQL statement to prepare and execute.</p> <p>Data inside the query should be properly escaped.</p>
	 * @return int <p><b>PDO::exec()</b> returns the number of rows that were modified or deleted by the SQL statement you issued. If no rows were affected, <b>PDO::exec()</b> returns <i>0</i>.</p><p><b>Warning</b></p><p>This function may return Boolean <b><code>FALSE</code></b>, but may also return a non-Boolean value which evaluates to <b><code>FALSE</code></b>. Please read the section on Booleans for more information. Use the === operator for testing the return value of this function.</p><p>The following example incorrectly relies on the return value of <b>PDO::exec()</b>, wherein a statement that affected 0 rows results in a call to <code>die()</code>:</p> <code> &lt;&#63;php<br>$db-&gt;exec()&nbsp;or&nbsp;die(print_r($db-&gt;errorInfo(),&nbsp;true));&nbsp;//&nbsp;incorrect<br>&#63;&gt;  </code>
	 * @link http://php.net/manual/en/pdo.exec.php
	 * @see PDO::prepare(), PDO::query(), PDOStatement::execute()
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.1.0
	 */
	public function exec(string $statement): int;

	/**
	 * Retrieve a database connection attribute
	 * <p>This function returns the value of a database connection attribute. To retrieve PDOStatement attributes, refer to <code>PDOStatement::getAttribute()</code>.</p><p>Note that some database/driver combinations may not support all of the database connection attributes.</p>
	 * @param int $attribute <p>One of the <i>PDO::ATTR_&#42;</i> constants. The constants that apply to database connections are as follows:</p><ul> <li><i>PDO::ATTR_AUTOCOMMIT</i></li> <li><i>PDO::ATTR_CASE</i></li> <li><i>PDO::ATTR_CLIENT_VERSION</i></li> <li><i>PDO::ATTR_CONNECTION_STATUS</i></li> <li><i>PDO::ATTR_DRIVER_NAME</i></li> <li><i>PDO::ATTR_ERRMODE</i></li> <li><i>PDO::ATTR_ORACLE_NULLS</i></li> <li><i>PDO::ATTR_PERSISTENT</i></li> <li><i>PDO::ATTR_PREFETCH</i></li> <li><i>PDO::ATTR_SERVER_INFO</i></li> <li><i>PDO::ATTR_SERVER_VERSION</i></li> <li><i>PDO::ATTR_TIMEOUT</i></li> </ul>
	 * @return mixed <p>A successful call returns the value of the requested PDO attribute. An unsuccessful call returns <i>null</i>.</p>
	 * @link http://php.net/manual/en/pdo.getattribute.php
	 * @see PDO::setAttribute(), PDOStatement::getAttribute(), PDOStatement::setAttribute()
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.2.0
	 */
	public function getAttribute(int $attribute);


	/**
	 * Checks if inside a transaction
	 * <p>Checks if a transaction is currently active within the driver. This method only works for database drivers that support transactions.</p>
	 * @return bool <p>Returns <b><code>TRUE</code></b> if a transaction is currently active, and <b><code>FALSE</code></b> if not.</p>
	 * @link http://php.net/manual/en/pdo.intransaction.php
	 * @since PHP 5 >= 5.3.3, Bundled pdo_pgsql, PHP 7
	 */
	public function inTransaction(): bool;


	/**
	 * Returns the ID of the last inserted row or sequence value
	 * <p>Returns the ID of the last inserted row, or the last value from a sequence object, depending on the underlying driver. For example, PDO_PGSQL requires you to specify the name of a sequence object for the <code>name</code> parameter.</p><p><b>Note</b>:</p><p>This method may not return a meaningful or consistent result across different PDO drivers, because the underlying database may not even support the notion of auto-increment fields or sequences.</p>
	 * @param string $name <p>Name of the sequence object from which the ID should be returned.</p>
	 * @return string <p>If a sequence name was not specified for the <code>name</code> parameter, <b>PDO::lastInsertId()</b> returns a string representing the row ID of the last row that was inserted into the database.</p><p>If a sequence name was specified for the <code>name</code> parameter, <b>PDO::lastInsertId()</b> returns a string representing the last value retrieved from the specified sequence object.</p><p>If the PDO driver does not support this capability, <b>PDO::lastInsertId()</b> triggers an <i>IM001</i> SQLSTATE.</p>
	 * @link http://php.net/manual/en/pdo.lastinsertid.php
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.1.0
   * 
   * I have no idea why this method erasure does not match the PDO class.  
   * 
	 */
	public function lastInsertId( $seqname = null );

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
	public function prepareStatement(string $statement, array $driver_options = [] ): PDOStatement;

	
  
  /**
   * Used to execute a query that returns a result set
   * @param $statement SQL statement to use
   * @return Generator yielded results 
   * @throws DBException if there is one
   */
  public function executeQuery( string $statement ) : Generator;  
  

	/**
	 * Rolls back a transaction
	 * <p>Rolls back the current transaction, as initiated by <code>PDO::beginTransaction()</code>.</p><p>If the database was set to autocommit mode, this function will restore autocommit mode after it has rolled back the transaction.</p><p>Some databases, including MySQL, automatically issue an implicit COMMIT when a database definition language (DDL) statement such as DROP TABLE or CREATE TABLE is issued within a transaction. The implicit COMMIT will prevent you from rolling back any other changes within the transaction boundary.</p>
	 * @return bool <p>Returns <b><code>TRUE</code></b> on success or <b><code>FALSE</code></b> on failure.</p>
	 * @link http://php.net/manual/en/pdo.rollback.php
	 * @see PDO::beginTransaction(), PDO::commit()
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.1.0
	 */
	public function rollBack(): bool;

}
