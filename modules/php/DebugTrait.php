<?php

namespace FOS;

use FOS\Core\Globals;
use FOS\Core\Game;
use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Helpers\Log;
use FOS\Managers\Cards;
use FOS\Models\Player;
use FOS\Models\Species;
use FOS\Models\Species\Sapling;

trait DebugTrait
{

  function debug_isrealTree()
  {
    var_dump(Cards::get(140)->isRealTree());
  }
  function chooseCard($cardId)
  {
    Cards::insertOnTop($cardId, 'deck');
  }

  function test()
  {
    die(var_dump(Cards::getAllOfTypeFromClearing([BAT])));
  }

  // ████                         █████ ██████████            █████                        
  //░░███                        ░░███ ░░███░░░░███          ░░███                         
  // ░███   ██████   ██████    ███████  ░███   ░░███  ██████  ░███████  █████ ████  ███████
  // ░███  ███░░███ ░░░░░███  ███░░███  ░███    ░███ ███░░███ ░███░░███░░███ ░███  ███░░███
  // ░███ ░███ ░███  ███████ ░███ ░███  ░███    ░███░███████  ░███ ░███ ░███ ░███ ░███ ░███
  // ░███ ░███ ░███ ███░░███ ░███ ░███  ░███    ███ ░███░░░   ░███ ░███ ░███ ░███ ░███ ░███
  // █████░░██████ ░░████████░░████████ ██████████  ░░██████  ████████  ░░████████░░███████
  //░░░░░  ░░░░░░   ░░░░░░░░  ░░░░░░░░ ░░░░░░░░░░    ░░░░░░  ░░░░░░░░    ░░░░░░░░  ░░░░░███
  //                                                                               ███ ░███
  //                                                                              ░░██████ 
  //                                                                               ░░░░░░  

  public function loadBugReportSQL(int $reportId, array $studioPlayers): void
  {
    $prodPlayers = $this->getObjectListFromDb("SELECT `player_id` FROM `player`", true);
    $prodCount = count($prodPlayers);
    $studioCount = count($studioPlayers);
    if ($prodCount != $studioCount) {
      throw new BgaVisibleSystemException("Incorrect player count (bug report has $prodCount players, studio table has $studioCount players)");
    }

    // SQL specific to your game
    // For example, reset the current state if it's already game over
    $sql = [
      "UPDATE `global` SET `global_value` = 10 WHERE `global_id` = 1 AND `global_value` = 99"
    ];
    foreach ($prodPlayers as $index => $prodId) {
      $studioId = $studioPlayers[$index];
      // SQL common to all games
      $sql[] = "UPDATE `player` SET `player_id` = $studioId WHERE `player_id` = $prodId";
      $sql[] = "UPDATE `global` SET `global_value` = $studioId WHERE `global_value` = $prodId";
      $sql[] = "UPDATE `stats` SET `stats_player_id` = $studioId WHERE `stats_player_id` = $prodId";
      $sql[] = "UPDATE `bga_globals` set `name` = REPLACE(`name`, '$prodId', '$studioId'), `value` = REPLACE(`value`, '$prodId', '$studioId')";

      // SQL specific to your game
      $sql[] = "UPDATE cards SET card_state=$studioId WHERE card_state=$prodId";
      $sql[] = "UPDATE user_preferences SET player_id=$studioId WHERE player_id=$prodId";
      $sql[] = "UPDATE global_variables SET name = REPLACE(name, '$prodId', '$studioId')";
    }
    foreach ($sql as $q) {
      $this->DbQuery($q);
    }
    $this->reloadPlayersBasicInfos();
  }

  /*
   * loadBug: in studio, type loadBug(20762) into the table chat to load a bug report from production
   * client side JavaScript will fetch each URL below in sequence, then refresh the page
   */
  public function loadBug($reportId)
  {
    $db = explode('_', self::getUniqueValueFromDB("SELECT SUBSTRING_INDEX(DATABASE(), '_', -2)"));
    $game = $db[0];
    $tableId = $db[1];
    self::notifyAllPlayers(
      'loadBug',
      "Trying to load <a href='https://boardgamearena.com/bug?id=$reportId' target='_blank'>bug report $reportId</a>",
      [
        'urls' => [
          // Emulates "load bug report" in control panel
          "https://studio.boardgamearena.com/admin/studio/getSavedGameStateFromProduction.html?game=$game&report_id=$reportId&table_id=$tableId",

          // Emulates "load 1" at this table
          "https://studio.boardgamearena.com/table/table/loadSaveState.html?table=$tableId&state=1",

          // Calls the function below to update SQL
          "https://studio.boardgamearena.com/1/$game/$game/loadBugSQL.html?table=$tableId&report_id=$reportId",

          // Emulates "clear PHP cache" in control panel
          // Needed at the end because BGA is caching player info
          "https://studio.boardgamearena.com/admin/studio/clearGameserverPhpCache.html?game=$game",
        ],
      ]
    );
  }

  /*
   * loadBugSQL: in studio, this is one of the URLs triggered by loadBug() above
   */
  public function loadBugSQL($reportId)
  {
    $studioPlayer = self::getCurrentPlayerId();
    $players = self::getObjectListFromDb('SELECT player_id FROM player', true);

    // Change for your game
    // We are setting the current state to match the start of a player's turn if it's already game over
    $sql = ['UPDATE global SET global_value=5 WHERE global_id=1 AND global_value=99'];
    // $sql[] = 'ALTER TABLE `gamelog` ADD `cancel` TINYINT(1) NOT NULL DEFAULT 0;';
    $map = [];
    foreach ($players as $pId) {
      $map[(int) $pId] = (int) $studioPlayer;

      // All games can keep this SQL
      $sql[] = "UPDATE player SET player_id=$studioPlayer WHERE player_id=$pId";
      $sql[] = "UPDATE global SET global_value=$studioPlayer WHERE global_value=$pId";
      $sql[] = "UPDATE stats SET stats_player_id=$studioPlayer WHERE stats_player_id=$pId";

      // Add game-specific SQL update the tables for your game
      $sql[] = "UPDATE cards SET card_state=$studioPlayer WHERE card_state=$pId";
      $sql[] = "UPDATE user_preferences SET player_id=$studioPlayer WHERE player_id=$pId";
      $sql[] = "UPDATE global_variables SET name = REPLACE(name, '$pId', '$studioPlayer')";

      // This could be improved, it assumes you had sequential studio accounts before loading
      // e.g., quietmint0, quietmint1, quietmint2, etc. are at the table
      $studioPlayer++;
    }
    $msg =
      "<b>Loaded <a href='https://boardgamearena.com/bug?id=$reportId' target='_blank'>bug report $reportId</a></b><hr><ul><li>" .
      implode(';</li><li>', $sql) .
      ';</li></ul>';
    self::warn($msg);
    self::notifyAllPlayers('message', $msg, []);

    foreach ($sql as $q) {
      self::DbQuery($q);
    }

    /******************
     *** Fix Globals ***
     ******************/

    // Turn orders
    // Globals::setDebugMode();
    // $turnOrders = Globals::getCustomTurnOrders();
    // foreach ($turnOrders as $key => &$order) {
    //   $t = [];
    //   foreach ($order['order'] as $pId) {
    //     $t[] = $map[$pId];
    //   }
    //   $order['order'] = $t;
    // }
    // Globals::setCustomTurnOrders($turnOrders);

    // // Engine
    // PGlobals::fetch();
    // $flows = PGlobals::getAll('engine');
    // foreach ($flows as $pId => $engine) {
    //   self::loadDebugUpdateEngine($engine, $map);
    //   PGlobals::setEngine($pId, $engine);
    // }

    // First player
    $fp = Globals::getFirstPlayer();
    Globals::setFirstPlayer($map[$fp]);

    // Globals::unsetDebugMode();

    self::reloadPlayersBasicInfos();
  }

  public function invitePlayersToAlpha($groupId)
  {
    $playersList = [
      'firgon',
    ];

    Notifications::invitePlayersToAlpha(
      'invitePlayers',
      "Trying invite all your contacts to Alpha",
      [
        'urls' => [
          "https://boardgamearena.com/player/player/findplayer.html?q=firgon&start=0&count=1",
          "https://boardgamearena.com/community/community/inviteGroup.html?id=14792051&player=",
          "https://boardgamearena.com/community/community/promoteGroupAdmin.html?id=14792051&player="
        ],
      ]
    );
  }
}
