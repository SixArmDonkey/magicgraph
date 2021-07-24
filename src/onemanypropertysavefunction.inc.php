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

use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\persist\ISaveFunction;
use Exception;


/**
 * WARNING: It is currently possible for child service providers to leave
 * orphaned rows when deleting.  This seems to only happen with relationships nested > 2 levels.
 */
class OneManyPropertySaveFunction implements ISaveFunction
{
  private $parent;
  private $repo;
  private $parentModelName;
  private $parentPropName;
  private $modelIdPropName;
  private $beforeSave;
  private $afterSave;
  private bool $manageDeletes;
  
  
  /**
   * Create a new save function.
   * 
   * ie: 
   * Say you want to link many addresses to a single customer model.
   * 
   * new OneManyPropertySaveFunction( customer_model, address_repo, 'address', 'link_entity', 'id' );
   * 
   * customer_model is the customer IModel instance
   * address_repo is the IRepository instance for saving IAddress instances
   * 'address' is the array property name attached to the customer_model containing the IAddress instances
   * 'link_entity' is an integer property attached to the address model that contains the customer id 
   * 'id' is an integer property and primary key for the address model
   * 
   * 
   * @param IModel $parent The parent model where many models are attached.
   * @param IRepository $repo The repo for saving the ATTACHED models.
   * @param string $parentModelName The parent property name of the array containing the linked models 
   * @param string $parentPropName The linked model property name that contains the id of the parent model
   * @param string $modelIdPropName The linked model id property name 
   */
  public function __construct( IModel $parent, IRepository $repo, string $parentModelName, string $parentPropName, string $modelIdPropName, ?\Closure $beforeSave = null, ?\Closure $afterSave = null, bool $manageDeletes = true )
  {
    $this->parent = $parent;
    $this->repo = $repo;
    $this->parentModelName = $parentModelName;
    $this->parentPropName = $parentPropName;
    $this->modelIdPropName = $modelIdPropName;
    $this->beforeSave = $beforeSave;
    $this->afterSave = $afterSave;
    $this->manageDeletes = $manageDeletes;
  }
  
  public function getSaveFunction() : array
  {    
    $priKeys = $this->parent->getPropertySet()->getPrimaryKeys();
    if ( empty( $priKeys ))
      throw new Exception( 'Parent model (' . get_class( $this->parent ) . ') must contain at least one primary key definition' );
    else if ( sizeof( $priKeys ) > 1 )
      throw new Exception( 'Address Service cannot be natively linked to models with compound primary keys.  You must override and create your own save function.' );
    
    $parentId = (string)$this->parent->getValue( $priKeys[0]->getName());    
    
    $modelSave = [];
    $ids = [];
    
    foreach( $this->parent->getValue( $this->parentModelName ) as $model )
    {
      /* @var $model IModel  */
      $idProp = $model->getValue( $this->modelIdPropName );
      if ( !empty( $idProp ))
        $ids[] = $idProp;

      

      //..Default model/repo takes care of all of this 
      $modelSave[] = $model;
      //if ( $model->hasEdits())
      //{
//        $modelSave[] = $model;
  //    }
    }
    
    $self = $this;
    
    $md = $this->manageDeletes;
    
    return $this->repo->getSaveFunction( 
      function( IRepository $repo, IModel ...$models ) use($priKeys,$self) {
        $parentId = (string)$self->parent->getValue( $priKeys[0]->getName());    
        foreach( $models as $m )
        {
          $id = $m->getValue( $self->parentPropName );
          if ( empty( $id ))
            $m->setValue( $self->parentPropName, $parentId );
        }
        
        if ( $self->beforeSave != null )
        {
          $f = $self->beforeSave;
          $f( $repo, $self->parent, ...$models );
        }
      }, 
      
      function( IRepository $repo, IModel ...$models ) use($ids,$priKeys,$self, $md) {
        if ( !$md )
          return;
        
        
        $parentId = (string)$self->parent->getValue( $priKeys[0]->getName());    
        foreach( $models as $a )
        {
          $ids[] = $a->getValue( $self->modelIdPropName );
        }         

        $eIds = $repo->getIdsForProperty( $self->parentPropName, $parentId );
        foreach( array_diff( $eIds, $ids ) as $removeId )
        {
          /*
          //..This theoretically should fix the save issue with nested models.
          //..It should simply be a matter of clearing the array properties as the 
          //..Backing service provider for the child would then fire this code again.
          foreach( $models as $a )
          {
            if ( $a->getValue( $self->modelIdPropName ) == $removeId )
            {
              foreach( $a->getPropertySet()->getProperties() as $prop )
              {
                // @var $prop buffalokiwi\magicgraph\property\IProperty 
                if ( $prop->getType()->TARRAY())
                {
                  try {
                    $prop->setValue( [] );
                  } catch (Exception $ex) {
                    //..Do nothing.  Data could be orphaned at this point due to read only configuration, etc.
                  }              
                }
              }              
            }
          }
          */
          $repo->removeById((string)$removeId );
        }
        
        if ( $self->afterSave != null )
        {
          $f = $self->afterSave;
          $f( $repo, $self->parent, ...$models );
        }
      },      
      ...$modelSave 
    );  
  }
}
