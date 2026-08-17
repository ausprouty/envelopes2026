<?php

namespace App\Services\TransactionImport;

use RuntimeException;

class QfxParser
{
    public function parse(
        string $contents,
        string $payeeField = 'MEMO',
        ?string $descriptionField = null
    ): array {
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
            $payee = $this->getTagValue(
                $transactionBlock,
                $payeeField
            );

            $description = $descriptionField
                ? $this->getTagValue(
                    $transactionBlock,
                    $descriptionField
                )
                : null;

            $transactions[] = [
                'amount' => $this->parseAmount(
                    $this->getTagValue(
                        $transactionBlock,
                        'TRNAMT'
                    )
                ),

                'description' => $description,

                'external_id' => $this->getTagValue(
                    $transactionBlock,
                    'FITID'
                ),

                'payee' => $payee,

                'transaction_date' => $this->parseDate(
                    $this->getTagValue(
                        $transactionBlock,
                        'DTPOSTED'
                    )
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

    public function parseBalances(string $contents): array
    {
        $ledgerBlock = $this->getBlock(
            $contents,
            'LEDGERBAL'
        );

        $availableBlock = $this->getBlock(
            $contents,
            'AVAILBAL'
        );

        $ledgerBalance = $ledgerBlock
            ? $this->parseAmount(
                $this->getTagValue($ledgerBlock, 'BALAMT')
            )
            : null;

        $availableBalance = $availableBlock
            ? $this->parseAmount(
                $this->getTagValue($availableBlock, 'BALAMT')
            )
            : null;

        $ledgerAsOf = $ledgerBlock
            ? $this->parseDateTime(
                $this->getTagValue($ledgerBlock, 'DTASOF')
            )
            : null;

        $availableAsOf = $availableBlock
            ? $this->parseDateTime(
                $this->getTagValue($availableBlock, 'DTASOF')
            )
            : null;

        return [
            'available_balance' => $availableBalance,
            'balance_as_of' => $ledgerAsOf ?? $availableAsOf,
            'ledger_balance' => $ledgerBalance,
        ];
    }

    private function getBlock(
        string $contents,
        string $tag
    ): ?string {
        if (
            preg_match(
                '/<' . preg_quote($tag, '/') . '>(.*?)<\/'
                . preg_quote($tag, '/') . '>/si',
                $contents,
                $match
            )
        ) {
            return $match[1];
        }

        return null;
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

    private function parseAmount(?string $value): ?float
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = str_replace(' ', '', trim($value));

        return is_numeric($value)
            ? (float) $value
            : null;
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

    private function parseDateTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $dateTime = substr($value, 0, 14);

        if (! preg_match('/^\d{14}$/', $dateTime)) {
            return $this->parseDate($value);
        }

        return substr($dateTime, 0, 4)
            . '-'
            . substr($dateTime, 4, 2)
            . '-'
            . substr($dateTime, 6, 2)
            . ' '
            . substr($dateTime, 8, 2)
            . ':'
            . substr($dateTime, 10, 2)
            . ':'
            . substr($dateTime, 12, 2);
    }
}
