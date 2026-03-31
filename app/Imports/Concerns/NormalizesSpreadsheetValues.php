<?php

namespace App\Imports\Concerns;

use Carbon\Carbon;
use DateTimeInterface;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;
use Throwable;

trait NormalizesSpreadsheetValues
{
    protected function blank(mixed $value): bool
    {
        return $value === null || trim((string) $value) === '';
    }

    protected function stringOrNull(mixed $value, ?int $maxLength = null): ?string
    {
        if ($this->blank($value)) {
            return null;
        }

        $string = trim((string) $value);

        return $maxLength === null ? $string : mb_substr($string, 0, $maxLength);
    }

    protected function intOrNull(mixed $value): ?int
    {
        if ($this->blank($value) || ! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    protected function dateOrNull(mixed $value): ?string
    {
        if ($this->blank($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(SpreadsheetDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }
}