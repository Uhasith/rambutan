<?php 
require 'vendor/autoload.php'; // Load ICS parser via Composer
require('fpdf186/fpdf.php');
require('fpdf186/makefont/makefont.php');

use ICal\ICal;

class PDF extends FPDF {

    function customerData($customerName, $address, $address_line_1, $address_line_2, $city, $date, $account_number) {
        $this->SetFont('CourierPS', '', 12);
        $this->Cell(10, 5, '', 0, 0, 'L');
        $this->Cell(140, 5, $customerName, 0, 0, 'L');
        $this->Cell(60, 5, date('d-m-Y', strtotime($date)), 0, 0 , 'L');
        $this->Ln();
        $this->Cell(10, 5, '', 0, 0, 'L');
        $this->Cell(140, 5, "$address");
        $this->Cell(60, 5, $account_number, 0, 0 , 'L');
        $this->Ln();
        $this->Cell(10, 5, '', 0, 0, 'L');
        $this->Cell(140, 5, "$address_line_1");
        $this->Cell(60, 5, 'LKR', 0, 0 , 'L');

        $this->Ln();
        $this->Cell(10, 5, '', 0, 0, 'L');
        $this->Cell(40, 5, "$address_line_2");
        $this->Ln();
        $this->Cell(10, 5, '', 0, 0, 'L');
        $this->Cell(40, 5, "$city");
        $this->Ln();
        $this->Ln(250);
    }
    // Page header
    function Header() {
        // Add a title and other header details
        // $this->SetFont('CourierPS','',12);
        // $this->Cell(190,10,'Account Statement',0,1,'C');
        // $this->Ln(10);
    }

    // Page footer
    function Footer() {
        // Page number
        $this->SetY(-15);
        $this->SetFont('CourierPS','',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }

    function tableHeader() {
        $this->SetFont('CourierPS', '', 10);
        $this->Cell(10, 10, '', 0);
        $this->Cell(30, 10, 'Date', 0);
        $this->Cell(20, 10, 'Ref', 0);
        $this->Cell(20, 10, 'Type', 0);
        $this->Cell(40, 10, 'Deposit Amount', 0);
        $this->Cell(40, 10, 'Withdrawal Amount', 0);
        $this->Cell(40, 10, 'Balance', 0);
        $this->Ln();
    }

    function AddBalanceForward($depositType, $balance) {
        $this->SetFont('CourierPS', '', 10);
        $this->Cell(32, 5, '', 0);
        $this->Cell(20, 5, 'Balance B/F', 0);
        $this->Cell(90, 5, str_pad(number_format($balance, 2), 10, "*", STR_PAD_LEFT), 0, 0, 'R');
        $this->Ln();
    }

    function addPageBreakGap() {
        $this->Ln(30);
    }

    function AddBalanceCarry() {
        $this->SetFont('CourierPS', '', 11);
        $this->Cell(10, 5, '', 0);
        $this->Cell(25, 5, '', 0);
        $this->Cell(15, 5, '', 0);
        // $this->Cell(20, 5, '', 0);
        $this->Cell(92, 15, 'Balance C/F to Next Page', 0, 0, 'R');
        $this->Ln();
        $this->Ln();
        $this->Ln();
        $this->Ln();
        $this->Ln();
    }
    // Function to generate transaction rows with separate deposit and withdrawal columns
    function AddTransaction($index, $date, $ref, $depositType, $depositAmount, $withdrawalAmount, $balance) {

        if($depositType == 'CWD'):
            $transaction_type = 'Cash W/D';
        elseif($depositType ==  'CSH'):
            $transaction_type = 'Deposit';
        elseif($depositType == 'ATM'):
            $transaction_type = 'ATM WD';
        elseif($depositType == 'INT'):
            $transaction_type = 'Interest';
        elseif($depositType == 'WHT'):
            $transaction_type = 'W/H Tax';
        else:
            $transaction_type = $depositType;
        endif;

        $this->SetFont('Calibri', '', 9);
        $this->Cell(10, 5, $index, 0, 0, 'R');
        $this->Cell(20, 5, date('d-m-y', strtotime($date)), 0, 0, 'R');
        $this->Cell(12, 5, $ref, 0, 0, 'R');
        $this->Cell(20, 5, $transaction_type, 0, 0, 'R');
        $this->Cell(25, 5, $withdrawalAmount ? str_pad(number_format($withdrawalAmount, 2), 10, "*", STR_PAD_LEFT) : '', 0, 0, 'R');
        $this->Cell(25, 5, $depositAmount ? str_pad(number_format($depositAmount, 2), 10, "*", STR_PAD_LEFT) : '', 0, 0, 'R');
        $this->Cell(30, 5, str_pad(number_format($balance, 2), 10, "*", STR_PAD_LEFT), 0, 0, 'R');
        $this->Ln();
    }
}

// Get form data (in real scenario, you'd retrieve from POST request)
$customerName = 'MR G A KANTHA';
$address = 'NO. 33/2/B,';
$address_line_1 = 'EKSATH SUBASADAKA ROAD,';
$address_line_2 = 'PAMUNUWA,';
$city = 'MAHARAGAMA';
$bookDate = '19-08-2023';
$account_number = '8023485507';

$startDate = '2023-01-01';
$endDate = '2023-12-31';

$transactionCount = 97;
$forwardBalance = 85766.70;

// Helper function to check if a date is a weekend
function isWeekend($date) {
    $dayOfWeek = date('N', strtotime($date));
    return ($dayOfWeek == 6 || $dayOfWeek == 7); // Saturday = 6, Sunday = 7
}

// Helper function to get the last bank working day of the month
function getLastWorkingDay($year, $month, $bankHolidays) {
    $lastDay = date('Y-m-t', strtotime("$year-$month-01")); // Get the last day of the month
    while (isWeekend($lastDay) || isBankHoliday($lastDay, $bankHolidays)) {
        // If it's a weekend or a bank holiday, move to the previous day
        $lastDay = date('Y-m-d', strtotime('-1 day', strtotime($lastDay)));
    }
    return $lastDay;
}

$GLOBALS['bankHolidays'] = getBankHolidaysFromICS($startDate, $endDate);

$businessDays = getWorkingDayesFromICS($startDate, $endDate);

// echo '<pre>';
// print_r($businessDays);
// echo '</pre>';

// $transactionDates = pickSequentialRandomDates($businessDays, $transactionCount);
$transactionDates = getRandomDatesByMonth($businessDays);
$lastDays = getLastBusinessDaysOfMonths($businessDays);
$transactionDates = array_unique(array_merge($transactionDates, $lastDays));
usort($transactionDates, function($a, $b) {
    return strtotime($a) - strtotime($b);
});
// echo '<pre>';
// print_r($transactionDates);
// echo '</pre>';
// return;
// Create the PDF
$pdf = new PDF();
$pdf->SetLeftMargin(25);
$pdf->AddFont('CourierPS', '', 'Courier-PS-Regular.php', '.');
$pdf->AddFont('MerchantCopy', '', 'Merchant Copy.php', '.');
$pdf->AddFont('LCDDot', '', 'lcddot.php', '.');
$pdf->AddFont('Casio-fx-115ES-Plus', '', 'casio-fx-115es-plus.php', '.');
$pdf->AddFont('E-KeetEpsom', '', 'e-keet-epsom.php', '.');
$pdf->AddFont('Cousine', '', 'Cousine-Regular.php', '.');
$pdf->AddFont('Calibri', '', 'calibri-regular.php', '.');

// MakeFont('fonts/calibri-regular.ttf', 'cp1252');

$pdf->AliasNbPages();
$pdf->AddPage('P', 'A4');

// Add customer and bank details
$pdf->customerData($customerName, $address, $address_line_1, $address_line_2, $city, $bookDate, $account_number);

// Add transaction header with separate columns for deposits and withdrawals

// Generate random transactions and store in an array for sorting
$currentBalance = 0; //floatval($startBalance);
$transactions = []; // Array to store the transactions before sorting
$transactionCount = 20; // Example number of transactions
$currentMonth = date('m', strtotime($startDate)); // Track the current month

$transactionRefs = array(
    'CWD' => '6',
    'ATM' => '1007',
    'INT' => '0990',
    'WHT' => '0990',
    'CSH' => '6435'
);

$lastTransationType = '';
$lastTransactionDate = '';

// while ($transactionCount > 0) {

// foreach($transactionDates as $index => $transactionDate){
for($index = 0; $index < count($transactionDates); $index++){
    // Random transaction type and amount
    $transactionDate = $transactionDates[$index];
    $transactionType = array('CSH', 'CWD', 'ATM', 'INT', 'WHT')[rand(0, 2)];
    $amount = rand(100, 400000);

   if (in_array($transactionType, ['CWD', 'ATM']) && ($currentBalance - $amount) < 0) {
        $index--;
        continue;
    }

    if(!empty($lastTransationType) && $lastTransationType == 'WHT' && date('m', strtotime($transactionDate)) == date('m', strtotime($lastTransactionDate))){
        $lastTransactionDate = '';
        $index--;
        continue;
    }

    if(count($transactions) <= 0){
        $transactions[] = [
            'date' => $transactionDate,
            'depositType' => 'BF',
            'depositAmount' => $forwardBalance,
            'withdrawalAmount' => null,
            'balance' => $forwardBalance
        ];

        $currentBalance = $forwardBalance;
        $index--;
        continue;
    }

    // Update balance and store transactions
    if (in_array($transactionType, ['CWD', 'ATM'])) {
        if($transactionType == 'ATM'){
            $amount = rand(100, 200000);
            $amount = (ceil( $amount / 100 ) * 100) + 5 ;
        }else{
            $amount = (ceil( $amount / 50 ) * 50) ;
        }

        $lastTransactionBalance = isset($transactions[array_key_last($transactions) - 1]) ? $transactions[array_key_last($transactions) - 1]['balance'] : $currentBalance;
        $currentBalance = floatval($currentBalance) - floatval($amount); // Withdrawals decrease balance

        $transactions[] = [
            'date' => $transactionDate,
            'reference' => $transactionRefs[$transactionType],
            'depositType' => $transactionType, // No deposit for withdrawals
            'depositAmount' => null,
            'withdrawalAmount' => $amount,
            'balance' => $currentBalance
        ];
    } else {
        $amount = (ceil( $amount / 500 ) * 500);
        $lastTransactionBalance = isset($transactions[array_key_last($transactions) - 1]) ? $transactions[array_key_last($transactions) - 1]['balance'] : $currentBalance;
        $currentBalance += floatval($amount);

        $transactions[] = [
            'date' => $transactionDate,
            'reference' => $transactionRefs[$transactionType],
            'depositType' => $transactionType,
            'depositAmount' => $amount,
            'withdrawalAmount' => null,
            'balance' => $currentBalance,
        ];
    }

    // If we're on a new month, calculate interest for the previous month
    $newMonth = date('m', strtotime($transactionDate));
    $lastWorkingDay = getLastDateForMonth($transactionDates, date('Ym', strtotime($transactionDate)));

    if ($transactionDate == $lastWorkingDay) {
        // Get the last working day of the previous month
        $lastTransactionBalance = $currentBalance; //$transactions[array_key_last($transactions) - 1]['balance'];
        $interest = $lastTransactionBalance * 0.0015; // 0.0015% interest
        // Add interest transaction on the last working day
        $currentBalance = $currentBalance + floatval($interest); // Adding interest to balance
        $transactionType = 'INT';
        $transactions[] = [
            'date' => $lastWorkingDay,
            'reference' => $transactionRefs['INT'],
            'depositType' => $transactionType,
            'depositAmount' => floatval($interest),
            'withdrawalAmount' => null,
            'balance' => $currentBalance
        ];

        // WithHolding Tax
        $whtTax = $interest * 0.05;
        $currentBalance = $currentBalance - $whtTax;
        // $currentBalance_fix = $currentBalance + ($interest - $whtTax);
        $transactionType = 'WHT';
        $transactions[] = [
            'date' => $lastWorkingDay,
            'reference' => $transactionRefs['INT'],
            'depositType' => $transactionType,
            'depositAmount' => null,
            'withdrawalAmount' => floatval($whtTax) ,
            'balance' => $currentBalance, // deducting withholding interest from balance
        ];
        $lastTransactionDate = $lastWorkingDay;

        $currentMonth = $newMonth; // Update to the new month
    }

    $lastTransationType = $transactionType;
    // Decrease transaction counter
    // $transactionCount--;
}

// echo '<pre>';
// print_r($transactions);
// echo '</pre>';

// Sort transactions by date
usort($transactions, function($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
});

// Add sorted transactions to the PDF
$count = 1;
$init = true;
foreach ($transactions as $index => $transaction) {
    if($count == 34){
        $pdf->AddBalanceCarry();
        $pdf->AddBalanceForward(
            'BF', 
            $transactions[$index-1]['balance']
        );
        $count = 1;
    }

    if($count == 16){
        $pdf->addPageBreakGap();
    }

    if($transaction['depositType'] == 'BF'){
        $pdf->AddBalanceForward(
            $transaction['depositType'],
            $transaction['balance']
        );
        $init = false;
    }else{
        $pdf->AddTransaction(
            $count,
            $transaction['date'],
            $transaction['reference'],
            $transaction['depositType'], 
            $transaction['depositAmount'],
            $transaction['withdrawalAmount'], 
            $transaction['balance']
        );
        $count ++;
    }
}

// Output PDF
$pdf->Output();

function parseICS($year) {
    // Define URLs for the ICS files for each year (update with actual URLs)
    $icsFiles = [
        '2022' => 'http://localhost/banks/ics/2022.ics',
        '2023' => 'http://localhost/banks/ics/2023.ics',
        '2024' => 'http://localhost/banks/ics/2024.ics',
    ];

    // Parse the ICS file for the given year
    if (isset($icsFiles[$year])) {
        $ical = new ICal($icsFiles[$year]);
        return $ical->eventsFromRange("$year-01-01", "$year-12-31");
    }

    return [];
}

function generateRandomDate($startDate, $endDate, $holidays)
{
    $startTimestamp = strtotime($startDate);
    $endTimestamp = strtotime($endDate);

    do {
        // Generate a random timestamp between the start and end dates
        $randomTimestamp = mt_rand($startTimestamp, $endTimestamp);
        $randomDate = date('Y-m-d', $randomTimestamp);
    } while (isWeekend($randomDate) || in_array($randomDate, $holidays));

    return $randomDate;
}

// function getBusinessDays($startDate, $endDate) {
//     // Get all the bank holidays within the date range
//     $bankHolidays = getBankHolidaysFromICS($startDate, $endDate);

//     // Convert the holidays to a usable date format (Ymd)
//     $bankHolidays = array_map(function($holiday) {
//         return date('Ymd', strtotime($holiday));
//     }, $bankHolidays);

//     $businessDays = [];

//     // Iterate over each day in the date range
//     $currentDate = strtotime($startDate);
//     $endDate = strtotime($endDate);

//     while ($currentDate <= $endDate) {
//         // Get the day of the week (1 = Monday, 7 = Sunday)
//         $dayOfWeek = date('N', $currentDate);

//         // Check if it's a weekday (not Saturday or Sunday) and not a bank holiday
//         if ($dayOfWeek < 6 && !in_array(date('Ymd', $currentDate), $bankHolidays)) {
//             // Add the valid business day to the array
//             $businessDays[] = date('Y-m-d', $currentDate);
//         }

//         // Move to the next day
//         $currentDate = strtotime('+1 day', $currentDate);
//     }

//     return $businessDays;
// }

function getRandomDatesByMonth($dates) {
    $datesByMonth = [];

    // Group dates by month
    foreach ($dates as $date) {
        $month = substr($date, 0, 6); // Get the "YYYYMM" part
        $datesByMonth[$month][] = $date;
    }

    $selectedDates = [];

    // For each month, pick 5 to 8 random dates
    foreach ($datesByMonth as $month => $monthDates) {
        // Determine how many dates to select (5 to 8, depending on available dates)
        $count = min(8, max(5, count($monthDates)));

        // Use array_rand to get random keys from the array
        $randomKeys = (array) array_rand($monthDates, $count);

        // Add selected dates to the result
        foreach ($randomKeys as $key) {
            $selectedDates[] = $monthDates[$key];
        }
    }

    return $selectedDates;
}

function pickSequentialRandomDates($businessDays, $numDates) {
    $pickedDates = [];
    $datesPerMonth = [];
    $totalDays = count($businessDays);

    // If there are fewer days than requested, return all available dates
    if ($numDates >= $totalDays) {
        return $businessDays;
    }

    // Determine the size of each chunk to ensure even distribution
    $chunkSize = max(1, floor($totalDays / $numDates));

    // Start at the first date and pick one from each chunk progressively
    for ($i = 0; $i < $numDates; $i++) {
        $minIndex = $i * $chunkSize;
        // Ensure that maxIndex does not exceed the array bounds
        $maxIndex = min(($i + 1) * $chunkSize - 1, $totalDays - 1);

        echo $maxIndex . '<br>';
        // Try picking a random date within this chunk
        $randomIndex = rand($minIndex, $maxIndex);
        $pickedDate = $businessDays[$randomIndex];

        // Get the year and month of the picked date
        $monthKey = date('Y-m', strtotime($pickedDate));

        // If the current month has less than 8 dates picked, add the date
        if (!isset($datesPerMonth[$monthKey])) {
            $datesPerMonth[$monthKey] = 0;
        }

        // echo $monthKey . 
        // Ensure no more than 8 dates are picked from any given month
        if ($datesPerMonth[$monthKey] < 7) {
            $pickedDates[] = $pickedDate;
            $datesPerMonth[$monthKey]++;
        } else {
            // If the month already has 8 dates, reduce the loop counter to try again
            // $i--;
        }
    }

    // Ensure every month has at least one picked date
    $pickedDates = array_merge($pickedDates, ensureOneDatePerMonth($businessDays, $pickedDates));

    // Add the last business day of each month to $pickedDates
    $pickedDates = array_merge($pickedDates, getLastBusinessDaysOfMonths($businessDays));

    // Remove any duplicates in case the last business day was already picked
    $pickedDates = array_unique($pickedDates);

    // Sort the picked dates in order
    sort($pickedDates);

    return $pickedDates;
}

function ensureOneDatePerMonth($businessDays, $pickedDates) {
    $datesPerMonth = [];
    $missingMonths = [];
    $pickedDatesSet = array_flip($pickedDates); // Make checking for existing dates faster

    // Loop through the business days to find which months are missing
    foreach ($businessDays as $date) {
        $monthKey = date('Y-m', strtotime($date));

        // Count the number of picked dates for each month
        if (!isset($datesPerMonth[$monthKey])) {
            $datesPerMonth[$monthKey] = 0;
        }

        // Count picked dates for the month
        if (isset($pickedDatesSet[$date])) {
            $datesPerMonth[$monthKey]++;
        }
    }

    // Ensure every month has at least one date
    foreach ($datesPerMonth as $month => $count) {
        if ($count === 0) {
            // If a month has no dates, mark it as missing
            $missingMonths[] = $month;
        }
    }

    // Pick one date from each missing month
    $additionalDates = [];
    foreach ($missingMonths as $missingMonth) {
        foreach ($businessDays as $date) {
            if (strpos($date, $missingMonth) === 0) { // Check if the date belongs to the missing month
                $additionalDates[] = $date;
                break;
            }
        }
    }

    return $additionalDates;
}

function getLastDateForMonth($dates, $selectedMonth) {
    $lastDate = null;
    
    foreach ($dates as $date) {
        // Extract the year and month from the date
        $yearMonth = substr($date, 0, 6); // "YYYYMM"
        
        if ($yearMonth == $selectedMonth) {
            // Compare and set the latest date for the selected month
            if ($lastDate === null || $date > $lastDate) {
                $lastDate = $date;
            }
        }
    }
    
    return $lastDate;
}

function getLastBusinessDaysOfMonths($businessDays) {
    $lastBusinessDays = [];
    $currentMonth = '';

    foreach ($businessDays as $date) {
        $month = date('Y-m', strtotime($date)); // Get year and month (YYYY-MM format)

        // If it's a new month, add the previous date as the last business day of the last month
        if ($month !== $currentMonth) {
            if ($currentMonth !== '') {
                $lastBusinessDays[] = $lastDateOfPreviousMonth;
            }
            $currentMonth = $month;
        }

        // Update the last date seen for this month
        $lastDateOfPreviousMonth = $date;
    }

    // Add the last business day of the final month
    if (!empty($lastDateOfPreviousMonth)) {
        $lastBusinessDays[] = $lastDateOfPreviousMonth;
    }

    return $lastBusinessDays;
}

function getWorkingDayesFromICS($startDate, $endDate) {
    // Extract the years from the start and end dates
    $startYear = date('Y', strtotime($startDate));
    $endYear = date('Y', strtotime($endDate));

    $startDate = date('Ymd', strtotime($startDate));
    $endDate = date('Ymd', strtotime($endDate));

    $holidays = [];

    // If the start and end years are the same, only fetch one ICS file
    if ($startYear == $endYear) {
        $events = parseICS($startYear);

        // Filter events within the start and end date range
        foreach ($events as $event) {
            if(!empty($event->description) && !str_contains($event->description, 'Bank Holiday')){
                continue;
            }
            $eventDate = $event->dtstart;
            
            if ($eventDate >= $startDate && $eventDate <= $endDate) {
                $holidays[] = $eventDate;
            }
        }

    } else {
        // Fetch and merge holidays from both years
        $startEvents = parseICS($startYear);
        $endEvents = parseICS($endYear);

        // Filter events for the start year within its valid range
        foreach ($startEvents as $event) {
            if(!empty($event->description) && !str_contains($event->description, 'Bank Holiday')){
                continue;
            }
            $eventDate = $event->dtstart;
            if ($eventDate >= $startDate && $eventDate <= date('Ymd', strtotime("$startYear-12-31"))) {
                $holidays[] = $eventDate;
            }
        }

        // Filter events for the end year within its valid range
        foreach ($endEvents as $event) {
            if(!empty($event->description) && !str_contains($event->description, 'Bank Holiday')){
                continue;
            }
            $eventDate = $event->dtstart;
            if ($eventDate >=date('Ymd', strtotime("$endYear-01-01")) && $eventDate <= $endDate) {
                $holidays[] = $eventDate;
            }
        }
    }

    $startDate = new DateTime($startDate);
    $endDate = new DateTime($endDate);
    $interval = new DateInterval('P1D'); // 1 day interval
    $dateRange = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

    // Convert holiday dates to DateTime objects
    $holidayDates = array_map(function($holiday) {
        return DateTime::createFromFormat('Ymd', $holiday);
    }, $holidays);

    $result = [];

    foreach ($dateRange as $date) {
        // Check if the current date is not a holiday
        $isHoliday = false;
        foreach ($holidayDates as $holiday) {
            if ($date->format('Ymd') === $holiday->format('Ymd')) {
                $isHoliday = true;
                break;
            }
        }
        
        if (!$isHoliday && ($date->format('N') < 6)) {
            $result[] = $date->format('Ymd'); // Add the date if it's not a holiday
        }
    }

    return $result;
}

function getBankHolidaysFromICS($startDate, $endDate) {
    // Extract the years from the start and end dates
    $startYear = date('Y', strtotime($startDate));
    $endYear = date('Y', strtotime($endDate));

    $startDate = date('Ymd', strtotime($startDate));
    $endDate = date('Ymd', strtotime($endDate));

    $holidays = [];

    // If the start and end years are the same, only fetch one ICS file
    if ($startYear == $endYear) {
        $events = parseICS($startYear);

        // Filter events within the start and end date range
        foreach ($events as $event) {
            if(!empty($event->description) && !str_contains($event->description, 'Bank Holiday')){
                continue;
            }
            $eventDate = $event->dtstart;
         
            
            if ($eventDate >= $startDate && $eventDate <= $endDate) {
                $holidays[] = $eventDate;
            }
        }

    } else {
        // Fetch and merge holidays from both years
        $startEvents = parseICS($startYear);
        $endEvents = parseICS($endYear);

        // Filter events for the start year within its valid range
        foreach ($startEvents as $event) {
            if(!empty($event->description) && !str_contains($event->description, 'Bank Holiday')){
                continue;
            }
            $eventDate = $event->dtstart;
            // echo $eventDate . '<br>';
            if ($eventDate >= $startDate && $eventDate <= date('Ymd', strtotime("$startYear-12-31"))) {
                $holidays[] = $eventDate;
            }
        }

        // Filter events for the end year within its valid range
        foreach ($endEvents as $event) {
            if(!empty($event->description) && !str_contains($event->description, 'Bank Holiday')){
                continue;
            }
            $eventDate = $event->dtstart;
            if ($eventDate >= "$endYear-01-01" && $eventDate <= $endDate) {
                $holidays[] = $eventDate;
            }
        }
    }

    return $holidays;
}

function isBankHoliday($date) {
    $bankHolidays = $GLOBALS['bankHolidays'];
    return in_array($date, $bankHolidays);
}
?>
