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

namespace buffalokiwi\magicgraph\money;

use InvalidArgumentException;
use Money\Currency;
use Money\Money;


/**
 * A wrapper for the money\money money class.
 * This proxies all calls to the internal class, and any methods that return
 * a Money instance are wrapped in a new instance of MoneyProxy.
 * 
 * This is so we can swap out the underlying money implementation
 */
class MoneyProxy implements IMoney
{
  /**
   * Money 
   * @var Money
   */
  private $money;
  
  /**
   * Formatter
   * @var \Money\Formatter\IntlMoneyFormatter
   */
  private $formatter;
  
  /**
   * Decimal formatter 
   * @var Money\Formatter\DecimalMoneyFormatter
   */
  private $decFmt;
  
  
  /**
   * Create a new money wrapper 
   * @param Money $money Money to wrap 
   */
  public function __construct( Money $money, \Money\Formatter\IntlMoneyFormatter $formatter, \Money\Formatter\DecimalMoneyFormatter $decFmt )
  {
    $this->money = $money;
    $this->formatter = $formatter;
    $this->decFmt = $decFmt;
  }

  
  /**
   * Retrieve the amount formatted as currency
   * @return string amount
   */
  public function getFormattedAmount() : string
  {        
    return $this->formatter->format( $this->money );
  }  
  
  
	/**
	 * Specify data which should be serialized to JSON
	 * <p>Serializes the object to a value that can be serialized natively by <code>json_encode()</code>.</p>
	 * @return mixed <p>Returns data which can be serialized by <code>json_encode()</code>, which is a value of any type other than a <code>resource</code>.</p>
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @since PHP 5 >= 5.4.0, PHP 7
	 */
	public function jsonSerialize()
  {
    return $this->money->jsonSerialize();
  }
  
  
  /**
   * Checks whether a IMoney has the same Currency as this.
   *
   * @param IMoney $other
   *
   * @return bool
   */
  public function isSameCurrency(IMoney $other) : bool
  {
    return $this->money->isSameCurrency( $other->getMoney());
  }


  /**
   * Checks whether the value represented by this object equals to the other.
   *
   * @param IMoney $other
   *
   * @return bool
   */
  public function equals(IMoney $other) : bool
  {
    return $this->money->equals( $other->getMoney());
  }


  /**
   * Returns an integer less than, equal to, or greater than zero
   * if the value of this object is considered to be respectively
   * less than, equal to, or greater than the other.
   *
   * @param IMoney $other
   *
   * @return int
   */
  public function compare(IMoney $other) : int
  {
    return $this->money->compare( $other->getMoney());
  }


  /**
   * Checks whether the value represented by this object is greater than the other.
   *
   * @param IMoney $other
   *
   * @return bool
   */
  public function greaterThan(IMoney $other) : bool
  {
    return $this->money->greaterThan( $other->getMoney());
  }


  /**
   * @param \IMoney\IMoney $other
   *
   * @return bool
   */
  public function greaterThanOrEqual(IMoney $other) : bool
  {
    return $this->money->greaterThanOrEqual( $other->getMoney());
  }


  /**
   * Checks whether the value represented by this object is less than the other.
   *
   * @param IMoney $other
   *
   * @return bool
   */
  public function lessThan(IMoney $other) : bool
  {
    return $this->money->lessThan( $other->getMoney());
  }


  /**
   * @param \IMoney\IMoney $other
   *
   * @return bool
   */
  public function lessThanOrEqual(IMoney $other) : bool
  {
    return $this->money->lessThanOrEqual( $other->getMoney());
  }


  /**
   * Returns the value represented by this object.
   *
   * @return string
   */
  public function getAmount() : string
  {
    return $this->money->getAmount();
  }


  /**
   * Returns the currency of this object.
   *
   * @return Currency
   */
  public function getCurrency() : Currency
  {
    return $this->money->getCurrency();
  }


  /**
   * Retrieve the wrapped Money object instance 
   * @return Money Money 
   */
  public function getMoney() : Money
  {
    return $this->money;
  }


  /**
   * Returns a new IMoney object that represents
   * the sum of this and an other IMoney object.
   *
   * @param IMoney[] $addends
   *
   * @return IMoney
   */
  public function add(IMoney ...$addends) : IMoney
  {
    $m = $this->money;
    foreach( $addends as $add )
    {
      /* @var $add IMoney */
      $m = $this->money->add( $add->getMoney());
    }
    
    return new MoneyProxy( $m, $this->formatter, $this->decFmt );
  }


  /**
   * Returns a new IMoney object that represents
   * the difference of this and an other IMoney object.
   *
   * @param IMoney[] $subtrahends
   *
   * @return IMoney
   */
  public function subtract(IMoney ...$subtrahends) : IMoney
  {
    $m = $this->money;
    foreach( $subtrahends as $sub )
    {
      $m = $m->subtract( $sub->getMoney());
    }
    
    return new MoneyProxy( $m, $this->formatter, $this->decFmt );
  }


  /**
   * Returns a new IMoney object that represents
   * the multiplied value by the given factor.
   *
   * @param float|int|string $multiplier
   * @param int              $roundingMode
   *
   * @return IMoney
   */
  public function multiply($multiplier, $roundingMode = self::ROUND_HALF_UP) : IMoney
  {
    return new MoneyProxy( $this->money->multiply( $multiplier, $roundingMode ), $this->formatter, $this->decFmt );
  }


  /**
   * Returns a new IMoney object that represents
   * the divided value by the given factor.
   *
   * @param float|int|string $divisor
   * @param int              $roundingMode
   *
   * @return IMoney
   */
  public function divide($divisor, $roundingMode = self::ROUND_HALF_UP) : IMoney
  {
    return new MoneyProxy( $this->money->divide( $divisor, $roundingMode ), $this->formatter, $this->decFmt );
  }


  /**
   * Returns a new IMoney object that represents
   * the remainder after dividing the value by
   * the given factor.
   *
   * @param IMoney $divisor
   *
   * @return IMoney
   */
  public function mod(IMoney $divisor) : IMoney
  {
    return new MoneyProxy( $this->money->mod( $divisor->getMoney()), $this->formatter, $this->decFmt );
  }


  /**
   * Allocate the money according to a list of ratios.
   *
   * @param array $ratios
   *
   * @return IMoney[]
   */
  public function allocate(array $ratios) : array
  {
    $out = [];
    foreach( $this->money->allocate( $ratios ) as $money )
    {
      $out[] = new MoneyProxy( $money, $this->formatter, $this->decFmt );
    }
    return $out;
  }


  /**
   * Allocate the money among N targets.
   *
   * @param int $n
   *
   * @return IMoney[]
   *
   * @throws InvalidArgumentException If number of targets is not an integer
   */
  public function allocateTo($n) : array
  {
    $out = [];
    foreach( $this->money->allocateTo( $n ) as $money )
    {
      $out[] = new MoneyProxy( $money, $this->formatter, $this->decFmt );
    }
    return $out;      
  }


  /**
   * @param IMoney $money
   *
   * @return string
   */
  public function ratioOf(IMoney $money) : string
  {
    return $this->money->ratioOf( $money->getMoney());
  }


  /**
   * @return IMoney
   */
  public function absolute() : IMoney
  {
    return new MoneyProxy( $this->money->absolute(), $this->formatter, $this->decFmt );
  }


  /**
   * @return IMoney
   */
  public function negative() : IMoney
  {
    return new MoneyProxy( $this->money->negative(), $this->formatter, $this->decFmt );
  }


  /**
   * Checks if the value represented by this object is zero.
   *
   * @return bool
   */
  public function isZero() : bool
  {
    return $this->money->isZero();
  }


  /**
   * Checks if the value represented by this object is positive.
   *
   * @return bool
   */
  public function isPositive() : bool
  {
    return $this->money->isPositive();
  }


  /**
   * Checks if the value represented by this object is negative.
   *
   * @return bool
   */
  public function isNegative() : bool
  {
    return $this->money->isNegative();
  }
  
  
  public function __toString() : string
  {
    return $this->decFmt->format( $this->money );
  }
}
