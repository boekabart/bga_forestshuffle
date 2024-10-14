<?php

namespace FOS\Models\Species\vCard;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class CommonNightingale extends \FOS\Models\Species
{
    public $name = 'Nightingale';
    public $nb = 3;
    public $tags = [WOODLAND, BIRD];
    public $cost = 1;

    //
    public function effect() {}

    //[action ⟳]
    public function bonus()
    {
        return $this->playAgain();
    }

    //5 [points] if on a [shrub]
    public function score($playerId, $forests)
    {
        $tree = $this->getOwnTree();
        return (in_array(SHRUB, $tree->getSpecies()[0]->tags)) ? 5 : 0;
    }
}
