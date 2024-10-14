<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Fly Agaric
 */

class FlyAgaric extends \FOS\Models\Species
{
    public $name = 'Fly Agaric';
    public $nb = 2;
    public $tags = [MUSHROOM];
    public $cost = 2;

    //whenever you play a card with [paw] : [1 in card]
    public function specialEffect($card, $specie, $position)
    {
        if (in_array(PAW, $specie->tags)) $this->takeOneCardEffect();
    }

    //
    public function bonus()
    {
    }

    //

}
