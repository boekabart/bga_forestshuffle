<?php

namespace FOS\Models\Species\tree;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class Sambucus extends \FOS\Models\Species
{
    public $name = 'Elderberry';
    public $nb = 4;
    public $tags = [WOODLAND, SHRUB];
    public $cost = 2;

    //whenever you play a card with [plant]: [1 in card] 
    public function specialEffect($card, $specie, $position)
    {
        if (in_array(PLANT, $specie->tags)) $this->takeOneCardEffect();
    }

    //[↴plantcard]
    public function bonus()
    {
        return $this->freePlay(PLANT);
    }
}
