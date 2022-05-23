@extends('pages.profile.layout', [
    'title' => __('Authentication'),
])

@section('slot')
    @if (\Rawilk\LaravelBase\Features::enabled(\Rawilk\LaravelBase\Features::updatePasswords()))
        <livewire:profile.update-password-form />
    @endif

    @if (\Rawilk\LaravelBase\Features::canManageTwoFactorAuthentication() || \Rawilk\LaravelBase\Features::canManageWebauthnAuthentication())
        {{--<livewire:profile.two-factor-authentication-form />--}}

        <x-card>
            <x-slot name="header">
                <h2>{{ __('laravel-base::users.profile.two_factor_title') }}</h2>
                <p class="text-sm text-slate-500">
                    {{ __('laravel-base::users.profile.two_factor_sub_title') }}
                </p>
            </x-slot>

            <div class="divide-y divide-slate-400 space-y-4">
                {{-- security keys --}}
                @if (\Rawilk\LaravelBase\Features::canManageWebauthnAuthentication())
                    @push('head')
                        @webauthnScripts
                    @endpush

                    {{--<livewire:profile.webauthn-security-keys-form />--}}
                    <livewire:profile.webauthn-internal-keys-form />
                @endif

                {{-- otp 2fa --}}
                @if (\Rawilk\LaravelBase\Features::canManageTwoFactorAuthentication())
                    <div class="pt-4 first:pt-0">OTP</div>
                @endif

                {{-- recovery codes --}}
                <div class="pt-4">Codes</div>
            </div>
        </x-card>
    @endif
@endsection
