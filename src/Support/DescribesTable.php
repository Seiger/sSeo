<?php namespace Seiger\sSeo\Support;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Schema;

/**
 * Trait DescribesTable
 *
 * Lightweight, cross-database table introspection for Eloquent models without requiring a working
 * Doctrine DBAL bridge. Returns a normalized schema description for the model's table.
 *
 * Normalized type values (best-effort):
 *  - int, string, json, datetime, date, time, float, bool, uuid, enum, set, blob
 *
 * Notes:
 *  - For MySQL/MariaDB, types are primarily taken from `SHOW FULL COLUMNS`.
 *  - For PostgreSQL, types are derived from `pg_catalog.format_type`.
 *  - For SQLite, types are derived from `PRAGMA table_info`.
 *  - Additionally, Eloquent `$casts` are respected: columns casted to array/json/object are treated as `json`.
 *
 * @package Seiger\sSeo\Support
 */
trait DescribesTable
{
    /**
     * Describe the model's table in a normalized, driver-agnostic form.
     *
     * @return array<int,array{name:string,type:string}>
     */
    public static function describe(): array
    {
        $instance = new static();
        $conn     = $instance->getConnection(); /** @var ConnectionInterface $conn */
        $tableRaw = $instance->getTable();
        $prefix   = method_exists($conn, 'getTablePrefix') ? (string)$conn->getTablePrefix() : '';
        $table    = $prefix . $tableRaw;
        $driver   = method_exists($conn, 'getDriverName') ? (string)$conn->getDriverName() : 'mysql';

        // Base: names from Schema
        $names = Schema::getColumnListing($tableRaw);
        $rows  = [];

        foreach ($names as $name) {
            $type = '';

            // Try generic type (may throw / may be empty depending on driver/DBAL availability)
            try {
                $t = Schema::getColumnType($tableRaw, $name);
                if (is_string($t) && $t !== '') {
                    $type = self::normalizeTypeGeneric($t);
                }
            } catch (\Throwable $e) {
                // Ignore and rely on vendor hydrators.
            }

            $rows[$name] = [
                'name' => $name,
                'type' => $type,
            ];
        }

        // Vendor-specific enrichment
        try {
            switch ($driver) {
                case 'mysql':
                case 'mariadb':
                    self::hydrateFromMySql($conn, $table, $rows);
                    break;

                case 'pgsql':
                case 'postgres':
                    self::hydrateFromPostgres($conn, $tableRaw, $rows);
                    break;

                case 'sqlite':
                case 'sqlite3':
                    self::hydrateFromSqlite($conn, $table, $rows);
                    break;

                default:
                    // Unknown driver: keep best-effort generic info
                    break;
            }
        } catch (\Throwable $e) {
            // Keep best-effort results
        }

        // Respect Eloquent casts (e.g., JSON stored as TEXT/LONGTEXT).
        // This makes schema description consistent with how the model reads/writes data.
        try {
            $casts = method_exists($instance, 'getCasts') ? $instance->getCasts() : [];
            if (is_array($casts)) {
                foreach ($casts as $col => $cast) {
                    if (!isset($rows[$col])) {
                        continue;
                    }

                    $cast = is_string($cast) ? strtolower($cast) : '';

                    // Handle Laravel style casts like: array, json, object, collection, encrypted:array, etc.
                    if ($cast === 'array' || $cast === 'json' || $cast === 'object' || $cast === 'collection'
                        || str_starts_with($cast, 'encrypted:') && str_contains($cast, 'array')
                        || str_starts_with($cast, 'encrypted:') && str_contains($cast, 'json')
                    ) {
                        $rows[$col]['type'] = 'json';
                    }

                    if ($cast === 'boolean' || $cast === 'bool') {
                        $rows[$col]['type'] = 'bool';
                    }

                    if ($cast === 'integer' || $cast === 'int') {
                        $rows[$col]['type'] = 'int';
                    }

                    if ($cast === 'float' || $cast === 'double' || $cast === 'decimal') {
                        $rows[$col]['type'] = 'float';
                    }

                    if ($cast === 'datetime' || $cast === 'immutable_datetime' || $cast === 'date') {
                        // Keep 'date' if it is explicitly date, otherwise datetime
                        $rows[$col]['type'] = ($cast === 'date') ? 'date' : 'datetime';
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        // Keep the same order as Schema::getColumnListing()
        $ordered = [];
        foreach ($names as $n) {
            if (isset($rows[$n])) {
                $ordered[$n] = $rows[$n];
            }
        }

        return $ordered;
    }

    /**
     * Return a flat list of column names for the model's table.
     *
     * @return array<int,string>
     */
    public static function columnNames(): array
    {
        $instance = new static();
        return Schema::getColumnListing($instance->getTable());
    }

    /**
     * Vendor hydrator for MySQL/MariaDB (uses `SHOW FULL COLUMNS`).
     *
     * @param ConnectionInterface                      $conn
     * @param string                                   $table Prefixed table name (not quoted).
     * @param array<string,array{name:string,type:string}> $rows
     * @return void
     */
    protected static function hydrateFromMySql(ConnectionInterface $conn, string $table, array &$rows): void
    {
        $wrapped = $conn->getQueryGrammar()->wrapTable($table);
        /** @var array<int,object> $cols */
        $cols = $conn->select("SHOW FULL COLUMNS FROM {$wrapped}");

        foreach ($cols as $c) {
            $name   = (string) ($c->Field ?? '');
            $native = (string) ($c->Type ?? '');

            if ($name === '') {
                continue;
            }

            $normType = self::normalizeTypeMySql($native);

            if (!isset($rows[$name])) {
                $rows[$name] = ['name' => $name, 'type' => $normType];
            } else {
                if ($rows[$name]['type'] === '') {
                    $rows[$name]['type'] = $normType;
                }
            }
        }
    }

    /**
     * Vendor hydrator for PostgreSQL (`pg_catalog`).
     *
     * @param ConnectionInterface                      $conn
     * @param string                                   $tableRaw Model table name (with optional schema).
     * @param array<string,array{name:string,type:string}> $rows
     * @return void
     */
    protected static function hydrateFromPostgres(ConnectionInterface $conn, string $tableRaw, array &$rows): void
    {
        $parts  = explode('.', $tableRaw, 2);
        $schema = count($parts) === 2 ? $parts[0] : 'public';
        $tbl    = count($parts) === 2 ? $parts[1] : $tableRaw;

        $sql = <<<SQL
SELECT
  a.attname AS name,
  pg_catalog.format_type(a.atttypid, a.atttypmod) AS type
FROM pg_catalog.pg_attribute a
JOIN pg_catalog.pg_class     cl ON a.attrelid = cl.oid
JOIN pg_catalog.pg_namespace ns ON cl.relnamespace = ns.oid
WHERE ns.nspname = :schema
  AND cl.relname = :table
  AND a.attnum > 0
  AND NOT a.attisdropped
ORDER BY a.attnum
SQL;

        /** @var array<int,mixed> $res */
        $res = $conn->select($sql, ['schema' => $schema, 'table' => $tbl]);

        foreach ($res as $r) {
            $name   = is_array($r) ? (string)($r['name'] ?? '') : (string)($r->name ?? '');
            $native = is_array($r) ? (string)($r['type'] ?? '') : (string)($r->type ?? '');

            if ($name === '' || $native === '') {
                continue;
            }

            $normType = self::normalizeTypePostgres($native);

            if (!isset($rows[$name])) {
                $rows[$name] = ['name' => $name, 'type' => $normType];
            } else {
                if ($rows[$name]['type'] === '') {
                    $rows[$name]['type'] = $normType;
                }
            }
        }
    }

    /**
     * Vendor hydrator for SQLite (`PRAGMA table_info`).
     *
     * @param ConnectionInterface                      $conn
     * @param string                                   $table Prefixed table name.
     * @param array<string,array{name:string,type:string}> $rows
     * @return void
     */
    protected static function hydrateFromSqlite(ConnectionInterface $conn, string $table, array &$rows): void
    {
        $safe = str_replace("'", "''", $table);
        /** @var array<int,object> $cols */
        $cols = $conn->select("PRAGMA table_info('{$safe}')");

        foreach ($cols as $c) {
            $name   = (string) ($c->name ?? '');
            $native = (string) ($c->type ?? '');

            if ($name === '') {
                continue;
            }

            $normType = self::normalizeTypeSqlite($native);

            if (!isset($rows[$name])) {
                $rows[$name] = ['name' => $name, 'type' => $normType];
            } else {
                if ($rows[$name]['type'] === '') {
                    $rows[$name]['type'] = $normType;
                }
            }
        }
    }

    /**
     * Normalize a generic (Laravel/Doctrine) type name into a compact logical type.
     *
     * @param  string $t
     * @return string
     */
    protected static function normalizeTypeGeneric(string $t): string
    {
        $t = strtolower($t);

        $map = [
            'integer'    => 'int',
            'smallint'   => 'int',
            'bigint'     => 'int',
            'tinyint'    => 'int',
            'varchar'    => 'string',
            'char'       => 'string',
            'text'       => 'string',
            'mediumtext' => 'string',
            'longtext'   => 'string',
            'boolean'    => 'bool',
            'datetime'   => 'datetime',
            'datetimetz' => 'datetime',
            'timestamp'  => 'datetime',
            'date'       => 'date',
            'time'       => 'time',
            'float'      => 'float',
            'double'     => 'float',
            'decimal'    => 'float',
            'json'       => 'json',
            'guid'       => 'uuid',
            'uuid'       => 'uuid',
            'enum'       => 'enum',
            'set'        => 'set',
        ];

        return $map[$t] ?? $t;
    }

    /**
     * Normalize a native MySQL/MariaDB type to a compact logical type.
     *
     * @param  string $native
     * @return string
     */
    protected static function normalizeTypeMySql(string $native): string
    {
        $t = strtolower($native);
        $t = preg_replace('/\s+unsigned\b/', '', $t) ?? $t;
        $t = preg_replace('/\(.+\)/', '', $t) ?? $t;
        $t = trim($t);

        $map = [
            'tinyint'    => 'int',
            'smallint'   => 'int',
            'mediumint'  => 'int',
            'int'        => 'int',
            'integer'    => 'int',
            'bigint'     => 'int',

            'varchar'    => 'string',
            'char'       => 'string',
            'text'       => 'string',
            'mediumtext' => 'string',
            'longtext'   => 'string',

            'json'       => 'json',

            'datetime'   => 'datetime',
            'timestamp'  => 'datetime',
            'date'       => 'date',
            'time'       => 'time',

            'float'      => 'float',
            'double'     => 'float',
            'decimal'    => 'float',

            'enum'       => 'enum',
            'set'        => 'set',

            'bool'       => 'bool',
            'boolean'    => 'bool',

            'blob'       => 'blob',
            'longblob'   => 'blob',
            'mediumblob' => 'blob',
            'tinyblob'   => 'blob',
            'binary'     => 'blob',
            'varbinary'  => 'blob',
        ];

        return $map[$t] ?? $t;
    }

    /**
     * Normalize a native PostgreSQL type into a compact logical type.
     *
     * @param  string $native
     * @return string
     */
    protected static function normalizeTypePostgres(string $native): string
    {
        $t = strtolower($native);
        $t = preg_replace('/\(.+\)/', '', $t) ?? $t;
        $t = trim($t);

        $map = [
            'integer'                        => 'int',
            'int4'                           => 'int',
            'int2'                           => 'int',
            'int8'                           => 'int',
            'bigint'                         => 'int',
            'smallint'                       => 'int',

            'character varying'              => 'string',
            'varchar'                        => 'string',
            'character'                      => 'string',
            'text'                           => 'string',

            'bool'                           => 'bool',
            'boolean'                        => 'bool',

            'timestamp without time zone'    => 'datetime',
            'timestamp with time zone'       => 'datetime',
            'date'                           => 'date',
            'time without time zone'         => 'time',

            'json'                           => 'json',
            'jsonb'                          => 'json',

            'numeric'                        => 'float',
            'double precision'               => 'float',
            'real'                           => 'float',

            'uuid'                           => 'uuid',
        ];

        return $map[$t] ?? $t;
    }

    /**
     * Normalize a native SQLite type into a compact logical type (based on affinity).
     *
     * @param  string $native
     * @return string
     */
    protected static function normalizeTypeSqlite(string $native): string
    {
        $t = strtolower($native);
        $t = preg_replace('/\(.+\)/', '', $t) ?? $t;
        $t = trim($t);

        if (str_contains($t, 'int')) {
            return 'int';
        }

        if (str_contains($t, 'char') || str_contains($t, 'clob') || str_contains($t, 'text')) {
            return 'string';
        }

        if (str_contains($t, 'blob')) {
            return 'blob';
        }

        if (str_contains($t, 'real') || str_contains($t, 'floa') || str_contains($t, 'doub')) {
            return 'float';
        }

        if (str_contains($t, 'num') || str_contains($t, 'dec')) {
            return 'float';
        }

        if ($t === 'date') {
            return 'date';
        }

        if (str_contains($t, 'time')) {
            return 'time';
        }

        return $t;
    }
}
