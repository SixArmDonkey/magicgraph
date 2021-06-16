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

use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\property\IPropertySet;
use Exception;


/**
 * For some map of data, this will create a model and save it to the supplied repo.
 * 
 * On execute:
 * 
 * 1) Create a new model
 * 2) For each property:
 *   a) Check that property exists
 *   b) if exists, set model property value
 * 3) save the model to the repository 
 */
class RepositoryImportAdapter extends Adapter implements IImportAdapter
{
  private IRepository $repo;
  private IPropertySet $props;
  
  
  public function __construct( IRepository $repo, IAdapterHandler ...$handlers )
  {
    parent::__construct( ...$handlers );
    $this->repo = $repo;
    $this->props = $repo->create()->getPropertySet();
  }
  
  
  /**
   * Execute something against a row to be imported.
   * This is where the row should be saved.
   * @param array $data Data to be imported
   * @return void
   * @throws Exception
   */
  public function execute( array $data ) : void
  {
    $r = $this->repo->create();
    foreach( $data as $k => $v )
    {
      if ( $this->props->isMember( $k ))
        $r->setValue( $k, $v );
    }
    
    $this->beforeSave( $r );

    //..Attempt to save 
    $this->repo->save( $r );
  }
}
