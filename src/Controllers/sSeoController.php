<?php namespace Seiger\sSeo\Controllers;

use EvolutionCMS\Models\SystemSetting;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use View;

class sSeoController
{
    //public $url;

    /**
     * Construct
     */
    public function __construct()
    {
        //Paginator::defaultView('pagination');
    }

    /**
     * Show tabs with custom system settings
     *
     * @return View
     */
    public function index()
    {
        return $this->view('index');
    }


    /**
     * Update settings configuration
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updateConfigure()
    {
        $string = '<?php return [' . "\n";

        $string .= "\t" . '"manage_www" => ' . (int)request()->get('manage_www', 0) . ',' . "\n";

        $string .= "\t" . '"paginates_get" => "' . request()->get('paginates_get', 'page') . '",' . "\n";

        $noindex_get = explode(',', request()->get('noindex_get', ''));
        $string .= "\t" . '"noindex_get" => [' . "\n";
        foreach ($noindex_get as $item) {
            $string .= "\t\t" . '"' . trim($item) . '",' . "\n";
        }
        $string .= "\t" . '],' . "\n";

        $string .= '];';

        // Save config
        $handle = fopen(EVO_CORE_PATH . 'custom/config/seiger/settings/sSeo.php', "w");
        fwrite($handle, $string);
        fclose($handle);

        evo()->clearCache('full');
        return redirect()->back();
    }

    /**
     * Display render
     *
     * @param string $tpl
     * @param array $data
     * @return View
     */
    public function view(string $tpl, array $data = [])
    {
        return View::make('sSeo::'.$tpl, $data);
    }
}
