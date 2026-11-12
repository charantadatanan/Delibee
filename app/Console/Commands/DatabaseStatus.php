<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseStatus extends Command
{
    protected $signature = 'delibee:status';

    protected $description = 'Show database, migration, queue and backup health';

    public function handle(): int
    {
        $connection = DB::connection();

        $dumps = glob(storage_path('backups/delibee-*.dump')) ?: [];
        usort($dumps, fn (string $a, string $b): int => (int) filemtime($b) <=> (int) filemtime($a));

        $latest = $dumps[0] ?? null;
        $backup = 'none';

        if ($latest !== null) {
            $age = (int) floor((time() - (int) filemtime($latest)) / 3600);
            $backup = basename($latest)." ({$age}h old)";
        }

        $this->table(['Check', 'Value'], [
            ['Database', $connection->getDatabaseName()],
            ['Version', $this->scalar('select version() as version')],
            ['Size', $this->scalar('select pg_size_pretty(pg_database_size(current_database())) as size')],
            ['Tables', (int) $this->scalar("select count(*) as count from information_schema.tables where table_schema = 'public'")],
            ['Indexes', (int) $this->scalar("select count(*) as count from pg_indexes where schemaname = 'public'")],
            ['Migrations', $connection->table('migrations')->count()],
            ['Pending jobs', $connection->table('jobs')->count()],
            ['Failed jobs', $connection->table('failed_jobs')->count()],
            ['Latest backup', $backup],
        ]);

        return self::SUCCESS;
    }

    private function scalar(string $sql): string
    {
        $row = DB::connection()->selectOne($sql);

        if ($row === null) {
            return '';
        }

        $values = array_values((array) $row);

        return (string) ($values[0] ?? '');
    }
}
