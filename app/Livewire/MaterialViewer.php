<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Deck\Repositories\DeckRepository;
use Domain\Material\Repositories\MaterialRepository;
use Domain\User\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MaterialViewer extends Component
{
    public string $deck_shortcode;

    public int $current_index = 0;

    public bool $standalone = false;

    public ?string $exit_url = null;

    public function mount(
        string $deck_shortcode,
        bool $standalone,
        ?string $exit_url,
        UserRepository $user_repository,
        DeckRepository $deck_repository
    ): void {
        $this->deck_shortcode = $deck_shortcode;
        $this->standalone = $standalone;
        $this->exit_url = $exit_url ?? route('dashboard');

        // Verify user has access to this deck
        $user = $user_repository->getLoggedInUser();
        $deck = $deck_repository->findByShortcode($user->id, $deck_shortcode);

        if (! $deck) {
            $this->redirect(route('library'));
        }
    }

    public function render(
        MaterialRepository $material_repository,
        DeckRepository $deck_repository,
        UserRepository $user_repository
    ) {
        $user = $user_repository->getLoggedInUser();
        $deck = $deck_repository->findByShortcode($user->id, $this->deck_shortcode);

        if (! $deck) {
            return redirect()->route('library');
        }

        $materials = $material_repository->getByDeckId($deck->id);

        return view('livewire.material-viewer', [
            'materials' => $materials,
            'deck' => $deck,
            'current_material' => $materials->get($this->current_index),
            'total_materials' => $materials->count(),
        ]);
    }

    public function next(): void
    {
        $material_repository = app(MaterialRepository::class);
        $user_repository = app(UserRepository::class);
        $deck_repository = app(DeckRepository::class);

        $user = $user_repository->getLoggedInUser();
        $deck = $deck_repository->findByShortcode($user->id, $this->deck_shortcode);

        if ($deck) {
            $materials = $material_repository->getByDeckId($deck->id);
            if ($this->current_index < $materials->count() - 1) {
                $this->current_index++;
            }
        }
    }

    public function previous(): void
    {
        if ($this->current_index > 0) {
            $this->current_index--;
        }
    }

    public function skip(): void
    {
        $this->markMaterialsViewed();
        $this->dispatch('materials-skipped');
    }

    public function complete(): void
    {
        $this->markMaterialsViewed();
        $this->dispatch('materials-completed');
    }

    private function markMaterialsViewed(): void
    {
        $user_repository = app(UserRepository::class);
        $deck_repository = app(DeckRepository::class);

        $user = $user_repository->getLoggedInUser();
        $deck = $deck_repository->findByShortcode($user->id, $this->deck_shortcode);

        if ($deck) {
            DB::table('deck_user')
                ->where('user_id', $user->id)
                ->where('deck_id', $deck->id)
                ->update(['has_viewed_materials' => true]);
        }
    }
}
