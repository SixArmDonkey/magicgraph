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
  public function createElement( IModel $model, IProperty $property, string $name, string $id, string $value ) : IElement;
  
  
  /**
   * For a given model, generate a series of HTML form inputs.
   * @param IModel $model
   * @param array $attrs
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
}
