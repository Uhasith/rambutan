<?php

use Livewire\Volt\Component;
use App\Models\Passbook;
use Carbon\Carbon;
use App\Services\GenerateRambutanService;
use Codedge\Fpdf\Fpdf\Fpdf;

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
    public $transactions_count = 97;
    public $passbookModal = false;

    public function rules()
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:255'],
            'book_date' => ['required', 'date', 'max:255'],
            'start_date' => ['required', 'date', 'max:255'],
            'end_date' => ['required', 'date', 'max:255'],
            'forward_balance' => ['required', 'numeric'],
            'salary' => ['nullable', 'numeric', 'required_with:salary_date'],
            'salary_date' => ['nullable', 'numeric', 'min:1', 'max:31', 'required_with:salary'],
            'transactions_count' => ['required', 'numeric'],
        ];
    }

    public function resetForm()
    {
        $this->reset();
        $this->resetValidation();
    }

    public function create_passbook(): void
    {
        $this->validate();

        $bookDate = Carbon::parse($this->book_date)->format('Y-m-d');
        $startDate = Carbon::parse($this->start_date)->format('Y-m-d');
        $endDate = Carbon::parse($this->end_date)->format('Y-m-d');
        $salary = !empty($this->salary) && $this->salary != 0 ? (float) $this->salary : null;
        $salaryDate = !empty($this->salary_date) && $this->salary_date != 0 ? (int) $this->salary_date : null;

        $passbook = Passbook::create([
            'customer_name' => $this->customer_name,
            'address' => $this->address,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'bank_name' => $this->bank_name,
            'account_number' => $this->account_number,
            'book_date' => $bookDate,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'forward_balance' => $this->forward_balance,
            'salary' => $salary,
            'salary_date' => $salaryDate,
            'transactions_count' => $this->transactions_count,
        ]);

        $rambutanService = app(GenerateRambutanService::class);

        $transactions = $rambutanService->getRecords($startDate, $endDate, $this->forward_balance, $this->transactions_count, $salaryDate, $salary);

        $passbook->transactions_meta_data = $transactions ?? [];

        $passbook->save();

        $customer_data = array(
            'name' => $this->customer_name,
            'address' => $this->address,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'account_number' => $this->account_number,
        );

        if($this->bank_name == 'Commercial'){
            $this->generate_combnk($transactions, $customer_data);
        }elseif ($this->bank_name == 'BOC') {
            $this->generate_boc($transactions, $customer_data);
        }elseif ($this->bank_name == 'Seylan') {
            $this->generate_seylan($transactions, $customer_data);
        }elseif ($this->bank_name == 'Sampath') {
            $this->generate_sampath($transactions, $customer_data);
        }elseif ($this->bank_name == 'NSB') {
            $this->generate_nsb($transactions, $customer_data);
        }else{
            
        }

        $this->passbookModal = false;
        $this->reset();
        $this->dispatch('pg:eventRefresh-PassbookTable');
    }

    function generate_combnk($transactions, $customer_data) {
        $pdf = PDF::loadView('pdf.com', ['transactions' => $transactions, 'customer' => $customer_data]);
        $pdf->save($customer_data['account_number'] . '.pdf');
        return response()->streamDownload(
            fn() => print($pdf->output()), 
            $customer_data['account_number'] . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    function generate_seylan($transactions, $customer_data) {
        $pdf = PDF::loadView('pdf.seylan', ['transactions' => $transactions, 'customer' => $customer_data]);
        $pdf->save($customer_data['account_number'] . '.pdf');
        return response()->streamDownload(
            fn() => print($pdf->output()), 
            $customer_data['account_number'] . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    function generate_boc($transactions, $customer_data) {
        $pdf = PDF::loadView('pdf.boc', ['transactions' => $transactions, 'customer' => $customer_data]);
        $pdf->save($customer_data['account_number'] . '.pdf');
        
        return response()->streamDownload(
            fn() => print($pdf->output()), 
            $customer_data['account_number'] . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
    
    function generate_sampath($transactions, $customer_data) {
        $pdf = PDF::loadView('pdf.sampath', ['transactions' => $transactions, 'customer' => $customer_data]);
        $pdf->save($customer_data['account_number'] . '.pdf');
        return response()->streamDownload(
            fn() => print($pdf->output()), 
            $customer_data['account_number'] . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    function generate_nsb($transactions, $customer_data) {
        $pdf = PDF::loadView('pdf.nsb', ['transactions' => $transactions, 'customer' => $customer_data]);
        $pdf->save($customer_data['account_number'] . '.pdf');
        return response()->streamDownload(
            fn() => print($pdf->output()), 
            $customer_data['account_number'] . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    
}; ?>

<div>
    <x-wui-modal name="passbookCreateModal" wire:model="passbookModal" x-on:close='$wire.resetForm'>
        <x-wui-card title="New PassBook Create">
            <form wire:submit="create_passbook">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-wui-input label="Customer Name" wire:model="customer_name" placeholder="Enter customer name" />

                    <x-wui-input label="Address" wire:model="address" placeholder="Enter address" />

                    <x-wui-input label="Address Line 1" wire:model="address_line_1"
                        placeholder="Enter address line 1" />

                    <x-wui-input label="Address Line 2" wire:model="address_line_2"
                        placeholder="Enter address line 2" />

                    <x-wui-input label="City" wire:model="city" placeholder="Enter city" />

                    <x-wui-select label="Select Bank" wire:model="bank_name" placeholder="Select one bank name"
                        :options="['Commercial', 'BOC', 'Seylan', 'Sampath', 'NSB']" />

                    <x-wui-datetime-picker without-time label="Book Date" placeholder="Book Date"
                        parse-format="DD-MM-YYYY HH:mm" wire:model="book_date" />
                    <x-wui-input label="Account Number" wire:model="account_number"
                        placeholder="Enter account number" />

                    <x-wui-datetime-picker without-time label="Start Date" placeholder="Start Date"
                        parse-format="DD-MM-YYYY" wire:model="start_date" />

                    <x-wui-datetime-picker without-time label="End Date" placeholder="End Date"
                        parse-format="DD-MM-YYYY" wire:model="end_date" />

                    <x-wui-number label="Forward Balance" wire:model="forward_balance"
                        placeholder="Enter forward balance" />

                    <x-wui-number label="Salary" wire:model="salary" placeholder="Enter salary" />

                    <x-wui-number label="Salary Date" wire:model="salary_date" type="number"
                        placeholder="Enter salary date" />

                    <x-wui-number label="Transactions Count" wire:model="transactions_count" type="number"
                        placeholder="Enter transactions count" />

                </div>
                <x-slot name="footer" class="flex justify-end gap-x-4">
                    <x-wui-button flat label="Cancel" x-on:click="close" />

                    <x-wui-button primary label="Save Deatails" type="submit" />
                </x-slot>
        </x-wui-card>
        </form>
    </x-wui-modal>
</div>