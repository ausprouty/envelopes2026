<?php

namespace App\Services\TransactionImport;

use RuntimeException;

class QfxParser
{
    public function parse(string $contents): array
    {
        $contents = trim($contents);

        if ($contents === '') {
            throw new RuntimeException('The QFX file is empty.');
        }

        $transactions = [];

        preg_match_all(
            '/<STMTTRN>(.*?)<\/STMTTRN>/si',
            $contents,
            $matches
        );

        foreach ($matches[1] as $transactionBlock) {
            $transactions[] = [
                'transaction_date' => $this->parseDate(
                    $this->getTagValue($transactionBlock, 'DTPOSTED')
                ),

                'amount' => (float) $this->getTagValue(
                    $transactionBlock,
                    'TRNAMT'
                ),

                'payee' => $this->getTagValue(
                    $transactionBlock,
                    'NAME'
                ),

                'description' => $this->getTagValue(
                    $transactionBlock,
                    'MEMO'
                ),

                'external_id' => $this->getTagValue(
                    $transactionBlock,
                    'FITID'
                ),
            ];
        }

        if (empty($transactions)) {
            throw new RuntimeException(
                'No transactions were found in the QFX file.'
            );
        }

        return $transactions;
    }

    private function getTagValue(
        string $block,
        string $tag
    ): ?string {
        if (
            preg_match(
                '/<' . preg_quote($tag, '/') . '>([^<\r\n]*)/i',
                $block,
                $match
            )
        ) {
            $value = trim($match[1]);

            return $value !== '' ? $value : null;
        }

        return null;
    }

    private function parseDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $date = substr($value, 0, 8);

        if (! preg_match('/^\d{8}$/', $date)) {
            return null;
        }

        return substr($date, 0, 4)
            . '-'
            . substr($date, 4, 2)
            . '-'
            . substr($date, 6, 2);
    }
}
