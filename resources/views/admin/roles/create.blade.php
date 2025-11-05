@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto p-6">
        <h1 class="text-xl font-bold mb-6">ایجاد نقش جدید</h1>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                <ul class="list-disc mr-5">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">نام نقش</label>
                <input name="name" value="{{ old('name') }}" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">مجوزها</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($permissions as $perm)
                        <label class="flex items-center space-x-2 space-x-reverse p-2 border rounded">
                            <input type="checkbox" name="permissions[]" value="{{ $perm }}" @checked(collect(old('permissions',[]))->contains($perm))>
                            <span class="text-sm">{{ $perm }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-2">
                <button class="px-4 py-2 bg-gray-900 text-white rounded">ایجاد</button>
                <a href="{{ route('admin.roles.index') }}" class="ml-2 text-gray-600">بازگشت</a>
            </div>
        </form>
    </div>
@endsection
