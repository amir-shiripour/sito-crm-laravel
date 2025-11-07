<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            </div>

            {{-- چرا: فیلدها بر اساس نقش انتخابی تغییر می‌کنند --}}
            <select name="role" id="role" x-data x-on:change="$dispatch('role-changed', $event.target.value)">
                @foreach($availableRoles as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                @endforeach
            </select>
            <div x-data="{role: '{{ $availableRoles[0] ?? '' }}'}"
                 x-on:role-changed.window="role = $event.detail">
                @foreach($customFieldsByRole as $roleName => $fields)
                    <div x-show="role === '{{ $roleName }}'">
                        @foreach($fields as $field)
                            <label for="{{ $field->field_name }}">{{ $field->label }}</label>
                            @if($field->field_type === 'text')
                                <input type="text" name="custom[{{ $field->field_name }}]" id="{{ $field->field_name }}">
                            @elseif($field->field_type === 'number')
                                <input type="number" name="custom[{{ $field->field_name }}]" id="{{ $field->field_name }}">
                            @elseif($field->field_type === 'date')
                                <input type="date" name="custom[{{ $field->field_name }}]" id="{{ $field->field_name }}">
                            @elseif($field->field_type === 'email')
                                <input type="email" name="custom[{{ $field->field_name }}]" id="{{ $field->field_name }}">
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-center">
                            <x-checkbox name="terms" id="terms" required />

                            <div class="ms-2">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Terms of Service').'</a>',
                                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Privacy Policy').'</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-button class="ms-4">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
