<?php

use Domain\Admin\Models\Admin;
use Domain\Category\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = Admin::factory()->create();
    actingAs($this->admin, 'admin');
});

test('admin can view category management page', function () {
    Category::factory()->count(3)->create();

    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->assertOk()
        ->assertSee('Category Management');
});

test('admin can see all categories in table', function () {
    $categories = Category::factory()->count(3)->create();

    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->assertSee($categories[0]->name)
        ->assertSee($categories[1]->name)
        ->assertSee($categories[2]->name);
});

test('admin can open create category modal', function () {
    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->call('openCreateModal')
        ->assertSet('showModal', true)
        ->assertSet('editingCategoryId', null);
});

test('admin can create category', function () {
    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->call('openCreateModal')
        ->set('name', 'Red Wines')
        ->set('description', 'Categories for red wine varieties')
        ->set('is_active', true)
        ->call('createCategory')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'Red Wines',
        'description' => 'Categories for red wine varieties',
        'is_active' => true,
    ]);
});

test('admin can create category with image', function () {
    $image = UploadedFile::fake()->image('category.jpg');

    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->call('openCreateModal')
        ->set('name', 'White Wines')
        ->set('description', 'Categories for white wine varieties')
        ->set('image', $image)
        ->call('createCategory')
        ->assertHasNoErrors();

    $category = Category::where('name', 'White Wines')->first();
    expect($category)->not->toBeNull();
    expect($category->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($category->image_path);
});

test('category name is required', function () {
    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->call('openCreateModal')
        ->set('name', '')
        ->set('description', 'Test description')
        ->call('createCategory')
        ->assertHasErrors(['name' => 'required']);
});

test('category name must be at least 3 characters', function () {
    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->call('openCreateModal')
        ->set('name', 'AB')
        ->call('createCategory')
        ->assertHasErrors(['name' => 'min']);
});

test('category name must be unique', function () {
    Category::factory()->create(['name' => 'Existing Category']);

    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->call('openCreateModal')
        ->set('name', 'Existing Category')
        ->call('createCategory')
        ->assertHasErrors(['name' => 'unique']);
});

test('admin can open edit category modal', function () {
    $category = Category::factory()->create([
        'name' => 'Test Category',
        'description' => 'Test Description',
    ]);

    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->call('openEditModal', $category->id)
        ->assertSet('showModal', true)
        ->assertSet('editingCategoryId', $category->id)
        ->assertSet('name', 'Test Category')
        ->assertSet('description', 'Test Description');
});

test('admin can update category', function () {
    $category = Category::factory()->create([
        'name' => 'Original Name',
        'description' => 'Original Description',
    ]);

    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->call('openEditModal', $category->id)
        ->set('name', 'Updated Name')
        ->set('description', 'Updated Description')
        ->call('updateCategory')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Updated Name',
        'description' => 'Updated Description',
    ]);
});

test('admin can update category image', function () {
    $category = Category::factory()->create();
    $newImage = UploadedFile::fake()->image('new-category.jpg');

    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->call('openEditModal', $category->id)
        ->set('image', $newImage)
        ->call('updateCategory')
        ->assertHasNoErrors();

    $updatedCategory = Category::find($category->id);
    expect($updatedCategory->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($updatedCategory->image_path);
});

test('admin can delete category', function () {
    $category = Category::factory()->create();

    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->call('deleteCategory', $category->id);

    $this->assertDatabaseMissing('categories', [
        'id' => $category->id,
    ]);
});

test('deleting category detaches all deck relationships', function () {
    $category = Category::factory()->create();
    $deck = \Domain\Deck\Models\Deck::factory()->create();
    $category->decks()->attach($deck->id);

    expect($category->decks()->count())->toBe(1);

    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->call('deleteCategory', $category->id);

    $this->assertDatabaseMissing('category_deck', [
        'category_id' => $category->id,
    ]);
});

test('admin can toggle category active status', function () {
    $category = Category::factory()->create(['is_active' => true]);

    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->call('openEditModal', $category->id)
        ->set('is_active', false)
        ->call('updateCategory');

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'is_active' => false,
    ]);
});

test('categories display deck count', function () {
    $category = Category::factory()->create();
    $decks = \Domain\Deck\Models\Deck::factory()->count(3)->create();
    $category->decks()->attach($decks->pluck('id'));

    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->assertSee($category->name)
        ->assertSee('3 Decks');
});

test('modal closes after creating category', function () {
    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->call('openCreateModal')
        ->set('name', 'New Category')
        ->call('createCategory')
        ->assertSet('showModal', false);
});

test('modal closes after updating category', function () {
    $category = Category::factory()->create();

    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->call('openEditModal', $category->id)
        ->set('name', 'Updated Category')
        ->call('updateCategory')
        ->assertSet('showModal', false);
});

test('form resets when modal closes', function () {
    $category = Category::factory()->create([
        'name' => 'Test Category',
        'description' => 'Test Description',
    ]);

    Livewire::test(\App\Livewire\Admin\CategoryManagement::class)
        ->call('openEditModal', $category->id)
        ->call('closeModal')
        ->assertSet('name', '')
        ->assertSet('description', null)
        ->assertSet('editingCategoryId', null);
});

