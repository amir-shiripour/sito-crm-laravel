<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('مدیریت کاربران') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">

                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-900 dark:border-green-700 dark:text-green-300" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                        <a href="{{ route('admin.users.create') }}" class="px-3 py-2 bg-gray-900 text-white rounded">کاربر جدید</a>

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">
                                        نام
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        ایمیل
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        نقش
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        نقششش
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        <span class="sr-only">ویرایش</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $user->name }}
                                    </th>
                                    <td class="px-6 py-4">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $user->roles->first()?->name ?? '---' }}
                                    </td>
                                    <td>
                                        @foreach($user->getRoleNames() as $r)
                                            <span class="inline-block text-xs bg-gray-100 border rounded px-2 py-1 ml-1">{{ $r }}</span>
                                        @endforeach
                                    </td>
                                    <td class="px-6 py-4 text-left">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">ویرایش</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

