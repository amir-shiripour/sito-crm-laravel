@extends('layouts.user')

@section('content')
    <div class="max-w-2xl space-y-6">
        <h1 class="text-xl font-bold">ویرایش دسته‌بندی</h1>

        <form method="POST" action="{{ route('user.booking.categories.update', $category) }}" class="bg-white rounded border p-6">
            @csrf
            @method('PUT')
            @include('booking::user.categories._form', ['category' => $category])
        </form>
    </div>
@endsection
