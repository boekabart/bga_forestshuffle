<?php

namespace FOS\Models\Species\hCard;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class EuropeanWildcat extends \FOS\Models\Species
{
    public $name = EUROPEAN_WILDCAT;
    public $nb = 3;
    public $tags = [WOODLAND, PAW];
    public $cost = 1;

    //[1 in card] from the clearing
    public function effect()
    {
        return $this->takeCardsFromClearing(1, [], HAND, EUROPEAN_WILDCAT);
    }

    //
    public function bonus()
    {
    }

    //1 [points] x [woodland edge card]
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 1, WOODLAND);
    }
}
