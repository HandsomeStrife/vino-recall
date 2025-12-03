<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Domain\User\Repositories\UserRepository;
use Livewire\Component;

class UserManagement extends Component
{
    public function render(UserRepository $userRepository)
    {
        $users = $userRepository->getAll();

        return view('livewire.admin.user-management', [
            'users' => $users,
        ]);
    }
}
