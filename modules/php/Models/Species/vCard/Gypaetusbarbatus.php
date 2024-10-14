<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * GypaetusBarbatus
 */

class GypaetusBarbatus extends \FOS\Models\Species
{
    public $name = 'Gypaetus Barbatus';
    public $nb = 3;
    public $tags = [BIRD, MOUNTAIN];
    public $cost = 1;

    //
    public function effect()
    {
        return $this->takeCardsFromClearing(2, [], CAVE);
    }

    //
    public function bonus()
    {
    }

    //Gain 1 point for each card in Cave
    public function score($playerId, $forests)
    {
        return Cards::countInLocation('cave', $playerId);
    }
}
