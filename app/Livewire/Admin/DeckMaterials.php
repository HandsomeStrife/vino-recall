<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Domain\Deck\Repositories\DeckRepository;
use Domain\Material\Actions\CreateMaterialAction;
use Domain\Material\Actions\DeleteMaterialAction;
use Domain\Material\Actions\UpdateMaterialAction;
use Domain\Material\Actions\UpdateMaterialSortOrderAction;
use Domain\Material\Enums\ImagePosition;
use Domain\Material\Repositories\MaterialRepository;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class DeckMaterials extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $deck_id;

    public bool $show_add_form = false;

    public bool $show_edit_modal = false;

    public ?int $editing_material_id = null;

    public ?string $title = null;

    public string $content = '';

    public $image = null;

    public ?string $existing_image_path = null;

    public string $image_position = 'top';

    public function mount(int $deck_id): void
    {
        $this->deck_id = $deck_id;
    }

    public function render(MaterialRepository $material_repository, DeckRepository $deck_repository)
    {
        $deck = $deck_repository->findById($this->deck_id);
        $materials = $material_repository->getByDeckId($this->deck_id);

        return view('livewire.admin.deck-materials', [
            'deck' => $deck,
            'materials' => $materials,
        ]);
    }

    public function toggleAddForm(): void
    {
        $this->show_add_form = ! $this->show_add_form;
        if ($this->show_add_form) {
            $this->resetForm();
        }
    }

    public function createMaterial(CreateMaterialAction $create_material_action): void
    {
        $this->validate([
            'content' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:5120'],
            'image_position' => ['required', 'in:top,left,right,bottom'],
        ]);

        $image_path = null;
        if ($this->image) {
            $image_path = $this->image->store('materials', 'public');
        }

        $create_material_action->execute(
            deck_id: $this->deck_id,
            content: $this->content,
            title: $this->title,
            image_path: $image_path,
            image_position: ImagePosition::from($this->image_position)
        );

        $this->resetForm();
        $this->show_add_form = false;
        $this->dispatch('material-saved', message: 'Material created successfully!');
    }

    public function openEditModal(int $material_id, MaterialRepository $material_repository): void
    {
        $material = $material_repository->findById($material_id);
        if ($material) {
            $this->editing_material_id = $material_id;
            $this->title = $material->title;
            $this->content = $material->content;
            $this->existing_image_path = $material->image_path;
            $this->image_position = $material->image_position->value;
            $this->show_edit_modal = true;
        }
    }

    public function closeEditModal(): void
    {
        $this->show_edit_modal = false;
        $this->resetForm();
    }

    public function updateMaterial(UpdateMaterialAction $update_material_action): void
    {
        if (! $this->editing_material_id) {
            return;
        }

        $this->validate([
            'content' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:5120'],
            'image_position' => ['required', 'in:top,left,right,bottom'],
        ]);

        $image_path = $this->existing_image_path;
        if ($this->image) {
            $image_path = $this->image->store('materials', 'public');
        }

        $update_material_action->execute(
            material_id: $this->editing_material_id,
            content: $this->content,
            title: $this->title,
            image_path: $image_path,
            image_position: ImagePosition::from($this->image_position)
        );

        $this->closeEditModal();
        $this->dispatch('material-saved', message: 'Material updated successfully!');
    }

    public function deleteMaterial(int $material_id, DeleteMaterialAction $delete_material_action): void
    {
        $delete_material_action->execute($material_id);
        $this->dispatch('material-saved', message: 'Material deleted successfully!');
    }

    public function updateSortOrder(array $ordered_ids, UpdateMaterialSortOrderAction $update_sort_order_action): void
    {
        $update_sort_order_action->execute($this->deck_id, $ordered_ids);
    }

    private function resetForm(): void
    {
        $this->editing_material_id = null;
        $this->title = null;
        $this->content = '';
        $this->image = null;
        $this->existing_image_path = null;
        $this->image_position = 'top';
        $this->resetValidation();
    }
}

