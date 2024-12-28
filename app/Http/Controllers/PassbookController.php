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

    $pdf = PDF::loadView('pdf.com', ['transactions' => $transactions, 'customer' => $customer_data]);

    return $pdf->stream('passbook_'.$passbook->id.'.pdf'); // Stream PDF to the browser
}

}
