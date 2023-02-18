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

namespace buffalokiwi\magicgraph\persist\importexport;

use Exception;
use Throwable;



class CSVException extends Exception
{
  private string $filename;
  private int $row;
  private int $col;
  
  
  /**
   * Construct the exception
   * <p>Constructs the Exception.</p>
   * @param string $message <p>The Exception message to throw.</p>
   * @param int $code <p>The Exception code.</p>
   * @param Throwable $previous <p>The previous exception used for the exception chaining.</p>
   * @return self
   * @link http://php.net/manual/en/exception.construct.php
   * @since PHP 5, PHP 7
   */
  public function __construct(string $message = "", int $code = 0, Throwable $previous = NULL, string $filename = '', int $row = -1, int $col = -1 )
  {
    parent::__construct( $message, $code, $previous );
    $this->filename = $filename;
    $this->row = $row;
    $this->col = $col;
  }
  
  
  /**
   * Retrieve the csv filename 
   * @return string
   */
  public function getCSVFilename() : string
  {
    return $this->filename;
  }
  
  
  /**
   * Retrieve the row the exception was thrown on 
   * @return int
   */
  public function getCSVRow() : int
  {
    return $this->row;
  }
  
  
  /**
   * Retrieve the column index responsible for the exception 
   * @return int
   */
  public function getCSVColumn() : int
  {
    return $this->col;
  }
  
  
  /**
   * String representation of the exception
   * <p>Returns the <code>string</code> representation of the exception.</p>
   * @return string <p>Returns the <code>string</code> representation of the exception.</p>
   * @link http://php.net/manual/en/exception.tostring.php
   * @since PHP 5, PHP 7
   */
  public function __toString(): string 
  {
    return parent::__toString() . "\n" . ' Error in CSV file: "' . $this->getCSVFilename() . '" on row ' . $this->getCSVRow() . ' in column ' . $this->getCSVColumn() . '. ';
  }
}
