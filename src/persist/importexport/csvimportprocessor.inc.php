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

namespace buffalokiwi\magicgraph\persist\importexport;

use Exception;
use InvalidArgumentException;


/**
 * The first row MUST contain the column headings 
 */
class CSVImportProcessor implements IImportProcessor
{
  private IImportAdapter $adapter;
  private string $filename;
  private int $len;
  private string $separator;
  private string $enclosure;
  private string $escape;
  
  
  /**
   * 
   * @param string $filename CSV Filename 
   * @param int $maxCSVLineLength Passed to fgetcsv() as the max length.  This can be set to zero.
   * @param string $separator Passed to fgetcsv() - the csv separator character
   * @param string $enclosure Passed to fgetcsv() - The csv field enclosed by character
   * @param string $escape Passed to fgetcsv() - The escape character 
   * @throws InvalidArgumentException
   */
  public function __construct( 
    IImportAdapter $adapter,
    string $filename, 
    int $maxCSVLineLength = 16384, 
    string $separator = ",",
    string $enclosure = '"',
    string $escape = "\\" ) 
  {
    if ( !file_exists( $filename ))
      throw new InvalidArgumentException( 'CSV file does not exist at ' . $filename );
    else if ( empty( $separator ))
      throw new InvalidArgumentException( 'Separator must not be empty' );
    
    $this->adapter = $adapter;
    $this->filename = $filename;
    $this->len = ( $maxCSVLineLength ) < 0 ? 0 : $maxCSVLineLength;
    $this->separator = $separator;
    $this->enclosure = $enclosure;
    $this->escape = $escape;
  }
  
  
  /**
   * Import the csv 
   * @return void
   * @throws CSVException 
   */
  public function process() : void
  {
    $h = fopen( $this->filename, 'r' );
    if ( $h === false )
      throw new CSVException( 'Failed to open csv file', 0, null, $this->filename );
    
    $this->adapter->initialize();
    $rowIndex = -1;
    
    try {
      $columnNames = [];
      while (( $row = fgetcsv( $h, $this->len, $this->separator, $this->enclosure, $this->escape )) !== false )
      {
        if ( ++$rowIndex == 0 )
        {
          //..This should contain column headings, which are to be mapped to some model 
          $columnNames = $row;
          continue;
        }
        
        $entry = [];
        foreach( $row as $k => $v )
        {
          $entry[$columnNames[$k]] = $v;
        }
        
        //..Process in some adapter 
        $this->adapter->execute( $entry );
      }
      
      //..Finalize
      $this->adapter->finalize();
    } catch ( Exception $e ) {
      $this->adapter->exception();
      throw new CSVException( 'Failed to process csv file', 0, $e, $this->filename, $rowIndex, -1 ); //..Replace -1 with the row/col variables 
    } finally {
      fclose( $h );
    }
  }
}

