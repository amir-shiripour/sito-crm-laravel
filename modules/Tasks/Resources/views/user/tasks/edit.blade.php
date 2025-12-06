@extends('layouts.user')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">
    <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">
        ویرایش وظیفه
    </h1>

    <form method="POST" action="{{ route('user.tasks.update', $task) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium mb-1">عنوان</label>
            <input type="text" name="title" class="w-full rounded-xl border-gray-200 px-3 py-2 text-sm"
                   value="{{ old('title', $task->title) }}" required>
            @error('title')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">توضیحات</label>
            <textarea name="description" rows="3"
                      class="w-full rounded-xl border-gray-200 px-3 py-2 text-sm">{{ old('description', $task->description) }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">نوع وظیفه</label>
                <select name="task_type" class="w-full rounded-xl border-gray-200 px-3 py-2 text-sm">
                    @foreach($types as $t)
                        <option value="{{ $t }}" @selected(old('task_type', $task->task_type) === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">وضعیت</label>
                <select name="status" class="w-full rounded-xl border-gray-200 px-3 py-2 text-sm">
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" @selected(old('status', $task->status) === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">اولویت</label>
                <select name="priority" class="w-full rounded-xl border-gray-200 px-3 py-2 text-sm">
                    @foreach($priorities as $p)
                        <option value="{{ $p }}" @selected(old('priority', $task->priority) === $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">موعد انجام</label>
                <input type="datetime-local" name="due_at"
                       class="w-full rounded-xl border-gray-200 px-3 py-2 text-sm"
                       value="{{ old('due_at', optional($task->due_at)->format('Y-m-d\TH:i')) }}">
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-4">
            <a href="{{ route('user.tasks.index') }}" class="px-4 py-2 text-sm rounded-xl border border-gray-300">
                انصراف
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm rounded-xl bg-emerald-600 text-white hover:bg-emerald-700">
                ذخیره تغییرات
            </button>
        </div>
    </form>
</div>
@endsection
