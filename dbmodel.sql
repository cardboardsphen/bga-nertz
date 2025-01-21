
-- ------
-- BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
-- Nertz implementation : © cardboardsphen, bga-dev@sphen.com
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

-- ------
-- The check constraint doesn't work because as of 2024-12-28, BGA uses MySql 5.7.44, which doesn't support check constraints.
-- Since it doesn't cause an errors, I'm leaving it in for a future upgrade.
-- It's not critical to anything, but it would be nice to have.
-- ------
create table if not exists cards (
    player int unsigned not null,
    rank tinyint unsigned not null,
    suit enum('spade', 'heart', 'club', 'diamond') not null,
    location enum('nertz', 'tableau', 'stock', 'waste', 'foundation', 'discard') not null,
    pile_number tinyint unsigned,
    order_in_pile tinyint unsigned,

    check(rank >= 1 and rank <= 13),
    foreign key (player) references player (player_id),
    primary key (player, rank, suit)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;