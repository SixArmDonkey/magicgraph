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


/**
 * A factory for obtaining an import processor 
 */
class FileImportFactory implements IFileImportProcessorFactory
{
  private array $config = [];
  
  /**
   * @param array $config [name => function( string $filename ) : IImportProcessor]
   * $config is a map of name to a closure that returns an import processor 
   */
  public function __construct( array $config )
  {
    foreach( $config as $k => $v )
    {
      if ( !is_string( $k ) || empty( $k ))
        throw new \InvalidArgumentException( 'Configuration keys must be non-empty strings' );
      else if ( !( $v instanceof \Closure ))
        throw new \InvalidArgumentException( 'Configuration values must be instances of \Closure' );
      
      $this->config[$k] = $v;
    }
  }
  
  
  /**
   * 
   * @param string $type The processor type.  This must match a key from the $config array passed to the constructor
   * @param string $importFilename The file to import
   */
  public function getImportProcessor( string $type, string $importFilename ) : IImportProcessor
  {
    if ( !isset( $this->config[$type] ))
      throw new \InvalidArgumentException( 'Invalid import processor type' );
    
    $f = $this->config[$type];
    
    $processor = $f( $importFilename );
    if ( !( $processor instanceof IImportProcessor ))
    {
      throw new \Exception( 'Factory closure for type ' . $type . ' did not return an instance of ' . IImportProcessor::class );
    }
    
    return $processor;
  }
}

