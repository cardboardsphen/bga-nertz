<?php

declare(strict_types=1);

namespace Bga\Games\Nertz\Models;

use stdClass;

class Card {
    /**
     * @param int $rank The rank of this card.
     * @param Suit $suit The suit of this card.
     */
    private function __construct(public int $rank, public Suit $suit) {
    }

    public function getShortName(): string {
        return $this->rank . substr($this->suit->name, 0, 1);
    }

    /**
     * Creates a new card.
     *
     * @param int $rank The rank of this card.
     * @param Suit $suit The suit of this card.
     *
     * @return void
     */
    public static function create(int $rank, Suit $suit) {
        return new Card($rank, $suit);
    }

    /**
     * Creates a new card from a database object.
     *
     * @param stdClass $card The database object.
     *
     * @return Card|null Returns the card if the object contains the required fields; null otherwise.
     */
    public static function fromDb(stdClass $card): ?Card {
        if (!isset($card->rank, $card->suit))
            return null;

        return new Card(
            intval($card->rank),
            Suit::fromName(($card->suit)),
        );
    }
}
