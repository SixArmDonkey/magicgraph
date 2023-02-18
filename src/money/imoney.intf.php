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

namespace buffalokiwi\magicgraph\money;

use InvalidArgumentException;
use JsonSerializable;
use Money\Currency;
use Money\Money;


interface IMoney extends JsonSerializable
{
    const ROUND_HALF_UP = PHP_ROUND_HALF_UP;

    const ROUND_HALF_DOWN = PHP_ROUND_HALF_DOWN;

    const ROUND_HALF_EVEN = PHP_ROUND_HALF_EVEN;

    const ROUND_HALF_ODD = PHP_ROUND_HALF_ODD;

    const ROUND_UP = 5;

    const ROUND_DOWN = 6;

    const ROUND_HALF_POSITIVE_INFINITY = 7;

    const ROUND_HALF_NEGATIVE_INFINITY = 8;  
  
    
    
    /**
     * Retrieve the amount formatted as currency
     * @return string amount
     */
    public function getFormattedAmount() : string;
    
    /**
     * Checks whether a IMoney has the same Currency as this.
     *
     * @param IMoney $other
     *
     * @return bool
     */
    public function isSameCurrency(IMoney $other) : bool;


    /**
     * Checks whether the value represented by this object equals to the other.
     *
     * @param IMoney $other
     *
     * @return bool
     */
    public function equals(IMoney $other) : bool;
    

    /**
     * Returns an integer less than, equal to, or greater than zero
     * if the value of this object is considered to be respectively
     * less than, equal to, or greater than the other.
     *
     * @param IMoney $other
     *
     * @return int
     */
    public function compare(IMoney $other) : int;
    

    /**
     * Checks whether the value represented by this object is greater than the other.
     *
     * @param IMoney $other
     *
     * @return bool
     */
    public function greaterThan(IMoney $other) : bool;
    

    /**
     * @param \IMoney\IMoney $other
     *
     * @return bool
     */
    public function greaterThanOrEqual(IMoney $other) : bool;
    

    /**
     * Checks whether the value represented by this object is less than the other.
     *
     * @param IMoney $other
     *
     * @return bool
     */
    public function lessThan(IMoney $other) : bool;
    

    /**
     * @param \IMoney\IMoney $other
     *
     * @return bool
     */
    public function lessThanOrEqual(IMoney $other) : bool;
    

    /**
     * Returns the value represented by this object.
     *
     * @return string
     */
    public function getAmount() : string;
    

    /**
     * Returns the currency of this object.
     *
     * @return Currency
     */
    public function getCurrency() : Currency;
    
    
    /**
     * Retrieve the wrapped Money object instance 
     * @return Money Money 
     */
    public function getMoney() : Money;
    
    
    /**
     * Returns a new IMoney object that represents
     * the sum of this and an other IMoney object.
     *
     * @param IMoney[] $addends
     *
     * @return IMoney
     */
    public function add(IMoney ...$addends) : IMoney;
    

    /**
     * Returns a new IMoney object that represents
     * the difference of this and an other IMoney object.
     *
     * @param IMoney[] $subtrahends
     *
     * @return IMoney
     */
    public function subtract(IMoney ...$subtrahends) : IMoney;

    
    /**
     * Returns a new IMoney object that represents
     * the multiplied value by the given factor.
     *
     * @param float|int|string $multiplier
     * @param int              $roundingMode
     *
     * @return IMoney
     */
    public function multiply($multiplier, $roundingMode = self::ROUND_HALF_UP) : IMoney;
    

    /**
     * Returns a new IMoney object that represents
     * the divided value by the given factor.
     *
     * @param float|int|string $divisor
     * @param int              $roundingMode
     *
     * @return IMoney
     */
    public function divide($divisor, $roundingMode = self::ROUND_HALF_UP) : IMoney;
    
    /**
     * Returns a new IMoney object that represents
     * the remainder after dividing the value by
     * the given factor.
     *
     * @param IMoney $divisor
     *
     * @return IMoney
     */
    public function mod(IMoney $divisor) : IMoney;

    /**
     * Allocate the money according to a list of ratios.
     *
     * @param array $ratios
     *
     * @return IMoney[]
     */
    public function allocate(array $ratios) : array;
    

    /**
     * Allocate the money among N targets.
     *
     * @param int $n
     *
     * @return IMoney[]
     *
     * @throws InvalidArgumentException If number of targets is not an integer
     */
    public function allocateTo($n) : array;
    

    /**
     * @param IMoney $money
     *
     * @return string
     */
    public function ratioOf(IMoney $money) : string;
    

    /**
     * @return IMoney
     */
    public function absolute() : IMoney;
    

    /**
     * @return IMoney
     */
    public function negative() : IMoney;
    

    /**
     * Checks if the value represented by this object is zero.
     *
     * @return bool
     */
    public function isZero() : bool;
    

    /**
     * Checks if the value represented by this object is positive.
     *
     * @return bool
     */
    public function isPositive() : bool;
    

    /**
     * Checks if the value represented by this object is negative.
     *
     * @return bool
     */
    public function isNegative() : bool;
}
