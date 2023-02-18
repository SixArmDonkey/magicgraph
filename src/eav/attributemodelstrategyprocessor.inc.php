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

namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\ValidationException;



class AttributeModelStrategyProcessor implements IAttributeModelStrategyProcessor
{
  private array $strategyList;
  
  public function __construct( IAttributeModelServiceSaveStrategy ...$strategyList )
  {
    $this->strategyList = $strategyList;
  }
  
  
  /**
   * Called prior to Attribute models being saved
   * @throws ValidationException for rollback
   */
  public function processBeforeSaveAttributeModel( IRepository $repo, IAttributeModel ...$modelList ) : void
  {
    foreach( $this->strategyList as $strategy )
    {
      $f = $strategy->getBeforeSaveAttributeModel();
      if ( $f instanceof \Closure )
        $f( $repo, ...$modelList );
    }
  }
  
  
  /**
   * Called after Attribute models are saved
   * @throws ValidationException for rollback
   */
  public function processAfterSaveAttributeModel( IRepository $repo, IAttributeModel ...$modelList ) : void
  {
    foreach( $this->strategyList as $strategy )
    {
      $f = $strategy->getAfterSaveAttributeModel();
      if ( $f instanceof \Closure )
        $f( $repo, ...$modelList );
    }    
  }
  
  
  /**
   * Called prior to Attribute values being saved
   * @throws ValidationException for rollback
   */
  public function processBeforeSaveAttributeValue( IRepository $repo, IAttrValue ...$modelList ) : void
  {
    foreach( $this->strategyList as $strategy )
    {
      $f = $strategy->getBeforeSaveAttributeValue();
      if ( $f instanceof \Closure )
        $f( $repo, ...$modelList );
    }    
  }


  /**
   * Called after Attribute values are saved
   * @throws ValidationException for rollback
   */
  public function processAfterSaveAttributeValue( IRepository $repo, IAttrValue ...$modelList ) : void
  {
    foreach( $this->strategyList as $strategy )
    {
      $f = $strategy->getAfterSaveAttributeValue();
      if ( $f instanceof \Closure )
        $f( $repo, ...$modelList );
    }        
  }
}

