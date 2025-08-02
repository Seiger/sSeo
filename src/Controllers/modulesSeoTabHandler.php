<?php

use Seiger\sCommerce\Controllers\TabProductController;
use Seiger\sSeo\Models\sSeoModel;

switch (request()->input('get')) {
    case 'sseoproduct':
        $requestId = (int)request()->input('i', 0);
        $iUrl = trim($iUrl) ?: '&i=0';

        $result = (new TabProductController())->content($requestId);
        $tabs = $result['tabs'] ?? [];

        evo()->documentObject['type'] = 'product';
        if (!empty($result['data']['product'])) {
            evo()->documentObject = array_merge(evo()->documentObject, $result['data']['product']->toArray());
        }
        if (!empty($result['data']['item'])) {
            evo()->documentObject = array_merge(evo()->documentObject, $result['data']['item']->toArray());
        }

        $data = sSeoModel::where('resource_id', $requestId)->where('resource_type', 'product')->first()?->toArray();
        $data['id'] = evo()->documentObject['id'] = $requestId;
        $data['type'] = evo()->documentObject['type'] = 'product';
        $data['pagetitle'] = evo()->documentObject['pagetitle'] = $result['data']['item']->pagetitle ?? '';

        $_SESSION['itemname'] = $data['pagetitle'];
        $_SESSION['itemaction'] = 'Editing SEO Tab';
        break;
}