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

namespace buffalokiwi\magicgraph\property;

use buffalokiwi\magicgraph\IModel;
use Closure;
use Exception;


/**
 * Base property service config class.
 * 
 */
abstract class BasePropSvcCfg extends BasePropertyConfig implements IPropertySvcConfig
{
  /**
   * List of before save functions
   * @var Closure[] 
   */
  private $beforeSave = [];
  
  
  /**
   * List of after save functions 
   * @var Closure[] 
   */
  private $afterSave = [];
  
  
  /**
   * Constructor 
   * @param INamedPropertyBeavior $behavior Property behavior modifications 
   */
  public function __construct( INamedPropertyBehavior ...$behavior )
  {
    parent::__construct( ...$behavior );
    
    foreach( $behavior as $b )
    {
      /* @var $b INamedPropertyBehavior */
      if ( $b->getBeforeSaveCallback() != null )
        $this->beforeSave[] = $b->getBeforeSaveCallback();
      
      if ( $b->getAfterSaveCallback() != null )
        $this->afterSave[] = $b->getAfterSaveCallback();
    }
  }

  

  
  
  /**
   * Retrieve a save function to be used with some transaction.
   * @param IModel $parent Model this provider is linked to 
   * @return array IRunnable[] Something the saves data 
   * @throws Exception
   * @final 
   */
  public final function getSaveFunction( IModel $parent ) : array
  {
    $out = [];
    
    foreach( $this->beforeSave as $f )
    {
      $out[] = $f;
    }
    
    foreach( $this->createSaveFunction( $parent ) as $f )
    {
      $out[] = $f;
    }
    
    
    foreach( $this->afterSave as $f )
    {
      $out[] = $f;
    }
    
    return $out;
  }
  
  
  /**
   * Retrieve a save function to be used with some transaction.
   * @param IModel $parent Model this provider is linked to 
   * @return array IRunnable[] Something the saves data 
   * @throws Exception
   */
  protected function createSaveFunction( IModel $parent ) : array
  {
    return [];
  }
}