<?php

namespace App\Services;

use DateTime;
use ICal\ICal;
use DatePeriod;
use DateInterval;
use Illuminate\Support\Facades\Log;

class GenerateRambutanService
{
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
        $transactionCount = 97;
        $salaryDate = 15;
        $salaryAmount = 50000;

        Log::info('Start Here');

        $GLOBALS['bankHolidays'] = $this->getBankHolidaysFromICS($startDate, $endDate);
        $businessDays = $this->getWorkingDaysFromICS($startDate, $endDate);

        $salaryDates = $this->getSalaryDepositDates($startDate, $endDate, $salaryDate, $businessDays);

        $transactions = $this->generateTransactions(
            $businessDays,
            $salaryDates,
            $forwardBalance,
            $salaryAmount,
            $transactionCount
        );

        Log::info("Generated Transactions - Passbook Format:\n" . $this->formatTransactionsAsPassbook($transactions));

        return $transactions;
    }

    private function generateTransactions($businessDays, $salaryDates, $forwardBalance, $salaryAmount, $transactionCount)
    {
        $currentBalance = $forwardBalance;
        $transactions = [];

        // Initial forward balance
        $transactions[] = [
            'date' => $businessDays[0],
            'depositType' => 'BF',
            'depositAmount' => $forwardBalance,
            'withdrawalAmount' => null,
            'balance' => $forwardBalance,
        ];

        // Add salary transactions
        foreach ($salaryDates as $salaryDate) {
            $transactions[] = [
                'date' => $salaryDate,
                'depositType' => 'SAL',
                'depositAmount' => $salaryAmount,
                'withdrawalAmount' => null,
                'balance' => $currentBalance += $salaryAmount,
            ];
        }

        // Generate additional transactions
        $remainingTransactions = $transactionCount - count($transactions);
        $additionalTransactions = $this->generateAdditionalTransactions($businessDays, $salaryDates, $currentBalance, $remainingTransactions);

        $transactions = array_merge($transactions, $additionalTransactions);

        // Sort transactions by date
        usort($transactions, function ($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        return $transactions;
    }

    private function generateAdditionalTransactions($businessDays, $salaryDates, &$currentBalance, $remainingTransactions)
    {
        $transactions = [];
        $usedDates = $salaryDates; // Prevent duplicate dates with salary

        while ($remainingTransactions > 0) {
            $transactionDate = $businessDays[array_rand($businessDays)];

            // Ensure no duplicate dates with salary
            if (in_array($transactionDate, $usedDates)) {
                continue;
            }

            $usedDates[] = $transactionDate;

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

            $remainingTransactions--;
        }

        return $transactions;
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
