<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Red Fox
 */

class RedFox extends \FOS\Models\Species
{
    public $name = 'Red Fox';
    public $nb = 5;
    public $tags = [PAW];
    public $cost = 2;

    //[1 in card] per European Hare
    public function effect()
    {
        $count = $this->countHares(Players::getActiveId());

        for ($i = 0; $i < $count; $i++) {
            if (!$this->takeOneCardEffect()) break;
        }
    }

    //
    public function bonus()
    {
    }

    //2 [points] per European Hare
    public function score($playerId, $forests)
    {
        return $this->countHares($playerId) * 2;
    }
}
