<?php namespace Seiger\sSeo\Models;

use Illuminate\Database\Eloquent\Model;

class sSeoModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 's_seo';

    protected $fillable = [
        'resource_id', 'resource_type', 'lang',
        'meta_title', 'meta_description', 'meta_keywords', 'canonical_url',
        'og_title', 'og_description', 'og_image', 'og_type',
        'twitter_card', 'robots', 'structured_data', 'extra_meta',
        'priority', 'changefreq', 'last_modified'
    ];

    protected $casts = [
        'structured_data' => 'array',
        'extra_meta' => 'array',
    ];
}
