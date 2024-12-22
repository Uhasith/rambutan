<?php

namespace App\Services;

use DateTime;
use ICal\ICal;
use DatePeriod;
use DateInterval;
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Support\Facades\Log;

class GenerateRambutanService
{
    protected $fpdf;

    public function __construct()
    {
        $this->fpdf = new Fpdf;
    }

    public function start_here()
    {
        $customerName = 'MR G A KANTHA';
        $address = 'NO. 33/2/B,';
        $address_line_1 = 'EKSATH SUBASADAKA ROAD,';
        $address_line_2 = 'PAMUNUWA,';
        $city = 'MAHARAGAMA';
        $bookDate = '19-08-2023';
        $account_number = '8023485507';

        $startDate = '2023-01-01';
        $endDate = '2023-12-31';

        $forwardBalance = 85766.70;
        $salaryDate = 10;
        $salaryAmount = 50000;

        Log::info('Start Here');

        $GLOBALS['bankHolidays'] = $this->getBankHolidaysFromICS($startDate, $endDate);
        $businessDays = $this->getWorkingDaysFromICS($startDate, $endDate);

        $salaryDates = $this->getSalaryDepositDates($startDate, $endDate, $salaryDate, $businessDays);

        $transactionDates = $this->generateTransactionDates($businessDays);
        $lastDays = $this->getLastBusinessDaysOfMonths($businessDays);
        $transactionDates = array_unique(array_merge($transactionDates, $lastDays));
        sort($transactionDates);

        $transactions = $this->generateTransactions(
            $transactionDates,
            $salaryDates,
            $forwardBalance,
            $salaryAmount
        );

        Log::info("Generated Transactions - Passbook Format:\n" . $this->formatTransactionsAsPassbook($transactions));

        return $transactions;
    }

    private function generateTransactions($transactionDates, $salaryDates, $forwardBalance, $salaryAmount)
    {
        $currentBalance = $forwardBalance;
        $transactions = [];

        $transactionRefs = [
            'CWD' => '6',
            'ATM' => '1007',
            'INT' => '0990',
            'WHT' => '0990',
            'CSH' => '6435',
            'SAL' => '5000',
        ];

        // Initial forward balance
        $transactions[] = [
            'date' => $transactionDates[0],
            'depositType' => 'BF',
            'depositAmount' => $forwardBalance,
            'withdrawalAmount' => null,
            'balance' => $forwardBalance,
        ];

        foreach ($transactionDates as $transactionDate) {
            if (in_array($transactionDate, $salaryDates)) {
                // Process salary deposit
                $transactions[] = $this->generateSalaryTransaction($transactionDate, $salaryAmount, $currentBalance, $transactionRefs);
                $currentBalance += $salaryAmount;
                continue;
            }

            if (rand(0, 1)) {
                // Withdrawal
                $amount = rand(500, 5000);
                if ($currentBalance - $amount < 0) {
                    continue;
                }

                $currentBalance -= $amount;
                $transactions[] = [
                    'date' => $transactionDate,
                    'depositType' => 'CWD',
                    'depositAmount' => null,
                    'withdrawalAmount' => $amount,
                    'balance' => $currentBalance,
                ];
            } else {
                // Deposit
                $amount = rand(500, 10000);
                $currentBalance += $amount;
                $transactions[] = [
                    'date' => $transactionDate,
                    'depositType' => 'CSH',
                    'depositAmount' => $amount,
                    'withdrawalAmount' => null,
                    'balance' => $currentBalance,
                ];
            }
        }

        // Interest and tax at the end of each month
        foreach ($this->getLastBusinessDaysOfMonths($transactionDates) as $lastDay) {
            $interest = round($currentBalance * 0.0015, 2);
            $currentBalance += $interest;

            $transactions[] = [
                'date' => $lastDay,
                'depositType' => 'INT',
                'depositAmount' => $interest,
                'withdrawalAmount' => null,
                'balance' => $currentBalance,
            ];

            $tax = round($interest * 0.05, 2);
            $currentBalance -= $tax;

            $transactions[] = [
                'date' => $lastDay,
                'depositType' => 'WHT',
                'depositAmount' => null,
                'withdrawalAmount' => $tax,
                'balance' => $currentBalance,
            ];
        }

        return $transactions;
    }

    private function generateSalaryTransaction($date, $amount, $balance, $refs)
    {
        $balance += $amount;

        return [
            'date' => $date,
            'depositType' => 'SAL',
            'depositAmount' => $amount,
            'withdrawalAmount' => null,
            'balance' => $balance,
            'reference' => $refs['SAL'],
        ];
    }

    private function getSalaryDepositDates($startDate, $endDate, $salaryDate, $businessDays)
    {
        $salaryDates = [];
        $months = array_unique(array_map(function ($date) {
            return date('Y-m', strtotime($date));
        }, $businessDays));

        foreach ($months as $month) {
            $expectedDate = date('Y-m-' . str_pad($salaryDate, 2, '0', STR_PAD_LEFT), strtotime($month . '-01'));
            $closestDate = $this->getClosestPriorBusinessDay($expectedDate, $businessDays);
            $salaryDates[] = $closestDate;
        }

        return $salaryDates;
    }

    private function getClosestPriorBusinessDay($date, $businessDays)
    {
        while (!in_array($date, $businessDays)) {
            $date = date('Y-m-d', strtotime('-1 day', strtotime($date)));
        }
        return $date;
    }

    private function generateTransactionDates($businessDays)
    {
        $selectedDates = [];
        foreach ($businessDays as $day) {
            if (rand(0, 1)) {
                $selectedDates[] = $day;
            }
        }
        return $selectedDates;
    }

    private function getBankHolidaysFromICS($startDate, $endDate)
    {
        $startYear = date('Y', strtotime($startDate));
        $endYear = date('Y', strtotime($endDate));
        $holidays = [];

        for ($year = $startYear; $year <= $endYear; $year++) {
            $events = $this->parseICS($year);
            foreach ($events as $event) {
                if (str_contains($event->description, 'Bank Holiday')) {
                    $holidays[] = $event->dtstart;
                }
            }
        }

        return $holidays;
    }

    private function getWorkingDaysFromICS($startDate, $endDate)
    {
        $bankHolidays = $GLOBALS['bankHolidays'];
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end->modify('+1 day'));

        $workingDays = [];
        foreach ($period as $date) {
            if ($date->format('N') < 6 && !in_array($date->format('Y-m-d'), $bankHolidays)) {
                $workingDays[] = $date->format('Y-m-d');
            }
        }
        return $workingDays;
    }

    private function getLastBusinessDaysOfMonths($businessDays)
    {
        $lastDays = [];
        $currentMonth = '';
        $lastDate = null;

        foreach ($businessDays as $day) {
            $month = date('Y-m', strtotime($day));
            if ($month !== $currentMonth) {
                if ($currentMonth && $lastDate) {
                    $lastDays[] = $lastDate;
                }
                $currentMonth = $month;
            }
            $lastDate = $day;
        }

        if (!empty($lastDate)) {
            $lastDays[] = $lastDate;
        }

        return $lastDays;
    }

    private function parseICS($year)
    {
        $icsFiles = [
            '2022' => public_path('assets/ics/2022.ics'),
            '2023' => public_path('assets/ics/2023.ics'),
            '2024' => public_path('assets/ics/2024.ics'),
        ];

        if (isset($icsFiles[$year])) {
            $ical = new ICal($icsFiles[$year]);
            return $ical->eventsFromRange("$year-01-01", "$year-12-31");
        }

        return [];
    }

    private function formatTransactionsAsPassbook($transactions)
    {
        $output = "-------------------------------------------------------------\n";
        $output .= "| Date       | Type | Deposit      | Withdrawal   | Balance      |\n";
        $output .= "-------------------------------------------------------------\n";

        foreach ($transactions as $transaction) {
            $output .= sprintf(
                "| %-10s | %-4s | %-12s | %-12s | %-12s |\n",
                $transaction['date'],
                $transaction['depositType'],
                $transaction['depositAmount'] !== null ? number_format($transaction['depositAmount'], 2) : ' ',
                $transaction['withdrawalAmount'] !== null ? number_format($transaction['withdrawalAmount'], 2) : ' ',
                number_format($transaction['balance'], 2)
            );
        }

        $output .= "-------------------------------------------------------------\n";

        return $output;
    }
}
