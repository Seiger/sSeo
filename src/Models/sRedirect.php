<?php namespace Seiger\sSeo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class sRedirect extends Model
{
    use HasFactory;

    protected $fillable = ['site_key', 'old_url', 'new_url', 'type'];

    /**
     * Apply human-friendly (natural) sorting to a query builder.
     *
     * This method ensures strings with numeric suffixes are sorted as humans expect
     * (e.g., test1, test2, test10) instead of lexicographically (e.g., test1, test10, test2).
     *
     * Supports PostgreSQL, MySQL, MariaDB, and SQLite.
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param string $column
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByNatural($builder, string $column, string $direction = 'asc')
    {
        $driver = DB::getDriverName();
        $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';

        if ($driver === 'pgsql') {
            return $builder->orderByRaw("
                regexp_replace(CAST($column AS TEXT), '[0-9]+$', '') $direction,
                COALESCE(CAST((regexp_match(CAST($column AS TEXT), '[0-9]+$'))[1] AS INTEGER), 0) $direction
            ");
        }

        if (in_array($driver, ['mysql', 'mariadb'])) {
            return $builder->orderByRaw("
                REGEXP_REPLACE(CAST($column AS CHAR), '[0-9]+$', '') $direction,
                CAST(REGEXP_SUBSTR(CAST($column AS CHAR), '[0-9]+$') AS UNSIGNED) $direction
            ");
        }

        if ($driver === 'sqlite') {
            return $builder->orderByRaw("
                regexp_replace(CAST($column AS TEXT), '[0-9]+$', '') COLLATE NOCASE $direction,
                CAST(substr(CAST($column AS TEXT), regexp_instr(CAST($column AS TEXT), '[0-9]+$')) AS INTEGER) $direction
            ");
        }

        return $builder->orderBy($column, $direction);
    }

    /**
     * Scope a query to only include redirects for the current site.
     *
     * This method filters redirects based on the current site's `site_key`,
     * ensuring that each site only manages its own redirects.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function forCurrentSite()
    {
        return self::where('site_key', evo()->getConfig('site_key', 'default'));
    }
}
