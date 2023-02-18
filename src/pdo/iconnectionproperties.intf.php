<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 *  Database
 * @author John Quinn
 */

namespace buffalokiwi\magicgraph\pdo;



/**
 * Defines connection properties used to establish a database connection
 */
interface IConnectionProperties 
{
  /**
   * Retrieve the host name 
   * @return string host 
   */
  public function getHost() : string;


  /**
   * Retrieve the user name
   * @return string user name 
   */
  public function getUser() : string;


  /**
   * Get the password
   * @return string password 
   */
  public function getPassword() : string;
  
  
  /**
   * Return the initial database name
   * @return string initial database name 
   */
  public function getDatabase() : string;


  /**
   * Retrieve the port number in use
   * @return int port 
   */
  public function getPort() : int;


  /**
   * Retrieve a hash representing these connection properties 
   * @return string hash
   */
  public function hash() : string;

  /**
   * Retrieve the character set
   * @return string charset
   */
  public function getCharset() : string;
  
  /**
   * Retrieve the data source name 
   * @return string dsn
   */
  public function dsn() : string;
  
  
  /**
   * Retrive an array of options for the database driver
   * @return array options 
   */
  public function getOptions() : array;
  
}
