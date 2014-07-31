<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140730
 */

namespace raptor_ci;

/**
 * Parser for contraindication expressions
 *
 * @author FrankWin7VM
 */
interface IExpressionParser
{
    public function parse($sExpression);
    public function compile($sExpression);
}
