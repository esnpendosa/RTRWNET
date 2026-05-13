<?php

// Test script for Bot Response logic
// Run with: php artisan tinker < app/Services/TestBotLogic.php

use App\Models\BotResponse;

$message = "Harga website";
$effectiveMessage = strtolower($message);
$message = strtolower($message);

// Slang normalization
$slang = [
    'piro' => 'harga', 'pira' => 'harga', 'rego' => 'harga', 'biaya' => 'harga',
    'nandi' => 'bayar', 'nendhi' => 'bayar', 'piye' => 'cara', 'pripun' => 'cara',
    'mboten' => 'tidak', 'ora' => 'tidak', 'ra' => 'tidak', 'gak' => 'tidak',
    'lemot' => 'gangguan', 'mati' => 'gangguan', 'rusak' => 'gangguan', 'trobel' => 'gangguan',
    'tag' => 'tagihan', 'bill' => 'tagihan', 'cek' => 'cek',
    'suwun' => 'terima', 'nuwun' => 'terima', 'thanks' => 'terima'
];
foreach ($slang as $s => $n) {
    if (str_contains($message, $s)) {
        $effectiveMessage .= " " . $n;
    }
}

$botResponses = BotResponse::where('is_active', true)->get();
$bestMatch = null;
$highestScore = 0;
$msgWords = array_filter(explode(' ', $effectiveMessage));

echo "Message: $message\n";
echo "Effective: $effectiveMessage\n\n";

foreach ($botResponses as $bot) {
    $keywords = array_map('trim', explode(',', strtolower($bot->keyword)));
    $currentScore = 0;
    
    // 1. EXACT MATCH CHECK
    if ($bot->is_exact_match) {
        if (in_array($message, $keywords)) {
            echo "ID {$bot->id}: Exact Match Found!\n";
            exit;
        }
    } else if ($bot->keyword !== 'default') {
        // 2. KEYWORD MATCH SCORE
        foreach ($keywords as $kw) {
            if (empty($kw)) continue;
            if (preg_match("/\b" . preg_quote($kw, '/') . "\b/i", $effectiveMessage)) {
                $currentScore += (strlen($kw) * 10);
                echo "ID {$bot->id}: Matched KW '$kw' (+".(strlen($kw) * 10).")\n";
            }
        }

        // 3. CONTEXT BONUS
        $cleanResponse = strtolower(preg_replace('/[^a-z0-9 ]/', '', $bot->response));
        foreach ($msgWords as $word) {
            if (strlen($word) > 3 && str_contains($cleanResponse, $word)) {
                $currentScore += 5;
                echo "ID {$bot->id}: Matched Context '$word' (+5)\n";
            }
        }
    }

    echo "ID {$bot->id} Total Score: $currentScore\n---\n";

    if ($currentScore > $highestScore) {
        $highestScore = $currentScore;
        $bestMatch = $bot;
    }
}

if ($bestMatch) {
    echo "\nBEST MATCH: ID {$bestMatch->id}\n";
    echo "Response Snapshot: " . substr($bestMatch->response, 0, 50) . "...\n";
} else {
    echo "\nNO MATCH FOUND.\n";
}
