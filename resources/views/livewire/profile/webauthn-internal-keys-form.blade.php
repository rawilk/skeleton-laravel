<div class="pt-4 first:pt-0">
    <div class="text-base text-slate-600 font-medium mb-3">{{ __('Internal (Built-In) Authenticators') }}</div>

    <ul>

    </ul>

    <div class="mt-3">
        <x-button variant="blue" wire:click="showAddKey" wire:target="showAddKey">
            {{ __('Add internal authenticator') }}
        </x-button>
    </div>

    @include('livewire.profile.partials.webauthn-key-dialog', [
        'model' => 'showAddInternalKey',
        'title' => __('Register Internal Authenticator'),
        'needsInstructions' => false,
    ])
</div>
