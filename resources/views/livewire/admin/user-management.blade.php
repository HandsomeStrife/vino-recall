<div class="p-8">
        <h1 class="text-3xl font-bold text-burgundy-900 mb-6">User Management</h1>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge.badge :variant="$user->role->value === 'admin' ? 'primary' : 'default'">
                                    {{ ucfirst($user->role->value) }}
                                </x-badge.badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <select wire:change="updateRole({{ $user->id }}, $event.target.value)" class="rounded border-gray-300">
                                    <option value="user" {{ $user->role->value === 'user' ? 'selected' : '' }}>User</option>
                                    <option value="admin" {{ $user->role->value === 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

