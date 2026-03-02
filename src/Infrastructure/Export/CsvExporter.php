<?php

declare(strict_types=1);

namespace App\Infrastructure\Export;

final class CsvExporter
{
    /**
     * @param array $data       Array of associative rows
     * @param array $columns    ['db_key' => 'Column Label']
     * @return string           CSV string
     */
    public function generate(array $data, array $columns): string
    {
        $stream = fopen('php://temp', 'w+');

        // Write header
        fputcsv($stream, array_values($columns), ',', '"', '\\');

        foreach ($data as $row) {
            $formattedRow = [];

            foreach ($columns as $key => $label) {
                $value = $row[$key] ?? '';

                if (is_array($value)) {
                    $value = json_encode($value);
                }

                $formattedRow[] = $value;
            }

            fputcsv($stream, $formattedRow, ',', '"', '\\');
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv ?: '';
    }
}