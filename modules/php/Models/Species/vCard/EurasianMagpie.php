<?php

namespace FOS\Models\Species\vCard;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class EurasianMagpie extends \FOS\Models\Species
{
    public $name = EURASIAN_MAGPIE;
    public $nb = 3;
    public $tags = [WOODLAND, BIRD];
    public $cost = 1;

    //[1 in card] from the clearing
    public function effect()
    {
        return $this->takeCardsFromClearing(1, [], HAND, EURASIAN_MAGPIE);
    }

    //[2 in card] from the clearing into your cave
    public function bonus()
    {
        return $this->takeCardsFromClearing(2, [], CAVE, EURASIAN_MAGPIE);
    }

    //3 [points]
    public function score($playerId, $forests)
    {
        return 3;
    }
}
