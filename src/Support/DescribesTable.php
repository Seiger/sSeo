<?php namespace Seiger\sSeo\Support;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\ConnectionInterface;

/**
 * Trait DescribesTable
 *
 * Lightweight, cross‑database table introspection for Eloquent models without requiring a working
 * Doctrine DBAL bridge. The trait returns a **normalized schema description** for the model's table
 * using a hybrid strategy:
 *
 *  1) Base column list from Laravel's Schema facade (`Schema::getColumnListing()`).
 *  2) Best‑effort type detection via `Schema::getColumnType()` when available.
 *  3) Vendor‑specific fallbacks to fetch accurate types, nullability and defaults:
 *       - MySQL/MariaDB  → `SHOW FULL COLUMNS FROM ...`
 *       - PostgreSQL     → `pg_catalog` system views
 *       - SQLite         → `PRAGMA table_info('...')`
 *
 * ### Returned shape
 * Each column is represented as an associative array with keys:
 * - `name`     (string) — column name
 * - `type`     (string) — **normalized** logical type: one of `int`, `string`, `json`, `datetime`,
 *                         `date`, `time`, `float`, `bool`, `uuid`, `enum`, `set`, etc. If a driver
 *                         cannot be resolved, it will be a best‑effort mapping or empty string.
 *
 * ### Supported drivers
 * - MySQL / MariaDB
 * - PostgreSQL
 * - SQLite / SQLite3
 *
 * > Note: The trait **does not** rely on a working Doctrine bridge and therefore works in
 * > environments where DBAL is installed but not exposed by the connection adapter.
 *
 * ### Usage
 * ```php
 * class sSeoModel extends \Illuminate\Database\Eloquent\Model {
 *     use \Seiger\sSeo\Support\DescribesTable;
 *     protected $table = 'evo_sseo';
 * }
 *
 * $cols = sSeoModel::describe();
 * // [
 * //   ['name'=>'id','type'=>'int'],
 * //   ['name'=>'meta_title','type'=>'string'],
 * //   ...
 * // ]
 * ```
 */
trait DescribesTable
{
    /**
     * Describe the model's table in a normalized, driver‑agnostic form.
     *
     * Strategy:
     *  - Build a base list of columns using `Schema::getColumnListing($table)`.
     *  - Attempt to fetch a generic type via `Schema::getColumnType()` (may require DBAL on some drivers).
     *  - Augment/fix data with vendor‑specific SQL to obtain native type, nullability and default.
     *
     * The resulting array preserves the column order reported by `Schema::getColumnListing()`.
     *
     * @return array<int,array{name:string,type:string}>
     *               A list of columns with normalized keys: name, type.
     */
    public static function describe(): array
    {
        $instance = new static();
        $conn     = $instance->getConnection(); /** @var ConnectionInterface $conn */
        $tableRaw = $instance->getTable();
        $prefix   = method_exists($conn, 'getTablePrefix') ? $conn->getTablePrefix() : '';
        $table    = $prefix . $tableRaw;
        $driver   = method_exists($conn, 'getDriverName') ? $conn->getDriverName() : 'mysql';

        // Base: names from Schema
        $names = Schema::getColumnListing($tableRaw);
        $rows  = [];
        foreach ($names as $name) {
            $type = '';

            // Try generic type (may be empty if unavailable on this driver)
            try {
                $t = Schema::getColumnType($tableRaw, $name);
                if (is_string($t) && $t !== '') {
                    $type = self::normalizeTypeGeneric($t);
                }
            } catch (\Throwable $e) {
                // ignore and rely on vendor hydrators
            }

            $rows[$name] = [
                'name' => $name,
                'type' => $type,
            ];
        }

        // Vendor‑specific enrichment
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
                    // Unknown driver: keep best‑effort generic info
                    break;
            }
        } catch (\Throwable $e) {
            // Fallback to whatever we already have
        }

        // Keep the same order as Schema::getColumnListing()
        return array_intersect_key($rows, array_flip($names));
    }

    /**
     * Return a flat list of column names for the model's table.
     *
     * Wrapper around `Schema::getColumnListing($table)`.
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
     * Fills/overrides:
     *  - `type`     → normalized logical type derived from native type
     *
     * @param ConnectionInterface                 $conn  Active database connection.
     * @param string                              $table Prefixed table name (not quoted).
     * @param array<string,array{name:string,type:string}> $rows
     *        Reference to the base map [name => column array] to be modified in place.
     * @return void
     */
    protected static function hydrateFromMySql(ConnectionInterface $conn, string $table, array &$rows): void
    {
        $wrapped = $conn->getQueryGrammar()->wrapTable($table);
        /** @var array<int,object> $cols */
        $cols = $conn->select("SHOW FULL COLUMNS FROM {$wrapped}");

        foreach ($cols as $c) {
            $name     = (string) $c->Field;
            $native   = (string) $c->Type;

            $normType = self::normalizeTypeMySql($native);

            if (!isset($rows[$name])) {
                $rows[$name] = ['name' => $name, 'type' => $normType];
            } else {
                if ($rows[$name]['type'] === '') $rows[$name]['type'] = $normType;
            }
        }
    }

    /**
     * Vendor hydrator for PostgreSQL (`pg_catalog`).
     *
     * Fills/overrides:
     *  - `type`     → normalized logical type derived from `format_type(...)`
     *  - `default`  → expression normalized by {@see normalizePgDefault()}
     *
     * Accepts a possibly schema‑qualified table name (e.g., "public.my_table").
     *
     * @param ConnectionInterface                 $conn
     * @param string                              $tableRaw Model table name as defined on the model (with optional schema).
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
LEFT JOIN pg_catalog.pg_attrdef ad
       ON ad.adrelid = a.attrelid AND ad.adnum = a.attnum
WHERE ns.nspname = :schema
  AND cl.relname = :table
  AND a.attnum > 0
  AND NOT a.attisdropped
ORDER BY a.attnum
SQL;

        /** @var array<int,array{name:string,type:string}> $res */
        $res = $conn->select($sql, ['schema' => $schema, 'table' => $tbl]);

        foreach ($res as $r) {
            $name     = is_array($r) ? (string)$r['name'] : (string)$r->name;
            $native   = is_array($r) ? (string)$r['type'] : (string)$r->type;

            $normType = self::normalizeTypePostgres($native);

            if (!isset($rows[$name])) {
                $rows[$name] = ['name' => $name, 'type' => $normType];
            } else {
                if ($rows[$name]['type'] === '') $rows[$name]['type'] = $normType;
            }
        }
    }

    /**
     * Vendor hydrator for SQLite (`PRAGMA table_info`).
     *
     * Fills/overrides:
     *  - `type`     → normalized logical type derived from native affinity
     *
     * @param ConnectionInterface                 $conn
     * @param string                              $table Prefixed table name (used as PRAGMA identifier).
     * @param array<string,array{name:string,type:string}> $rows
     * @return void
     */
    protected static function hydrateFromSqlite(ConnectionInterface $conn, string $table, array &$rows): void
    {
        $safe = str_replace("'", "''", $table);
        /** @var array<int,object> $cols */
        $cols = $conn->select("PRAGMA table_info('{$safe}')");

        foreach ($cols as $c) {
            $name     = (string) $c->name;
            $native   = (string) $c->type;

            $normType = self::normalizeTypeSqlite($native);

            if (!isset($rows[$name])) {
                $rows[$name] = ['name' => $name, 'type' => $normType];
            } else {
                if ($rows[$name]['type'] === '') $rows[$name]['type'] = $normType;
            }
        }
    }

    /**
     * Normalize a generic (Laravel/Doctrine) type name into a compact logical type.
     * Examples:
     *  - integer/smallint/bigint → int
     *  - string/text             → string
     *  - boolean                 → bool
     *  - datetimetz              → datetime
     *
     * @param  string $t  Generic type name (lower/upper insensitive).
     * @return string     Normalized logical type.
     */
    protected static function normalizeTypeGeneric(string $t): string
    {
        $t = strtolower($t);
        $map = [
            'integer'   => 'int',
            'smallint'  => 'int',
            'bigint'    => 'int',
            'varchar'   => 'string',
            'mediumtext'=> 'string',
            'text'      => 'string',
            'boolean'   => 'bool',
            'datetime'  => 'datetime',
            'datetimetz'=> 'datetime',
            'date'      => 'date',
            'time'      => 'time',
            'float'     => 'float',
            'decimal'   => 'float',
            'json'      => 'json',
            'guid'      => 'uuid',
        ];
        return $map[$t] ?? $t;
    }

    /**
     * Normalize a native MySQL/MariaDB type to a compact logical type.
     * Removes sizing and "unsigned" and maps common types to: int/string/json/datetime/date/time/float/bool/enum/set.
     *
     * @param  string $native e.g. "varchar(255)", "int(11) unsigned", "mediumtext"
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
        ];
        return $map[$t] ?? $t;
    }

    /**
     * Normalize a native PostgreSQL type (from `format_type`) into a compact logical type.
     * Sizing/precision is stripped, and common names are mapped to: int/string/json/datetime/date/time/float/bool/uuid.
     *
     * @param  string $native e.g. "character varying(255)", "timestamp without time zone", "int4"
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
     * Normalize a native SQLite type (affinity) into a compact logical type.
     * Uses SQLite's type affinity rules to classify into: int/text/blob/float/decimal/date/time.
     *
     * @param  string $native e.g. "INTEGER", "TEXT", "NUMERIC(10,2)"
     * @return string
     */
    protected static function normalizeTypeSqlite(string $native): string
    {
        $t = strtolower($native);
        $t = preg_replace('/\(.+\)/', '', $t) ?? $t;
        $t = trim($t);

        if (str_contains($t, 'int')) return 'int';
        if (str_contains($t, 'char') || str_contains($t, 'clob') || str_contains($t, 'text')) return 'string';
        if (str_contains($t, 'blob')) return 'blob';
        if (str_contains($t, 'real') || str_contains($t, 'floa') || str_contains($t, 'doub')) return 'float';
        if (str_contains($t, 'num') || str_contains($t, 'dec')) return 'float';
        if ($t === 'date') return 'date';
        if (str_contains($t, 'time')) return 'time';
        return $t;
    }
}
