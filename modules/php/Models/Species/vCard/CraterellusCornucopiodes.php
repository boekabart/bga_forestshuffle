<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Craterellus Cornucopiodes
 */

class CraterellusCornucopiodes extends \FOS\Models\Species
{
    public $name = 'Craterellus Cornucopiodes';
    public $nb = 2;
    public $tags = [MUSHROOM, MOUNTAIN];
    public $cost = 2;

    //Whenever you play a card with a mountain symbol receive 1 card
    public function specialEffect($card, $specie, $position)
    {
        if (in_array(MOUNTAIN, $specie->tags)) $this->takeOneCardEffect();
    }

    //
    public function bonus()
    {
    }
}
