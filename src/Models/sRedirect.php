<?php namespace Seiger\sSeo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sRedirect extends Model
{
    use HasFactory;

    protected $fillable = ['site_key', 'old_url', 'new_url', 'redirect_type'];

    /**
     * Ensure the redirect is unique before saving.
     */
    public static function addRedirect(string $oldUrl, string $newUrl, int $type): bool
    {
        if (self::where('old_url', $oldUrl)->exists()) {
            return false;
        }

        return self::create([
            'old_url' => trim($oldUrl),
            'new_url' => trim($newUrl),
            'type' => $type,
        ]) ? true : false;
    }

    /**
     * Retrieve all redirects, optionally sorted.
     */
    public static function getAllRedirects(string $orderBy = 'created_at', string $direction = 'desc')
    {
        return self::orderBy($orderBy, $direction)->get();
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
