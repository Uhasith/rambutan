<?php
namespace App\Http\Controllers;

use App\Models\Passbook;
use Illuminate\Http\Request;
use PDF; // Assuming you are using a PDF library like Dompdf or Laravel Snappy

class PassbookController extends Controller
{
    public function viewPdf($id)
{
    $passbook = Passbook::findOrFail($id);

    // Handle transactions_meta_data regardless of type
    $transactions = is_string($passbook->transactions_meta_data)
        ? json_decode($passbook->transactions_meta_data, true)
        : $passbook->transactions_meta_data;

    $customer_data = [
        'name' => $passbook->customer_name,
        'address' => $passbook->address,
        'address_line_1' => $passbook->address_line_1,
        'address_line_2' => $passbook->address_line_2,
        'city' => $passbook->city,
        'account_number' => $passbook->account_number,
    ];
    if ($passbook->bank_name == 'Commercial') {
        $pdf = PDF::loadView('pdf.com', ['transactions' => $transactions, 'customer' => $customer_data]);
    }elseif ($passbook->bank_name == 'BOC') {
        $pdf = PDF::loadView('pdf.boc', ['transactions' => $transactions, 'customer' => $customer_data]);
    }elseif ($passbook->bank_name == 'Seylan') {
        $pdf = PDF::loadView('pdf.seylan', ['transactions' => $transactions, 'customer' => $customer_data]);
    }elseif ($passbook->bank_name == 'Sampath') {
        $pdf = PDF::loadView('pdf.sampath', ['transactions' => $transactions, 'customer' => $customer_data]);
    }elseif ($passbook->bank_name == 'NSB') {
        $pdf = PDF::loadView('pdf.nsb', ['transactions' => $transactions, 'customer' => $customer_data]);
    }else{

    }

    return $pdf->stream('passbook_'.$passbook->id.'.pdf'); // Stream PDF to the browser
}

}
