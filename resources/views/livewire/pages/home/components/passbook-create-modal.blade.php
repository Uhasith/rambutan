<?php

use Livewire\Volt\Component;
use App\Models\Passbook;
use Carbon\Carbon;

new class extends Component {
    public $customer_name = '';
    public $address = '';
    public $address_line_1 = '';
    public $address_line_2 = '';
    public $city = '';
    public $book_date = '';
    public $account_number = '';
    public $start_date = '';
    public $end_date = '';
    public $bank_name = '';
    public $forward_balance = '';
    public $salary = '';
    public $salary_date = '';
    public $passbookModal;

    public function rules()
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:255'],
            'book_date' => ['required', 'date', 'max:255'],
            'start_date' => ['required', 'date', 'max:255'],
            'end_date' => ['required', 'date', 'max:255'],
            'forward_balance' => ['required', 'numeric'],
            'salary' => ['required', 'numeric'],
            'salary_date' => ['required', 'numeric', 'max:31'],
        ];
    }

    public function create_passbook(): void
    {
        $this->validate();

        Passbook::create([
            'customer_name' => $this->customer_name,
            'address' => $this->address,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'bank_name' => $this->bank_name,
            'account_number' => $this->account_number,
            'book_date' => Carbon::parse($this->book_date)->format('Y-m-d'),
            'start_date' => Carbon::parse($this->start_date)->format('Y-m-d'),
            'end_date' => Carbon::parse($this->end_date)->format('Y-m-d'),
            'forward_balance' => $this->forward_balance,
            'salary' => $this->salary,
            'salary_date' => $this->salary_date,
        ]);

        $this->reset();

        $this->passbookModal = false;

        $this->dispatch('pg:eventRefresh-PassbookTable');
    }
}; ?>

<div>
    <x-wui-modal name="passbookCreateModal" wire:model="passbookModal">
        <x-wui-card title="New PassBook Create">
            <form wire:submit="create_passbook">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-wui-input label="Customer Name" wire:model="customer_name" placeholder="enter customer name" />

                    <x-wui-input label="Address" wire:model="address" placeholder="enter address" />

                    <x-wui-input label="Address Line 1" wire:model="address_line_1"
                        placeholder="enter address line 1" />

                    <x-wui-input label="City" wire:model="city" placeholder="enter city" />

                    <x-wui-datetime-picker without-time label="Book Date" placeholder="Book Date"
                        parse-format="DD-MM-YYYY HH:mm" wire:model="book_date" />
                    <x-wui-input label="Account Number" wire:model="account_number"
                        placeholder="enter account number" />

                    <x-wui-datetime-picker without-time label="Start Date" placeholder="Start Date"
                        parse-format="DD-MM-YYYY" wire:model="start_date" />

                    <x-wui-datetime-picker without-time label="End Date" placeholder="End Date"
                        parse-format="DD-MM-YYYY" wire:model="end_date" />

                    <x-wui-input label="Address Line 2" wire:model="address_line_2"
                        placeholder="enter address line 2" />

                    <x-wui-select label="Select Bank" wire:model="bank_name" placeholder="Select one bank name"
                        :options="['Commercial', 'BOC', 'Seylan', 'Sampath', 'NSB']" />

                    <x-wui-number label="Forward Balance" wire:model="forward_balance"
                        placeholder="enter forward balance" />

                    <x-wui-number label="Salary" wire:model="salary" placeholder="enter salary" />

                    <x-wui-number label="Salary Date" wire:model="salary_date" type="number"
                        placeholder="enter salary date" />

                </div>
                <x-slot name="footer" class="flex justify-end gap-x-4">
                    <x-wui-button flat label="Cancel" x-on:click="close" />

                    <x-wui-button primary label="Save Deatails" type="submit" />
                </x-slot>
        </x-wui-card>
        </form>
    </x-wui-modal>
</div>