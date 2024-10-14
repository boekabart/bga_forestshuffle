<?php

namespace FOS\States;

use PDO;
use FOS\Core\Game;
use FOS\Core\Globals;
use FOS\Core\Notifications;
use FOS\Core\Engine;
use FOS\Core\Stats;
use FOS\Managers\Cards;
use FOS\Managers\Players;
use FOS\Models\Player;

trait GamesTrait
{
	public function stNextPlayer()
	{
		$this->activeNextPlayer();
		Globals::setCardTypes([]);
		if (Globals::getFirstPlayer() == Players::getActive()->getId()) Stats::inc("turns_number");
		$this->gamestate->nextState('');
	}

	public function stCheckClearing()
	{
		$this->clearClearing();
		$this->gamestate->nextState('');
	}

	public function clearClearing()
	{
		if (Cards::countInLocation('clearing') >= 10) {
			Cards::moveAllInLocation('clearing', 'discard');
			Notifications::clearClearing();
		}
	}

	public function stPreEndOfGame()
	{
		//update scores
		[$scores, $scoresByCards] = $this->getScores(true);

		Notifications::newScores($scores, $scoresByCards);

		$this->gamestate->nextState('');
	}
}
