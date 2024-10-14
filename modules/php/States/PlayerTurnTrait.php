<?php

namespace FOS\States;

use PDO;
use FOS\Core\Game;
use FOS\Core\Globals;
use FOS\Core\Notifications;
use FOS\Core\Engine;
use FOS\Core\Stats;
use FOS\Helpers\Utils;
use FOS\Managers\Cards;
use FOS\Managers\Players;
use FOS\Models\Player;
use FOS\Models\Species;

trait PlayerTurnTrait
{

	public function argPlayerTurn()
	{
		$cardsIds = array_keys(Players::getActive()->getCardsInHand(true)->toAssoc());
		return [
			'_private' => [
				'active' => [
					'playableCards' => $cardsIds,
					'takableCards' => (count($cardsIds) < 10)
						? array_keys(Cards::getInLocation('clearing')->toAssoc())
						: [],
					'canTake' => count($cardsIds) != 10
				]
			]
		];
	}

	public function stPlayerTurn()
	{
		$this->giveExtraTime(Players::getActive()->getId());
	}

	public function stRetake()
	{
		$args = $this->getArgs();

		if (!$args['_private']['active']['canTake']) {
			Notifications::tooManyCards(Players::getActive());
			$this->gamestate->nextState(PASS);
		} else if (count($args['_private']['active']['takableCards']) == 0) {
			$this->actTakeCardFromDeck();
		} else {
			$this->giveExtraTime(Players::getActive()->getId());
		}
	}

	public function actTakeCardFromClearing($cardId)
	{
		//get infos
		$pId = Game::get()->getCurrentPlayerId();
		$currentPlayer = Players::get($pId);

		//check rules
		self::checkAction(TAKE_CARD);

		$args = $this->getArgs();
		if (!in_array($cardId, $args['_private']['active']['takableCards'])) {
			throw new \BgaVisibleSystemException("You can't take this card from clearing.");
		}

		if ($currentPlayer->getCardsInHand(false) >= 10) {
			throw new \BgaVisibleSystemException("You have already too much cards.");
		}

		//process
		Cards::move($cardId, 'hand', $pId);


		//notification
		Notifications::takeCardFromClearing($currentPlayer, Cards::get($cardId));

		//stats
		//TODO

		$this->gamestate->nextState(TAKE_CARD);
	}

	public function actTakeCardFromDeck()
	{
		//get infos
		$pId = Game::get()->getCurrentPlayerId();
		$currentPlayer = Players::get($pId);

		//check rules
		self::checkAction(TAKE_CARD);

		if ($currentPlayer->getCardsInHand(false) >= 10) {
			throw new \BgaVisibleSystemException("You have already too much cards.");
		}

		//process
		$card = Cards::pickCard();
		if (!$card) {
			$this->gamestate->nextState(WINTER);
			return;
		}

		Cards::move($card->getId(), 'hand', $pId);

		//notification
		Notifications::takeCardFromDeck($currentPlayer, $card);

		//stats
		//TODO

		$this->gamestate->nextState(TAKE_CARD);
	}

	public function actPlayCard($cardId, $cards = [], $treeId = null, $position = TREE)
	{
		//get infos
		$pId = Game::get()->getCurrentPlayerId();
		$currentPlayer = Players::get($pId);

		//check rules
		self::checkAction(PLAY_CARD);

		$args = $this->getArgs();
		//check if player has cards in hand
		if (!in_array($cardId, $args['_private']['active']['playableCards'])) {
			throw new \BgaVisibleSystemException("This card is not in your hand");
		}

		foreach ($cards as $card) {
			if (!in_array($card, $args['_private']['active']['playableCards'])) {
				throw new \BgaVisibleSystemException("This card $card is not in your hand");
			}
		}
		//check if player can play on that position
		//if card can be play 
		$card = Cards::get($cardId);
		$type = $card->getType();
		$specie = $card->getVisible('species', $position);
		$treeSymbol = $card->getVisible('tree_symbol', $position);
		if ((($type == TREE && $position != TREE) || ($type == H_CARD && $position != RIGHT && $position != LEFT)
				|| ($type == V_CARD && $position != TOP && $position != BOTTOM)) && $position != SAPLING
		) {
			Utils::die([$type, $position]);
			throw new \BgaVisibleSystemException("you can't play $cardId here !");
		}

		if ($treeId && !$currentPlayer->isAvailablePosition($treeId, $position, $specie)) {
			throw new \BgaVisibleSystemException("you can't play $cardId on this tree !");
		}

		if (!$treeId && ($position != TREE && $position !=  SAPLING)) {
			throw new \BgaVisibleSystemException("you can't play this card $cardId as a new tree !");
		}

		if (!$treeId) {
			$treeId = $currentPlayer->getNextTreeId();
		}
		//check if cost is ok
		$overcost = $args['overcost'] ?? 0;
		if ($specie->cost + $overcost != count($cards) && !Game::isNoCostStateId()) {
			throw new \BgaVisibleSystemException("You didn't pay the right cost for card $cardId !");
		}

		//process
		$card->setLocation('betweenHandAndtable');
		$card->setPosition($position);
		$card->setTree($treeId);

		Cards::move($cards, 'clearing');

		//notification
		Notifications::playCard($currentPlayer, $card, $specie, $cards, $treeId, $position);

		//pick a new card from deck to clearing for each new tree
		if ($card->isRealTree() || $position == SAPLING) {
			// //if it's in play_all state postpone at the end of turn
			// if ($this->gamestate->state_id() == ST_PLAY_ALL) {
			// 	$pendingActions = $currentPlayer->getPendingAction();
			// 	$pendingActions[] = ADD_TO_CLEARING;
			// 	$currentPlayer->setPendingAction($pendingActions);
			// } else {
			$cardFromDeck = Cards::pickCard();
			if (!$cardFromDeck) {
				$card->setLocation('table');
				$this->gamestate->nextState(WINTER);
				return;
			}
			Cards::move($cardFromDeck->getId(), 'clearing');
			Notifications::newCardOnClearing($cardFromDeck);
			// }
		}


		//special effects
		foreach ($currentPlayer->getForest() as $specieInForest) {
			$specieInForest->specialEffect($card, $specie, $position);
		}

		$card->setLocation('table');

		if (Cards::countInLocation(W_CARD) == 3) {
			$this->gamestate->nextState(WINTER);
			return;
		}

		$pendingAction = PLAY_CARD;


		//do not act effect if it's FREE PLAY or SAPLING
		if (!Game::isNoCostStateId() || $position == SAPLING) {
			$pendingAction = $specie->effect() ?? $pendingAction;
		}

		if (Cards::countInLocation(W_CARD) == 3) {
			$this->gamestate->nextState(WINTER);
			return;
		}

		$bonus = true;
		foreach ($cards as $cardToPay) {
			if (!Cards::get($cardToPay)->hasTreeSymbol($treeSymbol)) {
				$bonus = false;
				break;
			}
		}

		//do not act bonus if it's FREE PLAY or SAPLING
		if (count($cards) && $bonus && (!Game::isNoCostStateId() && $position != SAPLING)) {
			$pendingAction = $specie->bonus() ?? $pendingAction;
		}

		[$scores, $scoresByCards] = $this->getScores();

		Notifications::newScores($scores, $scoresByCards);

		if (Cards::countInLocation(W_CARD) == 3) {
			$this->gamestate->nextState(WINTER);
			return;
		}

		//stats
		//TODO

		//TODO remove OR CONDITION later just to be sure it's retrocompatible
		if (Game::isStateId(ST_FREE_PLAY_ALL)) {
			$this->gamestate->nextState(PLAY_AGAIN);
		} else if (Game::isStateId(ST_FREE_PLAY)) {
			Game::transition('end');
		} else if ($this->gamestate->state_id() != ST_PLAY_ALL) {

			$this->dispatch();
		} else {
			switch ($pendingAction) {
				case RACCOON:
					Notifications::message(clienttranslate('${player_name} will be able to put some cards in his cave at the end of his turn'), ['player' => $currentPlayer]);
					break;
				case PLAY_AGAIN:
					Notifications::message(clienttranslate('${player_name} will get a new turn at the end of his turn'), ['player' => $currentPlayer]);
					break;

				case FREE_PLAY:
					Notifications::message(clienttranslate('${player_name} will play a free card at the end of his turn'), ['player' => $currentPlayer]);
					break;

				case FREE_PLAY_ALL:
					Notifications::message(clienttranslate('${player_name} will play one or more free card(s) at the end of his turn'), ['player' => $currentPlayer]);
					break;

				case PLAY_ALL:
					Notifications::message(clienttranslate('${player_name} will be able to play others cards by paying their cost at the end of his turn'), ['player' => $currentPlayer]);
					break;

				case TAKE_CARD_FROM_CLEARING:
					Notifications::message(clienttranslate('${player_name} will be able to take some cards from clearing at the end of his turn'), ['player' => $currentPlayer]);
					break;

				case GYPAETUS:
					Notifications::message(clienttranslate('${player_name} will be able to place two cards from the clearing to his cave at the end of his turn'), ['player' => $currentPlayer]);
					break;
			}

			if ($position == TREE || $position == SAPLING) {
				Globals::savePoint($currentPlayer);
			} else {
				Globals::setUndoable(1);
			}
			Game::transition(PLAY_CARD);
		}
	}

	public function getScores($bForce = false)
	{
		if (!Globals::getIsScoreVisible() && !$bForce) {
			return [0, []];
		}

		$players = Players::getAll();

		$forests = [];
		$scores = [];
		$scoresByCards = [];

		foreach ($players as $playerId => $player) {
			$forests[$playerId] = $player->getForest();
		}

		foreach ($players as $playerId => $player) {

			$butterfliesScored = false;
			$horseChestnutScored = false;
			$fireSalamanderScored = false;
			$fireFliesScored = false;

			$score = 0;
			$scoreForTree = 0;
			$scoreForTopAndBottom = 0;
			$scoreForLeftAndRight = 0;
			foreach ($forests[$playerId] as $specie) {

				//calculate score
				$sco = $specie->score($playerId, $forests) ?? 0;


				//add score to card (for hint display on js) except for sapling
				if ($specie->card->getPosition() != SAPLING) {
					$scoresByCards[$specie->card->getId()] = $sco;
				}

				//add score to total score except for table score if it has been already counted
				if ($specie->is(BUTTERFLY)) {
					if ($butterfliesScored) continue;
					else $butterfliesScored = true;
				} else if ($specie->name == HORSE_CHESTNUT) {
					if ($horseChestnutScored) continue;
					else $horseChestnutScored = true;
				} else if ($specie->name == 'Fire Salamander') {
					if ($fireSalamanderScored) continue;
					else $fireSalamanderScored = true;
				} else if ($specie->name == 'Fireflies') {
					if ($fireFliesScored) continue;
					else $fireFliesScored = true;
				}

				if ($specie->card->getPosition() == LEFT || $specie->card->getPosition() == RIGHT) {
					$scoreForLeftAndRight += $sco;
				} else if ($specie->card->getPosition() == TOP || $specie->card->getPosition() == BOTTOM) {
					$scoreForTopAndBottom += $sco;
					//hack for retro compatibility purpose
				} else if ($specie->card->getPosition() == 'tree' || $specie->card->getPosition() == TREE || $specie->card->getPosition() == SAPLING) {
					$scoreForTree += $sco;
				}

				$score += $sco;
			}
			$cave = Cards::countInLocation('cave', $playerId);
			$score += $cave;
			$scores[$playerId] = $score;
			Players::get($playerId)->setScore($score);

			if ($bForce) {
				Stats::set($cave, STAT_NAME_CAVE_POINTS, $player);
				Stats::set($scoreForTree, STAT_NAME_TREE_POINTS, $player);
				Stats::set($scoreForLeftAndRight, STAT_NAME_LEFT_RIGHT_POINTS, $player);
				Stats::set($scoreForTopAndBottom, STAT_NAME_TOP_BOTTOM_POINTS, $player);
				Stats::set(count($forests[$playerId]), STAT_NAME_PLAYED_CARDS, $player);
			}
		}

		return [$scores, $scoresByCards];
	}

	// public function actCall($pId2, $color, $value, $pId = null)
	// {
	// 	// get infos
	// 	if (!$pId) {
	// 		$pId = Game::get()->getCurrentPlayerId();
	// 		self::checkAction(ACT_CALL);
	// 	}

	// 	$currentPlayer = Players::get($pId);
	// 	$calledPlayer = Players::get($pId2);

	// 	$args = $this->getArgs();

	// 	if (!in_array($calledPlayer, $args['callablePlayers'])) {
	// 		throw new \BgaVisibleSystemException("You can't call this player.");
	// 	}

	// 	foreach ($args['uncallableCards'] as $id => $card) {
	// 		if ($card->getColor() == $color && $card->getValue() == $value)
	// 			throw new \BgaVisibleSystemException("You can't ask this card.");
	// 	}

	// 	Notifications::call($currentPlayer, $calledPlayer, $color, $value);

	// 	Globals::setCalledPlayer($pId2);
	// 	Globals::setCalledValue($value);
	// 	Globals::setCalledColor($color);


	// 	$this->gamestate->nextState('');
	// }
}
