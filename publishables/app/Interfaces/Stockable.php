<?php

namespace App\Interfaces;

interface Stockable
{
    public function getStock():int;
    public function getStockableId():int;
    public function getStockableType():string;
    public function getStockableLabel():string;
}
