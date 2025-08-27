<?php namespace Seiger\sMultisite\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/**
 * Class PublishAssets
 *
 * Prunes outdated published assets and republishes package files.
 * - Deletes specific target files before publish
 * - Calls vendor:publish for this provider
 */
class PublishAssets extends Command
{
    /** @var string */
    protected $signature = 'sseo:publish {--no-prune : Do not delete existing files before publish}';

    /** @var string */
    protected $description = 'Publish sSeo assets (with optional prune).';

    public function handle(Filesystem $fs): int
    {
        // 1) Targets to delete before publishing
        $targets = [
            public_path('assets/site/sseo.min.css'),
            public_path('assets/site/sseo.js'),
        ];

        if (!$this->option('no-prune')) {
            foreach ($targets as $path) {
                // File::delete() is safe even if file does not exist
                $fs->delete($path);
            }
        }

        // 2) Publish (force overwrite)
        $this->call('vendor:publish', [
            '--provider' => 'Seiger\sSeo\sSeoServiceProvider',
        ]);

        // 3) (Optional) drop VERSION file for debugging
        try {
            $ver = \Composer\InstalledVersions::getVersion('seiger/sseo');
            $fs->ensureDirectoryExists(public_path('assets/site'));
            $fs->put(
                public_path('core/vendor/seiger/sseo/config/sSeoCheck.php'),
                "<?php return ['check_sSeo' => true, 'sSeoVer' => '" . $ver . "'];"
            );
        } catch (\Throwable) {
            // ignore if class not available
        }

        $this->info('sSeo assets published.');
        return self::SUCCESS;
    }
}
