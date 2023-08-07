<?php namespace Seiger\sSeo\Controllers;

class sSeoController
{
    //public $url;

    /**
     * Construct
     */
    public function __construct()
    {
        //$this->url = $this->moduleUrl();
        //Paginator::defaultView('pagination');
    }

    /**
     * Show tabs with custom system settings
     *
     * @return View
     */
    public function index(): View
    {
        return $this->view('index');
    }

    /**
     * Display render
     *
     * @param string $tpl
     * @param array $data
     * @return bool
     */
    public function view(string $tpl, array $data = []): View
    {
        return \View::make('sSeo::'.$tpl, $data);
    }
}
