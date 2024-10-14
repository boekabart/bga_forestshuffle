<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * European Fat Dormouse
 */

class EuropeanFatDormouse extends \FOS\Models\Species
{
    public $name = 'European Fat Dormouse';
    public $nb = 4;
    public $tags = [PAW];
    public $cost = 1;

    //15 [points] if a [bat] also occupies this tree
    public function score($playerId, $forests)
    {
        if (!$this->card->isOnARealTree()) return 0;
        $onThisTree = Cards::getInLocationQ('table', $this->card->getState())
            ->where('tree', $this->card->getTree())->get();

        foreach ($onThisTree as $id => $card) {
            if ($card->getVisible('species')->is(BAT)) return 15;
        }
        return 0;
    }
}
