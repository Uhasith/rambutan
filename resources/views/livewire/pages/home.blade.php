<?php

use Livewire\Volt\Component;

new class extends Component {}; ?>

<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
    <div class="flex gap-4 justify-end mb-4">
        <x-wui-mini-button primary icon="plus"
            wire:click="$dispatch('open-create-order-modal', { params: { modalId: 'order-create-modal' } })"
            x-tooltip.placement.bottom.raw="Create Order" />
    </div>
    <livewire:tables.passbook-table />
</div>
