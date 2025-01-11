<?php

namespace App\Http\Controllers;

use App\Models\Passbook;
use Codedge\Fpdf\Facades\Fpdf;
use Illuminate\Http\Request;
use PDF; // Assuming you are using a PDF library like Dompdf or Laravel Snappy

class PassbookController extends Controller
{
    public $refs = array(
        'CWD' => '6',
        'ATM' => '1007',
        'INT' => '0990',
        'WHT' => '0990',
        'CSH' => '6435'
    );

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
            $this->com_view_pdf($transactions, $customer_data, $passbook->date);
            // $pdf = PDF::loadView('pdf.com', ['transactions' => $transactions, 'customer' => $customer_data]);
        } elseif ($passbook->bank_name == 'BOC') {
            
            // $pdf = PDF::loadView('pdf.boc', ['transactions' => $transactions, 'customer' => $customer_data]);
        } elseif ($passbook->bank_name == 'Seylan') {
            $pdf = PDF::loadView('pdf.seylan', ['transactions' => $transactions, 'customer' => $customer_data]);
        } elseif ($passbook->bank_name == 'Sampath') {
            $pdf = PDF::loadView('pdf.sampath', ['transactions' => $transactions, 'customer' => $customer_data]);
        } elseif ($passbook->bank_name == 'NSB') {
            $pdf = PDF::loadView('pdf.nsb', ['transactions' => $transactions, 'customer' => $customer_data]);
        } else {
        }

        return $pdf->stream('passbook_' . $passbook->id . '.pdf'); // Stream PDF to the browser
    }

    function com_view_pdf($transactions, $customer, $date): void
    {
        $fpdf = new \Codedge\Fpdf\Fpdf\Fpdf();
        $fpdf->AddPage();
        $fpdf->SetFont('Courier', 'B', 12);

        $fpdf->Cell(10, 5, '', 0, 0, 'L');
        $fpdf->Cell(140, 5, $customer['name'], 0, 0, 'L');
        $fpdf->Cell(60, 5, date('d-m-Y', strtotime($date)), 0, 0, 'L');
        $fpdf->Ln();
        $fpdf->Cell(10, 5, '', 0, 0, 'L');
        $fpdf->Cell(140, 5, $customer['address']);
        $fpdf->Cell(60, 5, $customer['account_number'], 0, 0, 'L');
        $fpdf->Ln();
        $fpdf->Cell(10, 5, '', 0, 0, 'L');
        $fpdf->Cell(140, 5, $customer['address_line_1']);
        $fpdf->Cell(60, 5, 'LKR', 0, 0, 'L');

        $fpdf->Ln();
        $fpdf->Cell(10, 5, '', 0, 0, 'L');
        $fpdf->Cell(40, 5, $customer['address_line_2']);
        $fpdf->Ln();
        $fpdf->Cell(10, 5, '', 0, 0, 'L');
        $fpdf->Cell(40, 5, $customer['city']);
        $fpdf->Ln();
        $fpdf->Ln(250);

        $fpdf->SetFont('Courier', '', 9);
        foreach ($transactions as $index => $transaction) {

            // $fpdf->SetFont('Calibri', '', 9);
            $fpdf->Cell(15, 5, $index + 1, 0, 0, 'R');
            $fpdf->Cell(25, 5, date('d-m-y', strtotime($date)), 0, 0, 'R');
            $fpdf->Cell(17, 5, $this->refs[$transaction['depositType']] ?? '', 0, 0, 'R');
            $fpdf->Cell(25, 5, $transaction['depositType'], 0, 0, 'R');
            $fpdf->Cell(30, 5, $transaction['withdrawalAmount'] ? str_pad(number_format($transaction['withdrawalAmount'], 2), 10, "*", STR_PAD_LEFT) : '', 0, 0, 'R');
            $fpdf->Cell(30, 5, $transaction['depositAmount'] ? str_pad(number_format($transaction['depositAmount'], 2), 10, "*", STR_PAD_LEFT) : '', 0, 0, 'R');
            $fpdf->Cell(35, 5, str_pad(number_format($transaction['balance'], 2), 10, "*", STR_PAD_LEFT), 0, 0, 'R');
            $fpdf->Ln();
        }
        $fpdf->output();
        exit;
    }
}
