<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Domain\Admin\Repositories\AdminRepository;
use Domain\Category\Actions\CreateCategoryAction;
use Domain\Category\Actions\DeleteCategoryAction;
use Domain\Category\Actions\UpdateCategoryAction;
use Domain\Category\Repositories\CategoryRepository;
use Livewire\Component;
use Livewire\WithFileUploads;

class CategoryManagement extends Component
{
    use WithFileUploads;

    public string $name = '';

    public ?string $description = null;

    public bool $is_active = true;

    public ?int $editingCategoryId = null;

    public bool $showModal = false;

    public $image = null;

    public ?string $existingImagePath = null;

    public function render(CategoryRepository $categoryRepository)
    {
        $categories = $categoryRepository->getCategoriesWithDeckCount();

        return view('livewire.admin.category-management', [
            'categories' => $categories,
        ]);
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $categoryId, CategoryRepository $categoryRepository): void
    {
        $category = $categoryRepository->findById($categoryId);
        if ($category) {
            $this->editingCategoryId = $categoryId;
            $this->name = $category->name;
            $this->description = $category->description;
            $this->is_active = $category->is_active;
            $this->existingImagePath = $category->image_path;
            $this->image = null;
            $this->showModal = true;
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function createCategory(CreateCategoryAction $createCategoryAction, AdminRepository $adminRepository): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('categories', 'public');
        }

        $admin = $adminRepository->getLoggedInAdmin();
        $createCategoryAction->execute(
            name: $this->name,
            description: $this->description,
            is_active: $this->is_active,
            created_by: $admin->id,
            image_path: $imagePath
        );

        $this->closeModal();
        session()->flash('message', 'Category created successfully.');
    }

    public function updateCategory(UpdateCategoryAction $updateCategoryAction): void
    {
        if ($this->editingCategoryId) {
            $this->validate([
                'name' => ['required', 'string', 'min:3', 'max:255', 'unique:categories,name,' . $this->editingCategoryId],
                'description' => ['nullable', 'string', 'max:1000'],
                'image' => ['nullable', 'image', 'max:5120'],
            ]);

            $imagePath = $this->existingImagePath;
            if ($this->image) {
                $imagePath = $this->image->store('categories', 'public');
            }

            $updateCategoryAction->execute(
                categoryId: $this->editingCategoryId,
                name: $this->name,
                description: $this->description,
                is_active: $this->is_active,
                image_path: $imagePath
            );

            $this->closeModal();
            session()->flash('message', 'Category updated successfully.');
        }
    }

    public function deleteCategory(int $categoryId, DeleteCategoryAction $deleteCategoryAction): void
    {
        $deleteCategoryAction->execute($categoryId);
        session()->flash('message', 'Category deleted successfully.');
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'description', 'is_active', 'editingCategoryId', 'image', 'existingImagePath']);
        $this->is_active = true;
    }
}

