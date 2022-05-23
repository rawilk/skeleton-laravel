@php
    $instructions = $instructions ?? null;
    $graphic = $graphic ?? null;
@endphp

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
            NotAllowedError: {{ Js::from(trans('webauthn::alerts.key_not_allowed_error')) }},
            InvalidStateError: {{ Js::from(trans('webauthn::alerts.key_already_used')) }},
            notSupported: {{ Js::from(trans('webauthn::alerts.browser_not_supported')) }},
            notSecured: {{ Js::from(trans('webauthn::alerts.browser_not_secure')) }},
        },
        publicKey: {!! Js::from(json_decode(json_encode($publicKey))) !!},
        keyData: null,
        init() {

        },
        register() {
            this.errorMessage = null;
            this.keyData = null;
            this.showName = false;
            this.showInstructions = false;

            this.webAuthn.register(this.publicKey, (publicKeyCredential, deviceName) => {
                this.keyName = deviceName;
                this.keyData = publicKeyCredential;
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
     }"
>
    <x-dialog-modal
        wire:model.defer="{{ $model }}"
        max-width="lg"
        :show-icon="false"
    >
        <x-slot name="title">{{ $title }}</x-slot>

        <x-slot name="content">
            @if ($needsInstructions)
                <div x-show="showInstructions">
                    <p>{{ $instructions }}</p>

                    @if ($graphic)
                        <div class="mt-4">
                            <img src="{{ $graphic }}" class="max-w-full">
                        </div>
                    @endif
                </div>
            @endif


        </x-slot>
    </x-dialog-modal>
</div>
