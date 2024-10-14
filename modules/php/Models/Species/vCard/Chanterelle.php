<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Chanterelle
 */

class Chanterelle extends \FOS\Models\Species
{
    public $name = 'Chanterelle';
    public $nb = 2;
    public $tags = [MUSHROOM];
    public $cost = 2;

    //whenever you play a card with [tree] : [1 in card]
    public function specialEffect($card, $specie, $position)
    {
        if (in_array(TREE, $specie->tags) || $position == SAPLING) $this->takeOneCardEffect();
    }

    //
    public function bonus()
    {
    }

    //

}
