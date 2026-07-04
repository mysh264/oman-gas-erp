<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

#[Signature('db:backup')]
#[Description('Create a compressed SQL backup in storage/app/backups.')]
class DatabaseBackup extends Command
{
    public function handle(): int
    {
        try {
            $backupDirectory = storage_path('app/backups');
            File::ensureDirectoryExists($backupDirectory);

            $backupPath = $backupDirectory.'/daily_backup_'.now()->format('Y-m-d').'.sql.gz';
            $connection = DB::connection();
            $pdo = $connection->getPdo();

            $tables = collect($connection->select("select tablename from pg_tables where schemaname = 'public' order by tablename"))
                ->pluck('tablename')
                ->all();

            $handle = gzopen($backupPath, 'w9');

            if ($handle === false) {
                $this->error('Unable to create backup file.');

                return self::FAILURE;
            }

            $write = static function ($handle, string $line): void {
                gzwrite($handle, $line);
            };

            $write($handle, "-- Daily backup generated at ".now()->toDateTimeString()."\n");
            $write($handle, "BEGIN;\n\n");

            foreach ($tables as $table) {
                $columns = collect($connection->select(
                    "select column_name from information_schema.columns where table_schema = 'public' and table_name = ? order by ordinal_position",
                    [$table]
                ))->pluck('column_name')->all();

                $write($handle, 'TRUNCATE TABLE "'.$table.'" RESTART IDENTITY CASCADE;' . "\n");

                $rows = $connection->table($table)->get();

                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $values = [];

                    foreach ($columns as $column) {
                        $values[] = $this->quoteValue($pdo, $rowArray[$column] ?? null);
                    }

                    $columnList = implode(', ', array_map(static fn (string $column): string => '"'.$column.'"', $columns));
                    $valueList = implode(', ', $values);

                    $write($handle, 'INSERT INTO "'.$table.'" ('.$columnList.') VALUES ('.$valueList.');' . "\n");
                }

                $write($handle, "\n");
            }

            $write($handle, "COMMIT;\n");
            gzclose($handle);

            $this->info('Backup created: '.$backupPath);

            return self::SUCCESS;
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());

            return self::FAILURE;
        }
    }

    protected function quoteValue(\PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $pdo->quote((string) $value);
    }
}