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

namespace buffalokiwi\magicgraph\pdo;

use PDO;


/**
 * When nesting transactions, this can be used to prevent an inner transaction from prematurely committing or rolling back
 * parent transactions.
 * 
 * beginTransaction(): If a transaction has already been started on the supplied db connection, this does nothing 
 * and returns false.  Otherwise, this starts a new transaction and returns the result.  
 * 
 * commit(): If there was a parent transaction, this returns false.  otherwise, it commits.
 * rollback(): If there was a parent transaction, this returns false.  otherwise, it does a rollback.
 * 
 * 
 * 
 */
interface ITransactionUnit
{
	/**
	 * Initiates a transaction
	 * <p>Turns off autocommit mode. While autocommit mode is turned off, changes made to the database via the 
   * PDO object instance are not committed until you end the transaction by calling <code>PDO::commit()</code>. 
   * Calling <code>PDO::rollBack()</code> will roll back all changes to the database and return the connection 
   * to autocommit mode.</p><p>Some databases, including MySQL, automatically issue an implicit COMMIT when a 
   * database definition language (DDL) statement such as DROP TABLE or CREATE TABLE is issued within a transaction. 
   * The implicit COMMIT will prevent you from rolling back any other changes within the transaction boundary.</p>
	 * @return bool <p>Returns <b><code>TRUE</code></b> on success or <b><code>FALSE</code></b> on failure.</p>
	 * @link http://php.net/manual/en/pdo.begintransaction.php
	 * @see PDO::commit(), PDO::rollBack()
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.1.0
	 */
	public function beginTransaction(): bool;
  

	/**
	 * Commits a transaction
	 * <p>Commits a transaction, returning the database connection to autocommit mode until the next call to 
   * <code>PDO::beginTransaction()</code> starts a new transaction.</p>
	 * @return bool <p>Returns <b><code>TRUE</code></b> on success or <b><code>FALSE</code></b> on failure.</p>
	 * @link http://php.net/manual/en/pdo.commit.php
	 * @see PDO::beginTransaction(), PDO::rollBack()
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.1.0
	 */
	public function commit(): bool;
  
  
	/**
	 * Rolls back a transaction
	 * <p>Rolls back the current transaction, as initiated by <code>PDO::beginTransaction()</code>.</p><p>If the database 
   * was set to autocommit mode, this function will restore autocommit mode after it has rolled back the 
   * transaction.</p><p>Some databases, including MySQL, automatically issue an implicit COMMIT when a database 
   * definition language (DDL) statement such as DROP TABLE or CREATE TABLE is issued within a transaction. 
   * The implicit COMMIT will prevent you from rolling back any other changes within the transaction boundary.</p>
	 * @return bool <p>Returns <b><code>TRUE</code></b> on success or <b><code>FALSE</code></b> on failure.</p>
	 * @link http://php.net/manual/en/pdo.rollback.php
	 * @see PDO::beginTransaction(), PDO::commit()
	 * @since PHP 5 >= 5.1.0, PHP 7, PECL pdo >= 0.1.0
	 */
	public function rollBack(): bool;  
  
  
  /**
   * If the supplied database connection was already in a transaction when this object was constructed.
   * @return bool has parent transaction 
   */
  public function hasParentTrans() : bool;  
}

