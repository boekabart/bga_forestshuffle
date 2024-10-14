<?php

namespace FOS\Models\Species\vCard;

use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Mole
 */

class Mole extends \FOS\Models\Species
{
    public $name = 'Mole';
    public $nb = 2;
    public $tags = [PAW];
    public $cost = 2;

    //immediately play any number of cards by paying their cost
    public function effect()
    {
        return $this->playAll();
    }

    //
    public function bonus()
    {
    }

    //

}
