{{-- panel/partials/flash.blade.php --}}
@if ($errors->any())
    <div class="mb-4 p-3 rounded-lg border border-red-200 bg-red-50 text-red-700">
        <ul class="list-disc mr-5 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if (session('success'))
    <div class="mb-4 p-3 rounded-lg border border-green-200 bg-green-50 text-green-700 text-sm">
        {{ session('success') }}
    </div>
@endif
@if (session('status'))
    <div class="mb-4 p-3 rounded-lg border border-blue-200 bg-blue-50 text-blue-700 text-sm">
        {{ session('status') }}
    </div>
@endif
