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
use FOS\Models\Species;
use FOS\Models\Species\hCard\BrownBear;

trait HibernateTrait
{
	public function stBearHibernate()
	{
		$cards = Globals::getCardsInClearing(); //get the stored ids
		$activePlayer = Players::getActive();
		Cards::move($cards, 'cave', $activePlayer->getId());
		Notifications::takeAllCardsFromClearing($activePlayer, $cards);
		$this->gamestate->nextState('end');
	}

	public function argHibernate()
	{
		return [
			'_private' => [
				'active' => [
					'playableCards' => array_keys(Players::getActive()->getCardsInHand(true)->toAssoc())
				]
			]
		];
	}

	public function argTakeCardFromClearing()
	{
		$args = Globals::getFirstArgs();

		if (!$args['nb']) {
			$args['nb'] = count(Cards::getAllOfTypeFromClearing($args['types']));
		}
		//limit $args['nb'] to 10 - cards in hand
		if ($args['where'] === HAND) {
			$args['nb'] = min($args['nb'], 10 - Players::getActive()->getCardsInHand(false));
		}

		$args['playableCards'] = Cards::getInLocation('clearing')->getIds();
		$args['suffix'] = ($args['where'] == HAND) ? 'tohand' : '';
		return $args;
	}

	public function stTakeCardFromClearing()
	{
		$args = $this->getArgs();

		//can be automatic if it's ALL card of types
		if ($args['types']) { //no choice ?? //TODO how if you have more than 10.
			$player = Players::getActive();

			$cardIds = Cards::getAllOfTypeFromClearing($args['types']);

			if ($args['where'] === CAVE) {
				Cards::move($cardIds, $args['where'], $player->getId());
				Notifications::takeAllCardsFromClearing($player, $cardIds, $args['specie']);
			} else if (count($cardIds) == $args['nb']) { //to hand, can't be automatic if card to take are not ALL
				Cards::move($cardIds, $args['where'], $player->getId());
				foreach ($cardIds as $cardId) {
					$card = Cards::get($cardId);
					Notifications::takeCardFromClearing($player, $card);
				}
			} else {
				//only give extratime
				$this->stPlayerTurn();
				return;
			}

			[$scores, $scoresByCards] = $this->getScores();
			Notifications::newScores($scores, $scoresByCards);
			Game::transition('end');
		} else {
			//only give extratime
			$this->stPlayerTurn();
		}
	}

	public function actChooseCard($cardIds)
	{
		//get infos
		$pId = Game::get()->getCurrentPlayerId();
		$currentPlayer = Players::get($pId);

		//check rules
		self::checkAction('actChooseCard');

		$args = $this->getArgs();

		//little hack for retrocompatibility
		if (!isset($args['nb'])) {
			$args['nb'] = 2;
			$args['where'] = CAVE;
		}

		foreach ($cardIds as $card) {
			if (!in_array($card, $args['playableCards'])) {
				throw new \BgaVisibleSystemException("This card $card is not on clearing");
			}
		}
		if (count($cardIds) > $args['nb']) {
			throw new \BgaVisibleSystemException("You can't place more than {$args['nb']} cards in your cave now");
		}

		//process
		Cards::move($cardIds, $args['where'], $pId);

		//notification
		if ($args['where'] == CAVE) {
			Notifications::hibernateGypaetus($currentPlayer, $cardIds);
		} else {
			foreach ($cardIds as $cardId) {
				Notifications::takeCardFromClearing($currentPlayer, Cards::get($cardId));
			}
		}

		//update scores
		[$scores, $scoresByCards] = $this->getScores();
		Notifications::newScores($scores, $scoresByCards);

		Game::transition('end');
	}

	public function argHibernateGypaetus()
	{
		return [
			'playableCards' => Cards::getInLocation('clearing')->getIds()
		];
	}

	public function actHibernateGypaetus($cardIds)
	{
		//get infos
		$pId = Game::get()->getCurrentPlayerId();
		$currentPlayer = Players::get($pId);

		//check rules
		self::checkAction(HIBERNATE_GYPAETUS);

		$args = $this->getArgs();

		foreach ($cardIds as $card) {
			if (!in_array($card, $args['playableCards'])) {
				throw new \BgaVisibleSystemException("This card $card is not on clearing");
			}
		}
		if (count($cardIds) > 2) {
			throw new \BgaVisibleSystemException("You can't place more than two cards in your cave now");
		}

		//process
		Cards::move($cardIds, 'cave', $pId);

		//notification
		Notifications::hibernateGypaetus($currentPlayer, $cardIds);

		//update scores
		[$scores, $scoresByCards] = $this->getScores();
		Notifications::newScores($scores, $scoresByCards);

		$this->gamestate->nextState('end');
	}

	public function actHibernate($cardIds)
	{
		//get infos
		$pId = Game::get()->getCurrentPlayerId();
		$currentPlayer = Players::get($pId);

		//check rules
		self::checkAction(HIBERNATE);

		$args = $this->getArgs();

		foreach ($cardIds as $card) {
			if (!in_array($card, $args['_private']['active']['playableCards'])) {
				throw new \BgaVisibleSystemException("This card $card is not in your hand");
			}
		}

		//process
		Cards::move($cardIds, 'cave', $pId);

		$newCards = [];

		for ($i = 0; $i < count($cardIds); $i++) {
			$card = Cards::pickCard();
			if (!$card) break;
			Cards::move($card->getId(), 'hand', $pId);
			$newCards[] = $card;
		}

		//notification
		Notifications::hibernate($currentPlayer, $cardIds, $newCards);

		//update scores
		[$scores, $scoresByCards] = $this->getScores();
		Notifications::newScores($scores, $scoresByCards);

		//stats
		//TODO
		if (Cards::countInLocation(W_CARD) == 3) {
			$this->gamestate->nextState(WINTER);
			return;
		}
		$this->gamestate->nextState('end');
	}

	public function stHibernateBear()
	{
		$player = Players::getActive();
		Notifications::takeAllCardsFromClearing($player, Globals::getCardsInClearing());
		Cards::moveAllInLocation('clearing', 'cave', null, $player->getId());

		$this->gamestate->nextState('end');
	}
}
