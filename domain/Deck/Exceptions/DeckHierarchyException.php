<?php

declare(strict_types=1);

namespace Domain\Deck\Exceptions;

use Exception;

class DeckHierarchyException extends Exception
{
    public static function cannotBeOwnParent(): self
    {
        return new self('A deck cannot be its own parent.');
    }

    public static function parentCannotHaveParent(): self
    {
        return new self('A deck that has children cannot have a parent (single-level hierarchy only).');
    }

    public static function parentCannotHaveCards(): self
    {
        return new self('A parent deck cannot have cards. Remove all cards first or assign children to a different deck.');
    }

    public static function targetAlreadyHasParent(): self
    {
        return new self('The target parent deck already has a parent. Only single-level hierarchy is allowed.');
    }

    public static function deckWithCardsCannotBecomeParent(): self
    {
        return new self('A deck with cards cannot become a parent deck. Remove all cards first.');
    }

    public static function collectionCannotHaveParent(): self
    {
        return new self('A collection cannot have a parent. Collections are top-level containers only.');
    }

    public static function collectionCannotHaveCards(): self
    {
        return new self('A collection cannot have cards. Collections are containers for other decks only.');
    }

    public static function parentMustBeCollection(): self
    {
        return new self('The selected parent must be a collection.');
    }

    public static function cannotRemoveCollectionWithChildren(): self
    {
        return new self('Cannot change deck type from collection while it has child decks. Remove or reassign children first.');
    }
}
