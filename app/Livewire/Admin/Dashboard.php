<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Domain\Card\Repositories\CardRepository;
use Domain\Deck\Repositories\DeckRepository;
use Domain\User\Repositories\UserRepository;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(
        UserRepository $userRepository,
        DeckRepository $deckRepository,
        CardRepository $cardRepository
    ) {
        $userCount = $userRepository->getAll()->count();
        $deckCount = $deckRepository->getAll()->count();
        $cardCount = $cardRepository->getAll()->count();

        return view('livewire.admin.dashboard', [
            'userCount' => $userCount,
            'deckCount' => $deckCount,
            'cardCount' => $cardCount,
        ]);
    }
}
