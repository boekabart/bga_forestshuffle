<?php

namespace FOS\Models\Species\tree;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Birch
 */

class Birch extends \FOS\Models\Species
{
    public $name = 'Birch';
    public $nb = 10;
    public $tags = [TREE];
    public $cost = 0;

    // [1 in card] 
    public function effect()
    {
        $this->takeOneCardEffect();
    }

    //
    public function bonus()
    {
    }

    //1 [point]
    public function score($playerId, $forests)
    {
        return 1;
    }
}
