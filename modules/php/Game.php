<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * Nertz implementation : © cardboardsphen, bga-dev@sphen.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */

declare(strict_types=1);

namespace Bga\Games\Nertz;

require_once(APP_GAMEMODULE_PATH . "module/table/table.game.php");
require_once("autoload.php");

use Bga\Games\Nertz\Helpers\DatabaseHelpers;
use Bga\Games\Nertz\Models\Card;

class Game extends \Table {
    use DatabaseHelpers;
    /**
     * Your global variables labels:
     *
     * Here, you can assign labels to global variables you are using for this game. You can use any number of global
     * variables with IDs between 10 and 99. If your game has options (variants), you also have to associate here a
     * label to the corresponding ID in `gameoptions.inc.php`.
     *
     * NOTE: afterward, you can get/set the global variables with `getGameStateValue`, `setGameStateInitialValue` or
     * `setGameStateValue` functions.
     */
    public function __construct() {
        parent::__construct();

        $this->initGameStateLabels([]);
    }

    /**
     * Game state arguments, example content.
     *
     * This method returns some additional information that is very specific to the `playerTurn` game state.
     *
     * @return array
     * @see ./states.inc.php
     */


    /**
     * Compute and return the current game progression.
     *
     * The number returned must be an integer between 0 and 100.
     *
     * This method is called each time we are in a game state with the "updateGameProgression" property set to true.
     *
     * @return int
     * @see ./states.inc.php
     */
    public function getGameProgression(): int {
        return 0;
    }

    /**
     * Player actions
     */


    /**
     * Game state actions
     */


    /**
     * This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
     * You can do whatever you want in order to make sure the turn of this player ends appropriately
     * (ex: pass).
     *
     * Important: your zombie code will be called when the player leaves the game. This action is triggered
     * from the main site and propagated to the gameserver from a server, not from a browser.
     * As a consequence, there is no current player associated to this action. In your zombieTurn function,
     * you must _never_ use `getCurrentPlayerId()` or `getCurrentPlayerName()`, otherwise it will fail with a
     * "Not logged" error message.
     *
     * @param array{ type: string, name: string } $state
     * @param int $active_player
     * @return void
     * @throws feException if the zombie mode is not supported at this game state.
     */
    protected function zombieTurn(array $state, int $activePlayer): void {
        $stateName = $state["name"];

        if ($state["type"] === "activeplayer") {
            switch ($stateName) {
                default: {
                        $this->gamestate->nextState("zombiePass");
                        break;
                    }
            }

            return;
        }

        throw new \feException("Zombie mode not supported at this game state: \"{$stateName}\".");
    }

    /**
     * Migrate database.
     *
     * You don't have to care about this until your game has been published on BGA. Once your game is on BGA, this
     * method is called everytime the system detects a game running with your old database scheme. In this case, if you
     * change your database scheme, you just have to apply the needed changes in order to update the game database and
     * allow the game to continue to run with your new version.
     *
     * @param int $from_version
     * @return void
     */
    public function upgradeTableDb($from_version) {
    }

    /*
     * Gather all information about current game situation (visible by the current player).
     *
     * The method is called each time the game interface is displayed to a player, i.e.:
     *
     * - when the game starts
     * - when a player refreshes the game page (F5)
     */
    protected function getAllDatas() {
        $result = [];

        // WARNING: We must only return information visible by the current player.
        $currentPlayerId = intval($this->getCurrentPlayerId());

        // Get information about players.
        // NOTE: you can retrieve some extra field you added for "player" table in `dbmodel.sql` if you need it.
        $result['players'] = $this->getCollectionFromDb(
            "SELECT player_id as id, player_score as score, player_color as color FROM player"
        );

        $result['cards'] = [];
        $players = self::getRowsFromDb("SELECT player_id as id from player");
        foreach ($players as $player) {
            $cardResults = [];

            $card = Card::fromDb(self::getFirstRowFromDb("SELECT rank, suit from cards where player = '$player->id' and location = 'nertz' order by order_in_pile desc limit 1"));
            $cardResults['nertz'] = $card?->getShortName();
            for ($i = 0; $i < 4; $i++) {
                $cards = self::getRowsFromDb("SELECT rank, suit from cards where player = '$player->id' and location = 'tableau' and pile_number = $i order by order_in_pile");
                $cardResults['tableau'][] = array_map(fn($card) => Card::fromDb($card)->getShortName(), $cards);
            }

            $result['cards'][$player->id] = $cardResults;
        }
        $piles = self::getRowsFromDb("SELECT distinct pile_number from cards where location = 'foundation' order by pile_number");
        foreach ($piles as $pile) {
            $cards = self::getRowsFromDb("SELECT rank, suit from cards where location = 'foundation' and pile_number = '$pile->pileNumber'");
            $result['cards']['foundations'][$pile->pileNumber] = array_map(fn($card) => Card::fromDb($card)->getShortName(), $cards);
        }

        $result['version'] = $this->getGameStateValue('game_db_version');

        return $result;
    }

    /**
     * Returns the game name.
     *
     * IMPORTANT: Please do not modify.
     */
    protected function getGameName() {
        return "nertz";
    }

    /**
     * This method is called only once, when a new game is launched. In this method, you must setup the game
     *  according to the game rules, so that the game is ready to be played.
     */
    protected function setupNewGame($players, $options = []) {
        $this->initPlayers($players);
        $this->initStatistics();

        $this->dealCards();

        // Activate first player once everything has been initialized and ready.
        $this->activeNextPlayer();
    }

    private function initPlayers($players): void {
        // Set the colors of the players with HTML color code. The default below is red/green/blue/orange/brown. The
        // number of colors defined here must correspond to the maximum number of players allowed for the gams.
        $gameinfos = $this->getGameinfos();
        $defaultColors = array("6f1926", "de324c", "f4895f", "f8e16f", "95cf92", "369acc", "9656a2", "cbabd1");
        shuffle($defaultColors);

        foreach ($players as $playerId => $player) {
            $color = array_shift($defaultColors);
            // Now you can access both $playerId and $player array
            $queryValues[] = vsprintf("('%s', '%s', '%s', '%s', '%s')", [
                $playerId,
                $color,
                $player["player_canal"],
                addslashes($player["player_name"]),
                addslashes($player["player_avatar"]),
            ]);
        }

        // Create players based on generic information.
        //
        // NOTE: You can add extra field on player table in the database (see dbmodel.sql) and initialize
        // additional fields directly here.
        $this->DbQuery(
            sprintf(
                "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES %s",
                implode(",", $queryValues)
            )
        );

        $this->reattributeColorsBasedOnPreferences($players, $gameinfos["player_colors"]);

        $this->reloadPlayersBasicInfos();
    }

    private function initStatistics(): void {
        // Init game statistics.
        //
        // NOTE: statistics used in this file must be defined in your `stats.inc.php` file.
    }

    private function dealCards(): void {
        $players = self::getRowsFromDb("SELECT player_id as id from player");

        // dump all cards in db
        $sql = "INSERT INTO cards (player, rank, suit, location, order_in_pile) values";
        foreach ($players as $player) {
            $order = 0;
            for ($rank = 1; $rank <= 13; $rank++) {
                foreach (['spade', 'heart', 'club', 'diamond'] as $suit) {
                    $sql .= "('$player->id', '$rank', '$suit', 'stock', '$order'),";
                    $order++;
                }
            }
        }
        $sql = substr($sql, 0, -1);
        $this->DbQuery($sql);

        // shuffle and deal cards
        foreach ($players as $player) {
            $order = range(0, 52);
            shuffle($order);
            for ($i = 0; $i < 13; $i++)
                $this->DbQuery("UPDATE cards set location = 'nertz', order_in_pile = '$i' where player = '$player->id' and location = 'stock' and order_in_pile = '$order[$i]'");
            for ($i = 0; $i < 4; $i++) {
                $next = $order[13 + $i];
                $this->DbQuery("UPDATE cards set location = 'tableau', pile_number = '$i', order_in_pile = 0 where player = '$player->id' and location = 'stock' and order_in_pile = '$next'");
            }

            $order = range(0, 34);
            shuffle($order);
            $cards = self::getRowsFromDb("SELECT rank, suit from cards where player = '$player->id' and location = 'stock'");
            for ($i = 0; $i < 35; $i++) {
                $rank = $cards[$i]->rank;
                $suit = $cards[$i]->suit;
                $this->DbQuery("UPDATE cards set order_in_pile = '$order[$i]' where player = '$player->id' and rank = '$rank' and suit = '$suit'");
            }
        }
    }
}
