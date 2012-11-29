<?php
require_once('jsonRPCClient.php');
$b = new jsonRPCClient('http://bitcoinrpc:PASSWORD@127.0.0.1:8332/');

define('MIN_BET', 0.01);
define('MAX_BET', 32);
define('ADDRESS', '1dice8EMZmqKvrGE4Qc9bUFf9PX3xaYDp');

$bet = MIN_BET;
$total_fees = 0;
$count = 0;
$count_won = 0;
$round = 0;
$round_won = 0;
$round_bet = 0;
$round_won_amt = 0;

while (($bet <= MAX_BET) && ($round_won < 100))
{
        $count++;
        $round++;

        $balance_a = $b->getbalance('*', 0);
        if (!isset($starting_balance)) $starting_balance = $balance_a;
        $b->sendtoaddress(ADDRESS, (float) $bet);
        $balance_b = $b->getbalance('*', 0);

        $fee = $balance_a - $balance_b - $bet;
        $total_fees += $fee;
        $total_fees = number_format($total_fees,8,'.','')+0;

        echo 'Game #'.$count."\n";
        echo 'Balance: ' . $balance_a."       ";
        echo 'Bet: '. $bet."       ";
        echo 'Fee: '. (number_format($fee, 8, '.', '') +0) . "       ";
        echo 'Total Fees: '. $total_fees. "\n";
        echo 'Balance: ' . $balance_b . "       ".'Waiting';

        $balance_c = 0;

        while ($balance_b >= $balance_c)
        {
            sleep(4);
        $balance_c = $b->getbalance('*', 0);
        echo '.';
        }

        $balance_available = $b->getbalance();
        echo "\nAvailable: $balance_available";


        echo "\nBalance: $balance_c       ";

        $diff = $balance_c - $balance_b;

        if ($diff > $bet)
        {
                $count_won++;
                $round_won_amt += $bet * 2;
                $round_bet += $bet;
                echo "Bet this round $round_bet - current bet $bet - round won amt $round_won_amt\n";
                if ($round_won_amt > $round_bet) {
                    $round_won++;
                    $round_won_amt = 0;
                    $round_bet = 0;
                    $bet = MIN_BET;
                    echo "Round $round_won won!\n";
                } else {
                    $bet *= 2.2;
                }
                echo "Next bet: $bet\n";
                echo "Win! ($count_won out of $count)\n";

        }
        else
        {
                $round_bet += $bet;
                echo "Bet this round $round_bet - current bet $bet - round won amt $round_won_amt\n";
                $bet = MIN_BET;
                echo "Next bet: $bet\n";
                echo 'Lose!'."\n";
        }

        if ($balance_available < $bet && $balance_c >= $bet)
        {
            echo "\nWaiting";
            while ($balance_available < $bet) {
                sleep(4);
                $balance_available = $b->getbalance();
                echo '.';
            }

        }
        echo "\nAvailable: $balance_available      ";

        echo "\n";
}

echo 'Starting Balance: '.$starting_balance."\n";
echo 'Ending Balance  : '. $balance_c."\n";
echo 'Total Fees: '. $total_fees."\n";
$amt_won = $balance_c - $starting_balance;
echo 'Net Profit: '. (number_format($amt_won,8,'.','') + 0). "\n\n";
