<?php

namespace App\Services\TransactionImport;

class PayeeCleaner
{
    public function cleanWestpac(string $payee): string
    {
        $payee = trim($payee);

        $patterns = [
            '/^DEBIT CARD PURCHASE\s+/i',
            '/^EFTPOS DEBIT\s+\d+\s+/i',
        ];

        foreach ($patterns as $pattern) {
            $payee = preg_replace($pattern, '', $payee);
        }

        return trim($payee);
    }
}
