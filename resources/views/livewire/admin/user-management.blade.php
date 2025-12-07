<div class="p-8">
    <h1 class="text-3xl font-bold text-white mb-6">User Management</h1>
    <div class="bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-700">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Locale</th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700">
                @foreach($users as $user)
                    <tr class="hover:bg-gray-750">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ \Carbon\Carbon::parse($user->created_at)->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ $user->locale ?? 'en' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

