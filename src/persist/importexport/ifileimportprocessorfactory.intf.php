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


interface IFileImportProcessorFactory 
{
  /**
   * get an import processor by name 
   * @param string $type The processor type.  This must match a key from the $config array passed to the constructor
   * @param string $importFilename The file to import
   */
  public function getImportProcessor( string $type, string $importFilename ) : IImportProcessor;  
}

