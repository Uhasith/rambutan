<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Passbook extends Model
{
    protected $fillable = [
        'customerName',
        'address',
        'address_line_1',
        'address_line_2',
        'city',
        'bookDate',
        'account_number',
        'startDate',
        'endDate',
        'bankName',
        'forwardBalance',
        'monthlySalary',
        'salaryDate',
    ];
}
