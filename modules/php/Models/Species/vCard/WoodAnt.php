<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Wood Ant
 */

class WoodAnt extends \FOS\Models\Species
{
    public $name = 'Wood Ant';
    public $nb = 3;
    public $tags = [INSECT];
    public $cost = 1;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //2 [points] per card below a tree
    public function score($playerId, $forests)
    {
        return 2 * Cards::getInLocationQ('table', $playerId)
            ->where('position', BOTTOM)
            ->get()->filter(fn ($card) => $card->isOnARealTree())->count();
    }
}
