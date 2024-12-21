<?php

use Livewire\Volt\Component;
use App\Models\Passbook;

new class extends Component {
    public string $customer_name = '';
    public string $address = '';
    public string $address_line_1 = '';
    public string $address_line_2 = '';
    public string $city = '';
    public string $book_date = '';
    public string $account_number = '';
    public string $start_date = '';
    public string $end_date = '';
    public string $bank_name = '';
    public string $forward_balance = '';
    public string $salary = '';
    public string $salary_date = '';

   
    public function create_passbook(): void

    {
        $validated = $this->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:255'],
            'book_date' => ['required', 'string', 'max:255'],
            'bank_name' => ['required', 'string', 'max:255'],

            'salary_date' => ['nullable', 'string', 'max:255'],
            'salary' => ['nullable', 'string', 'max:255'],
            'forward_balance' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'string', 'max:255'],
            'end_date' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],


        ]);
        
        Passbook::create($validated);
        $this->reset();

    }
}; ?>

<div>
    <x-wui-modal name="passbookCreateModal">
        <x-wui-card title="New PassBook Create">
        <form wire:submit="create_passbook">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-wui-input
                label="Customer Name"
                wire:model="customer_name" placeholder="enter customer name" />

            <x-wui-input
                label="Address"
                wire:model="address" placeholder="enter address" />
            
            <x-wui-input
                label="Address Line 1"
                wire:model="address_line_1" placeholder="enter address line 1" />
            
            <x-wui-input
                label="City"
                wire:model="city" placeholder="enter city" />

            <x-wui-datetime-picker without-time	
                    label="Book Date"
                    placeholder="Book Date"
                    parse-format="DD-MM-YYYY HH:mm"
                    wire:model="book_date"
                />
            <x-wui-input
                label="Account Number"
                wire:model="account_number" placeholder="enter account number" />    

            <x-wui-datetime-picker without-time	
                    label="Start Date"
                    placeholder="Start Date"
                    parse-format="DD-MM-YYYY"
                    wire:model="start_date"
                />

            <x-wui-datetime-picker without-time	
                    label="End Date"
                    placeholder="End Date"
                    parse-format="DD-MM-YYYY"
                    wire:model="end_date"
                />

            <x-wui-input
                label="Address Line 2"
                wire:model="address_line_2" placeholder="enter address line 2" />
            <x-wui-select
                        label="Select Bank"
                        wire:model="bank_name"
                        placeholder="Select one bank name"
                        :options="['Commercial', 'BOC', 'Seylan', 'Sampath','NSB']"
                    />
            <x-wui-input
                label="Forward Balance"
                wire:model="forward_balance" placeholder="enter forward balance" />
            
            <x-wui-input
                label="Salary"
                wire:model="salary" placeholder="enter salary" />

            <x-wui-datetime-picker without-time
                    label="Salary Date"
                    placeholder="Salary Date"
                    parse-format="DD-MM-YYYY"
                    wire:model="salary_date"
                />
        </div>
            <x-slot name="footer" class="flex justify-end gap-x-4">
                <x-wui-button flat label="Cancel" x-on:click="close" />

            <x-wui-button primary label="Save Deatails" type="submit" />
            </x-slot>
        </x-wui-card>
        </form>
    </x-wui-modal>
</div>