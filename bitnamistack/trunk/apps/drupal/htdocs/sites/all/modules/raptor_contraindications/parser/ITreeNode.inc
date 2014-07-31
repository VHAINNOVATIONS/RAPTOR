<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140727
 */

namespace raptor_ci;

/**
 * Abstract class for parser tree
 *
 * @author FrankWin7VM
 */
interface ITreeNode 
{
    public function isConstant();
    public function isVariable();
    public function isOperator();
    public function isBoolean();
    public function isNumeric();
    public function getLeftChild();
    public function getRightChild();
    public function getValue();
}
