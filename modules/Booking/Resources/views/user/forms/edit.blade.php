@extends('layouts.user')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">ویرایش فرم</h1>
            <a class="text-blue-600 hover:underline" href="{{ route('user.booking.forms.index') }}">بازگشت</a>
        </div>

        <form method="POST" action="{{ route('user.booking.forms.update', $form) }}" class="bg-white rounded border p-4 space-y-4">
            @csrf
            @method('PUT')
            @include('booking::user.forms._form', ['form' => $form])
        </form>
    </div>
@endsection
