<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Parasol Mushroom
 */

class ParasolMushroom extends \FOS\Models\Species
{
    public $name = 'Parasol Mushroom';
    public $nb = 2;
    public $tags = [MUSHROOM];
    public $cost = 2;

    //whenever you play a card below a tree : [1 in card]
    public function specialEffect($card, $specie, $position)
    {
        if ($position == BOTTOM && $card->isOnARealTree()) $this->takeOneCardEffect();
    }

    //
    public function bonus()
    {
    }

    //

}
