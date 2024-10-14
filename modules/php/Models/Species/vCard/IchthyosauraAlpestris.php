<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Ichthyosaura Alpestris
 */

class IchthyosauraAlpestris extends \FOS\Models\Species
{
    public $name = 'Ichthyosaura Alpestris';
    public $nb = 3;
    public $tags = [AMPHIBIAN, MOUNTAIN];
    public $cost = 1;

    //
    public function effect()
    {
    }

    //Play a card with a mountain symbol for free (you can’t use its effect or bonus)
    public function bonus()
    {
        return $this->freePlay([MOUNTAIN, INSECT]);
    }

    //Gain points according to the number of salamander you have
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 2, INSECT);
    }
}
