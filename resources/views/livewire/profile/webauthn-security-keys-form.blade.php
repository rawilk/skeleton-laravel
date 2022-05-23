<div>
    <div class="text-base text-slate-600 font-medium mb-3">{{ __('Security Keys') }}</div>

    <ul>
        @forelse ($securityKeys as $securityKey)
            <li wire:key="webauthnSecurityKey{{ $securityKey->getKey() }}">
                {{ $securityKey->getKey() }}
            </li>
        @empty
            <li wire:key="webauthnSecurityKeysNoResults">
                <div class="text-sm text-slate-600">
                    {{ __('You have not registered any security keys to your account.') }}
                </div>
            </li>
        @endforelse
    </ul>

    <div class="mt-3">
        <x-button variant="blue" wire:click="showAddKey" wire:target="showAddKey">
            {{ __('Add security key') }}
        </x-button>
    </div>

    <div x-data="{
        showInstructions: @entangle('showInstructions').defer,
        showName: false,
        keyName: @entangle('newKeyName').defer,
        errorMessage: null,
        notifyCallback() {
            return errorName => this.errorMessage = this.errorMessages[errorName];
        },
        webAuthn: new WebAuthn,
        errorMessages: {
            NotAllowedError: {{ \Illuminate\Support\Js::from(trans('webauthn::alerts.key_not_allowed_error')) }},
            InvalidStateError: {{ \Illuminate\Support\Js::from(trans('webauthn::alerts.key_already_used')) }},
            notSupported: {{ \Illuminate\Support\Js::from(trans('webauthn::alerts.browser_not_supported')) }},
            notSecured: {{ \Illuminate\Support\Js::from(trans('webauthn::alerts.browser_not_secure')) }},
        },
        publicKey: {!! \Illuminate\Support\Js::from(json_decode(json_encode($publicKey))) !!},
        keyData: null,
        init() {
            this.webAuthn.registerNotifyCallback(this.notifyCallback());
        },
        register() {
            this.errorMessage = null;
            this.keyData = null;
            this.showName = false;
            this.showInstructions = false;

            this.webAuthn.register(this.publicKey, (publicKey, deviceName) => {
                this.keyName = deviceName;
                this.keyData = publicKey;
                this.showName = true;
                setTimeout(() => this.$refs.name.focus(), 250);
            });
        },
        sendKey() {
            if (! this.keyData) {
                return;
            }

            @this.registerKey(this.keyData);
        },
    }">
        <x-laravel-base::modal.dialog-modal wire:model.defer="showAddSecurityKey" max-width="lg" :show-icon="false">
            <x-slot name="title">{{ __('Register Security Key') }}</x-slot>

            <x-slot name="content">
                <div x-show="showInstructions">
                    <p>
                        {{ __('To register a security key insert it into a USB port and press Next. When it starts flashing, press the gold disc on it.') }}
                    </p>

                    <div class="mt-4">
                        <img src="{{ asset('images/webauthn-key-example.png') }}"
                             class="max-w-full"
                        >
                    </div>
                </div>

                <div x-show="! showInstructions">
                    {{-- we are waiting for user to touch their security key --}}
                    <div x-show="! showName && ! errorMessage">
                        <p class="text-center text-2xl mb-4 mt-8">{{ __('Interact with your authenticator') }}</p>

                        <div class="mx-auto flex justify-center w-auto">
                            <svg class="animate-spin h-10 w-10 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    {{-- an error has occurred (user probably canceled) --}}
                    <div x-show="errorMessage">
                        <div class="flex justify-center mt-4 mb-2">
                            <x-css-info class="h-10 w-10 text-red-500" />
                        </div>

                        <p class="text-base text-center" x-html="errorMessage"></p>

                        <div class="mt-6 text-center">
                            <x-button
                                variant="blue"
                                x-on:click="register"
                            >
                                {{ __('Retry') }}
                            </x-button>
                        </div>
                    </div>

                    {{-- user needs to name their new key --}}
                    <div x-show="showName" class="pt-4">
                        <x-form-group label="{{ __('Name Your Key') }}" name="newKeyName" input-id="newKeyNameSecurityKey">
                            <x-input
                                x-model="keyName"
                                name="newKeyName"
                                id="newKeyNameSecurityKey"
                                x-ref="name"
                            />

                            <x-slot name="helpText">{{ __('This will help you identify your keys more easily in the future.') }}</x-slot>
                        </x-form-group>
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-button
                    x-show="showInstructions"
                    x-on:click="register"
                    variant="blue"
                >
                    {{ __('Next') }}
                </x-button>

                <x-button
                    x-show="! showInstructions && showName"
                    x-bind:disabled="! keyName"
                    x-on:click="sendKey"
                    wire:target="registerKey"
                    variant="blue"
                    class="-mr-4"
                >
                    {{ __('Register Key') }}
                </x-button>

                <x-button
                    x-show="! showInstructions && showName"
                    variant="white"
                    wire:click="$set('showAddSecurityKey', false)"
                >
                    {{ __('laravel-base::messages.confirm_modal_cancel') }}
                </x-button>
            </x-slot>
        </x-laravel-base::modal.dialog-modal>
    </div>
</div>
