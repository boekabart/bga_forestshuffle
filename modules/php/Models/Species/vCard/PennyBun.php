<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Penny Bun
 */

class PennyBun extends \FOS\Models\Species
{
    public $name = 'Penny Bun';
    public $nb = 2;
    public $tags = [MUSHROOM];
    public $cost = 2;

    //whenever you play a card atop a tree : [1 in card]
    public function specialEffect($card, $specie, $position)
    {
        if ($position == TOP && $card->isOnARealTree()) $this->takeOneCardEffect();
    }

    //
    public function bonus()
    {
    }

    //

}
