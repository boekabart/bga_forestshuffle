<?php

namespace FOS\Models\Species\hCard;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class EuropeanPolecat extends \FOS\Models\Species
{
    public $name = 'European Polecat';
    public $nb = 3;
    public $tags = [WOODLAND, PAW];
    public $cost = 2;

    //
    public function effect()
    {
    }

    //[action ⟳]
    public function bonus()
    {
        return $this->playAgain();
    }

    //10 [points] if alone on a tree
    public function score($playerId, $forests)
    {
        $onThisTree = Cards::getInLocationQ('table', $this->card->getState())
            ->where('tree', $this->card->getTree())->get();

        return $onThisTree->count() == 2 ? 10 : 0;
    }
}
