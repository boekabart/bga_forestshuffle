<?php

namespace FOS\Models\Species\hCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Lepus Timidus
 */

class LepusTimidus extends \FOS\Models\Species
{
    public $name = 'Lepus Timidus';
    public $nb = 3;
    public $tags = [PAW, MOUNTAIN];
    public $cost = 0;

    //Counts as a European Hare
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //Gain 1 point for each European Hare
    public function score($playerId, $forests)
    {
        return $this->countHares($playerId);
    }
}
