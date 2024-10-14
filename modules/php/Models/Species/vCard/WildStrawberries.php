<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Wild Strawberries
 */

class WildStrawberries extends \FOS\Models\Species
{
    public $name = 'Wild Strawberries';
    public $nb = 3;
    public $tags = [PLANT];
    public $cost = 0;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //10 [points] if all 8 different tree species are in your forest
    public function score($playerId, $forests)
    {

        return $this->countDifferentFromSpecificTag($playerId, TREE) >= 8 ? 10 : 0;
    }
}
