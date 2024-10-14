<?php

namespace FOS\Models\Species\vCard;

use FOS\Core\Globals;
use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class EuropeanWaterVole extends \FOS\Models\Species
{
    public $name = 'European Water Vole';
    public $nb = 2;
    public $tags = [WOODLAND, PAW];
    public $cost = 2;

    //immediately play any number of tree sapling
    public function effect()
    {
        return $this->freePlayAll(SAPLING);
    }

    //
    public function bonus()
    {
        return $this->playAgain();
    }
}
