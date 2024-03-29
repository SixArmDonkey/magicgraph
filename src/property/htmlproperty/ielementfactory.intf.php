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

namespace buffalokiwi\magicgraph\property\htmlproperty;

use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\property\IProperty;


/**
 * Converts IProperty to IElement.
 * Makes html inputs from properties.
 */
interface IElementFactory
{
  /**
   * Create an HTML Element from a property.
   * @param IProperty $property Property 
   * @param string $name Element name attribute value 
   * @param string $id Element Id attribute value 
   * @param string $value Property value from model getter.
   * @return IElement Element 
   * @throws HTMLPropertyException If a matching supplier does not exist.
   */
  public function createElement( IModel $model, IProperty $property, string $name, ?string $id, $value ) : IElement;
  
  
  /**
   * For a given model, generate a series of HTML form inputs.
   * @param IModel $model
   * @param array $attrs [name => [caption,value]]
   * @return array Configuration data for an animator/renderer.
   * [
   *   'name' => [
   *     'for' => "Property Name"
   *     '' => "Property Caption"
   *   ],
   *   'html' => "The html element code"
   * ]
   */
  public function createFormInputs( IModel $model, array $attrs = [] ) : array;  
  
  
  /**
   * Convert properties to html form inputs 
   * @param IModel $model model 
   * @param IProperty $properties properties to convert 
   * @return array Configuration data for an animator/renderer.
   * [
   *   'name' => [
   *     'for' => "Property Name"
   *     '' => "Property Caption"
   *   ],
   *   'html' => "The html element code"
   * ]
   */
  public function propertiesToFormInputs( IModel $model, IProperty ...$properties ) : array;
}
