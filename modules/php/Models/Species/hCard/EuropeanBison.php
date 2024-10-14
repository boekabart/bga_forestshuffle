<?php

namespace FOS\Models\Species\hCard;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class EuropeanBison extends \FOS\Models\Species
{
    public $name = 'European Bison';
    public $nb = 3;
    public $tags = [WOODLAND, CLOVEN];
    public $cost = 3;

    //[action ⟳]
    public function effect()
    {
        return $this->playAgain();
    }

    //
    public function bonus()
    {
    }

    //2 [points] x [oak], [beech]
    public function score($playerId, $forests)
    {
        return 2 * Players::get($playerId)->countInForest('tree_symbol', OAK) +
            2 * Players::get($playerId)->countInForest('tree_symbol', BEECH);
    }
}
