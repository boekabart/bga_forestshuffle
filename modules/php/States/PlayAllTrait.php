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

trait PlayAllTrait
{

	public function argPlayAll()
	{
		return [
			'_private' => [
				'active' => [
					'playableCards' => Players::getActive()->getCardsInHand(true)->getIds(),
					'canUndo' => Globals::getUndoable() == 1,
					'takableCards' => [],
					'canTake' => false,
				]
			],
			'overcost' => Globals::getFirstArgs()['overcost'] ?? 0,
			'suffix' => (Globals::getFirstArgs()['overcost'] ?? 0) ? 'overcost' : ''
		];
	}

	public function actUndo()
	{
		//sanity check
		self::checkAction('undo');

		$player = Players::getActive();

		//retrieve cards in hand at beginning of the turn
		$cards = Globals::getPlayableCards();
		// keep only those which are not still in hand
		$cards = array_diff($cards, $player->getCardsInHand(true)->getIds());

		// move all to hands
		foreach ($cards as $cardId) {
			$card = Cards::get($cardId);
			$card->setLocation('hand');
			$card->setState($player->getId());
			$card->setPosition(0);
			$card->setTree(0);
		}

		//reset all flags
		Globals::restoreSavePoint($player);

		// notify
		Notifications::cancelPlayAll($player, $cards);

		//update scores
		[$scores, $scoresByCards] = $this->getScores();
		Notifications::newScores($scores, $scoresByCards);

		$this->gamestate->nextState(PLAY_CARD); // transition to single player state (i.e. beginning of player actions for this turn)
	}

	public function actPass()
	{
		//check rules
		self::checkAction(PASS);
		Game::transition('end');
	}

	public function stPerformActions()
	{
		$cardsToTake = Globals::getCardsToTake();

		for ($i = 0; $i < count($cardsToTake); $i++) {
			if (!Species::takeOneCardPostponed($cardsToTake[$i])) break;
		}
		Globals::setCardsToTake([]);

		if (Cards::countInLocation(W_CARD) == 3) {
			$this->gamestate->nextState(WINTER);
			return;
		}

		$this->dispatch();
	}

	public function dispatch()
	{
		$player = Players::getActive();
		$pendingAction = $player->getActionFromPendingAction();


		switch ($pendingAction) {
			case PLAY_AGAIN:
				if ($player->hasOnlyPlayAgainAction()) {
					$this->clearClearing();
					Notifications::message(clienttranslate('${player_name} gets a new turn'), ['player' => $player]);
				} else {
					$player->addActionToPendingAction($pendingAction);
					return $this->dispatch();
				}
				break;

			case FREE_PLAY:
				Notifications::message(clienttranslate('${player_name} can play a free card'), ['player' => $player]);
				break;

			case FREE_PLAY_ALL:
				$cardsIds = array_keys($player->getCardsInHand(true)->toAssoc());
				Globals::setPlayableCards($cardsIds);
				Notifications::message(clienttranslate('${player_name} can play one or more free card(s)'), ['player' => $player]);
				break;

			case PLAY_ALL:
				$this->undoSavePoint();
				Globals::savePoint($player);
				Notifications::message(clienttranslate('${player_name} can play others cards by paying their cost'), ['player' => $player]);
				break;
		}

		$pendingAction = $pendingAction ?? 'end';

		$this->gamestate->nextState($pendingAction);
	}

	public function stDiscardAll()
	{
		Cards::moveAllInLocation('clearing', 'discard');
		Notifications::clearClearing(clienttranslate('Due to Wild Boar Female, all cards from the clearing are discarded'));
		$this->gamestate->nextState('end');
	}

	public function stAddToClearing()
	{
		$cardFromDeck = Cards::pickCard();
		if (!$cardFromDeck) {
			$this->gamestate->nextState(WINTER);
			return;
		}
		Cards::move($cardFromDeck->getId(), 'clearing');
		Notifications::newCardOnClearing($cardFromDeck);
		$this->gamestate->nextState('end');
	}
}
