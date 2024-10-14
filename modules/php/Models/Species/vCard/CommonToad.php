<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Common Toad
 */

class CommonToad extends \FOS\Models\Species
{
    public $name = 'Common Toad';
    public $nb = 6;
    public $tags = [AMPHIBIAN];
    public $cost = 0;

    //up to 2 Common Toads may share this spot
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //5 [points] if 2 Common Toads share this spot
    public function score($playerId, $forests)
    {
        return (2 == Cards::getInLocationQ('table', $playerId)
            ->where('tree', $this->card->getTree())
            ->where('position', $this->card->getPosition())
            ->get()->count()) ? 5 : 0;
    }
}
