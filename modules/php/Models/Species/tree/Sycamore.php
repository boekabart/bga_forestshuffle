<?php

namespace FOS\Models\Species\tree;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Sycamore
 */

class Sycamore extends \FOS\Models\Species
{
    public $name = 'Sycamore';
    public $nb = 6;
    public $tags = ['Tree'];
    public $cost = 2;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //1 [point] x [tree]
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 1, 'Tree');
    }
}
