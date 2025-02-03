<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('s_seo', function (Blueprint $table) {
            $table->id();
            $table->integer('resource_id')->index()->comment('Resource ID (page, product, etc.)');
            $table->string('resource_type', 64)->default('document')->comment('Resource Type (document, product, etc.)');
            $table->string('lang', 4)->default('base')->comment('Localization (for multilingual sites)');
            $table->string('meta_title', 255)->default('')->comment('Page Meta title');
            $table->mediumText('meta_description')->default('')->comment('Page Meta description');
            $table->mediumText('meta_keywords')->default('')->comment('Page Meta keywords');
            $table->string('canonical_url', 255)->default('')->comment('Canonical URL');
            $table->string('og_title', 255)->default('')->comment('Open Graph title');
            $table->mediumText('og_description')->default('')->comment('Open Graph description');
            $table->string('og_image', 255)->default('')->comment('URL to image');
            $table->string('og_type', 50)->default('website')->comment('Type OG (article, product etc.)');
            $table->string('twitter_card', 50)->default('summary')->comment('Type Twitter Card');
            $table->enum('robots', ['', 'index,follow', 'index,nofollow', 'noindex,nofollow'])->default('');
            $table->longText('structured_data')->default('')->comment('JSON-LD for structured data');
            $table->jsonb('extra_meta')->comment('Additional meta tags (key=value format)');
            $table->unsignedDecimal('priority', 2, 1)->default(0.5)->comment('Page priority for XML Sitemap');
            $table->enum('changefreq', ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'])->default('weekly')->comment('Sitemap change frequency');
            $table->timestamp('last_modified')->nullable()->comment('Sitemap change frequency');
            $table->timestamps();
        });

        Schema::create('s_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('site_key')->default('default');
            $table->string('old_url');
            $table->string('new_url');
            $table->unsignedMediumInteger('type')->default(301);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_redirects');
        Schema::dropIfExists('s_seo');
    }
};
