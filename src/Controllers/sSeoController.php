<?php namespace Seiger\sSeo\Controllers;

use EvolutionCMS\Models\SystemSetting;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Seiger\sMultisite\Models\sMultisite;
use Seiger\sSeo\Models\sRedirect;
use View;

/**
 * Show tabs with custom system settings
 *
 * @return \Illuminate\View\View
 */
class sSeoController
{
    /**
     * Returns the view for the redirects page.
     *
     * @return mixed The view for the redirects page.
     */
    public function redirects()
    {
        $GLOBALS['SystemAlertMsgQueque'] = &$_SESSION['SystemAlertMsgQueque'];
        $redirects = sRedirect::getAllRedirects('old_url', 'asc');

        $availableSites = collect([]);
        if (evo()->getConfig('check_sMultisite', false)) {
            $availableSites = sMultisite::all();
        }

        return $this->view('index', ['redirects' => $redirects, 'availableSites' => $availableSites]);
    }

    /**
     * Returns the view for the index page.
     *
     * @return mixed The view for the index page.
     */
    public function robots()
    {
        $GLOBALS['SystemAlertMsgQueque'] = &$_SESSION['SystemAlertMsgQueque'];
        $robots = file_get_contents(MODX_BASE_PATH . 'robots.txt') ?: '';
        return $this->view('index', compact('robots'));
    }

    /**
     * Returns the view for the index page.
     *
     * @return mixed The view for the index page.
     */
    public function configure()
    {
        $GLOBALS['SystemAlertMsgQueque'] = &$_SESSION['SystemAlertMsgQueque'];
        return $this->view('index');
    }

    /**
     * Update the redirects list with new data and create backups of old redirects.
     *
     * This method:
     * - Retrieves the submitted redirects from the request.
     * - Validates the input and returns an error message if no redirects are provided.
     * - Creates a backup of existing redirects if not already backed up for the current day.
     * - Maintains a maximum of 5 recent backup files and removes backups older than 7 days.
     * - Truncates the `sRedirect` table before inserting new redirects.
     * - Prevents duplicate redirects by checking for existing `old_url` entries.
     * - Inserts the validated redirects into the database.
     * - Clears the site cache after updating redirects.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success or error message.
     */
    public function updateRedirects()
    {
        $redirects = request()->input('redirects', []);

        if (empty($redirects)) {
            return redirect()->back()->with('error', trans('sSeo::global.no_redirects_provided'));
        }

        // Create backup directory if it doesn't exist
        $backupDir = storage_path('backups/');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Backup file for today
        $backupFile = $backupDir . 'redirects_backup_' . now()->format('Y-m-d') . '.json';

        if (!file_exists($backupFile)) {
            $backupRedirects = sRedirect::all()->toArray();
            file_put_contents($backupFile, json_encode($backupRedirects, JSON_PRETTY_PRINT));
        }

        // Cleanup old backups: keep the 5 most recent and remove backups older than 7 days
        $backupFiles = glob($backupDir . 'redirects_backup_*.json');
        if (count($backupFiles) > 5) {
            usort($backupFiles, function ($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            foreach (array_slice($backupFiles, 0, count($backupFiles) - 5) as $oldBackup) {
                unlink($oldBackup);
            }
        }

        foreach ($backupFiles as $file) {
            if (filemtime($file) < strtotime('-7 days')) {
                unlink($file);
            }
        }

        // Truncate the redirects table
        sRedirect::truncate();

        $insertData = [];
        foreach ($redirects as $redirect) {
            if (!isset($redirect['old'], $redirect['new'], $redirect['type'])) {
                continue;
            }

            $siteKey = trim($redirect['site_key']);
            $oldUrl = trim($redirect['old']);
            $newUrl = trim($redirect['new']);
            $type = intval($redirect['type']);

            if (empty($oldUrl) || empty($newUrl) || !in_array($type, [301, 302, 307])) {
                continue;
            }

            // Prevent duplicate redirects
            if (!sRedirect::where('old_url', $oldUrl)->exists()) {
                $insertData[] = [
                    'site_key' => $siteKey,
                    'old_url' => $oldUrl,
                    'new_url' => $newUrl,
                    'type' => $type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($insertData)) {
            sRedirect::insert($insertData);
        }

        // Clear full cache after updating redirects
        evo()->clearCache('full');
        return redirect()->back()->with('success', trans('sSeo::global.success_updated'));
    }


    /**
     * Update the content of the robots.txt file.
     *
     * This method handles the update of the robots.txt file by taking the
     * content passed from the request, validating that it is not empty,
     * and then writing it to the `robots.txt` file. If the input is empty,
     * it redirects the user back with an error message. Otherwise, it writes
     * the content to the file and returns a success message.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRobots()
    {
        $robots = request()->input('robots', []);

        if (empty($robots)) {
            return redirect()->back()->with('error', trans('sSeo::global.robots_text_empty'));
        }

        file_put_contents(MODX_BASE_PATH . 'robots.txt', $robots);

        return redirect()->back()->with('success', trans('sSeo::global.success_updated'));
    }

    /**
     * Updates the configure file with the new values.
     *
     * @return \Illuminate\Http\RedirectResponse The redirect response to the previous page.
     */
    public function updateConfigure()
    {
        $string = '<?php return [' . "\n";

        $string .= "\t" . '"manage_www" => ' . request()->integer('manage_www') . ',' . "\n";
        $string .= "\t" . '"paginates_get" => "' . request()->get('paginates_get', 'page') . '",' . "\n";

        $noindex_get = explode(',', request()->get('noindex_get', ''));
        $string .= "\t" . '"noindex_get" => [' . "\n";
        foreach ($noindex_get as $item) {
            $string .= "\t\t" . '"' . trim($item) . '",' . "\n";
        }
        $string .= "\t" . '],' . "\n";

        $string .= "\t" . '"redirects_enabled" => ' . request()->integer('redirects_enabled') . ',' . "\n";
        $string .= "\t" . '"generate_sitemap" => ' . request()->integer('generate_sitemap') . ',' . "\n";

        $string .= '];';

        // Save config
        $handle = fopen(EVO_CORE_PATH . 'custom/config/seiger/settings/sSeo.php', "w");
        fwrite($handle, $string);
        fclose($handle);

        evo()->clearCache('full');
        return redirect()->back();
    }

    /**
     * Returns the view for the specified template.
     *
     * @param string $tpl The template name.
     * @param array $data Optional data to be passed to the view.
     * @return mixed The view for the specified template.
     */
    public function view(string $tpl, array $data = [])
    {
        return View::make('sSeo::'.$tpl, $data);
    }
}
