<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 * CSV reader service
 * 
 */

namespace HugaShop\Extensions\ProductsImport\Services;

use SplFileObject;

final class CsvReader
{
    private SplFileObject $file;

    public function __construct(private string $path, private string $delimiter = ',')
    {
        $this->file = new SplFileObject($this->path, 'r');
        $this->file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        $this->file->setCsvControl($this->delimiter);
    }

    public function getHeader(): array
    {
        $this->file->rewind();
        return $this->file->fgetcsv();
    }

    public function seekTo(int $position): void
    {
        $this->file->fseek($position);
    }


    /**
     * Read rows
     */
    public function readRows(int $limit): array
    {
        $rows = [];
        for ($i = 0; !$this->file->eof() && $i < $limit; $i++) {
            $row = $this->file->fgetcsv();
            if ($row === false) {
                continue;
            }
            $rows[] = $row;
        }
        return $rows;
    }

    public function tell(): int
    {
        return $this->file->ftell();
    }

    public function eof(): bool
    {
        return $this->file->eof();
    }

    public function fileSize(): int
    {
        return $this->file->getSize();
    }


    /**
     * count row
     */
    public function countRows(): int
    {
        $current = $this->file->ftell();
        $this->file->rewind();
        $this->file->seek(PHP_INT_MAX);
        $rows = $this->file->key();
        $this->file->fseek($current);
        return $rows;
    }
}
