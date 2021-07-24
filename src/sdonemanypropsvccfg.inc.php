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


namespace buffalokiwi\magicgraph;

use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\property\INamedPropertyBehavior;
use Closure;
use Exception;
use InvalidArgumentException;


/**
 * This is the soft delete version of the save function.
 * When using this config, you will need to manage deletes yourself.
 * 
 * A property service configuration for a one to many relationship.
 * 
 * The parent model is expected to have an array property named $arrayProperty and be bound to instances 
 * of the linked model.
 * The linked model must contain a property with the parent id 
 * The linked model must contain a single primary key property 
 * 
 * Use this with a OneManyPropertyService 
 * 
 * @todo extending BasePropSvcCfg might not make sense.  Consider changing this into some sort of relationship provider configuration or something.
 * 
 */
class SDOneManyPropSvcCfg extends OneManyPropSvcCfg
{
  /**
   * Parent Id property name
   * @var string 
   */
  private $parentIdProperty;
  
  /**
   * Parent array property name 
   * @var string
   */
  private $arrayProperty;
  
  /**
   * Linked model entity id property name 
   * @var string
   */
  private $entityProperty;
  
  /**
   * Linked model property name
   * @var string
   */
  private $idProperty;
  
  /**
   * Address repo 
   * @var IRepository
   */
  private $repo;
  
  private $beforeSave;
  private $afterSave;
  
  
  /**
   * Create a new OneManyPropSvcCfg instance 
   * 
   * NOTE: There is zero point to having the behavior classes tacked on to the end of this.
   * This property service has absolutely no way to utilize the behavior as behaviors need to be built along with the 
   * property set config when creating IPropertySet instances.  The behavior argument will be removed when I get time.
   * 
   * @param IRepository $repo Linked model repository 
   * @param string $parentIdProperty The parent model primary key property name.
   * @param string $arrayProperty The parent model property name for the array of linked models 
   * @param string $linkEntityProperty A linked model property that contains the parent id 
   * @param string $idProperty A linked model property containing the unique id of the linked model
   * @throws InvalidArgumentException
   */
  public function __construct( IRepository $repo, string $parentIdProperty, string $arrayProperty, string $linkEntityProperty, string $idProperty, ?\Closure $beforeSave = null, ?\Closure $afterSave = null, INamedPropertyBehavior ...$behavior  )
  {
    parent::__construct( $repo, $parentIdProperty, $arrayProperty, $linkEntityProperty, $idProperty, $beforeSave, $afterSave, ...$behavior );
    
    $this->repo = $repo;
    $this->parentIdProperty = $parentIdProperty;
    $this->arrayProperty = $arrayProperty;
    $this->entityProperty = $linkEntityProperty;
    $this->idProperty = $idProperty;
    $this->beforeSave = $beforeSave;
    $this->afterSave = $afterSave;
  }
  
  
  /**
   * Retrieve a save function to be used with some transaction.
   * @param IModel $parent Model this provider is linked to 
   * @return array IRunnable[] Something the saves data 
   * @throws Exception
   */
  protected function createSaveFunction( IModel $parent ) : array
  {
    return (new OneManyPropertySaveFunction(
        $parent, 
        $this->repo, 
        $this->arrayProperty,
        $this->entityProperty,
        $this->idProperty,
        $this->beforeSave,
        $this->afterSave,
        false //..do not manage deletes
    ))->getSaveFunction();
  }
}
