<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Passbook extends Model
{
    protected $fillable = [
        'customer_name',
        'address',
        'address_line_1',
        'address_line_2',
        'city',
        'book_date',
        'account_number',
        'start_date',
        'end_date',
        'bank_name',
        'forward_balance',
        'salary',
        'salary_date',
        'transactions_count',
        'transactions_meta_data'
    ];

    protected $casts = [
        'transactions_meta_data' => 'array'
    ];
}
