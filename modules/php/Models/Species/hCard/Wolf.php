<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Wolf
 */

class Wolf extends \FOS\Models\Species
{
    public $name = 'Wolf';
    public $nb = 4;
    public $tags = [PAW];
    public $cost = 3;

    //[1 card] × [deer]
    public function effect()
    {
        $count = Players::getActive()->countInForest('tags', DEER);

        for ($i = 0; $i < $count; $i++) {
            if (!$this->takeOneCardEffect()) break;
        }
    }

    //[action ⟳]
    public function bonus()
    {
        return $this->playAgain();
    }

    //5 [points] × [deer]
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 5, DEER);
    }
}
