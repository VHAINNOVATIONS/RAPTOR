<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140730
 */

namespace raptor_ci;

/**
 * Boolean negation class for parser tree
 *
 * @author FrankWin7VM
 */
class TNONegate extends \raptor_ci\TNOBoolean
{
    public function __construct($oRight)
    {
        if(!$oRight->isBoolean())
        {
            throw new \Exception('Only boolean nodes can be negated: ' . $oRight);
        }
        parent::__construct(NULL,'not',$oRight);
        $this->m_bBoolean = TRUE;
    }
}
