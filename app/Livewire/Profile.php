<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Subscription\Repositories\PlanRepository;
use Domain\Subscription\Repositories\SubscriptionRepository;
use Domain\User\Actions\UpdateUserAction;
use Domain\User\Repositories\UserRepository;
use Livewire\Component;

class Profile extends Component
{
    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(
        UserRepository $userRepository,
        SubscriptionRepository $subscriptionRepository,
        PlanRepository $planRepository
    ): void {
        $user = $userRepository->getLoggedInUser();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function updateProfile(UpdateUserAction $updateUserAction, UserRepository $userRepository): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $user = $userRepository->getLoggedInUser();
        $updateUserAction->execute(
            userId: $user->id,
            name: $this->name,
            email: $this->email
        );

        session()->flash('message', 'Profile updated successfully.');
    }

    public function updatePassword(UpdateUserAction $updateUserAction, UserRepository $userRepository): void
    {
        $this->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = $userRepository->getLoggedInUser();
        $userModel = \Domain\User\Models\User::findOrFail($user->id);

        if (! \Illuminate\Support\Facades\Hash::check($this->current_password, $userModel->password)) {
            $this->addError('current_password', 'Current password is incorrect.');

            return;
        }

        $updateUserAction->execute(
            userId: $user->id,
            password: $this->password
        );

        $this->reset(['current_password', 'password', 'password_confirmation']);
        session()->flash('password_message', 'Password updated successfully.');
    }

    public function render(
        SubscriptionRepository $subscriptionRepository,
        PlanRepository $planRepository,
        UserRepository $userRepository
    ) {
        $user = $userRepository->getLoggedInUser();
        $subscription = $subscriptionRepository->findByUserId($user->id);
        $plan = null;
        if ($subscription) {
            $plan = $planRepository->findById($subscription->plan_id);
        }
        $plans = $planRepository->getAll();

        return view('livewire.profile', [
            'subscription' => $subscription,
            'plan' => $plan,
            'plans' => $plans,
        ]);
    }
}
