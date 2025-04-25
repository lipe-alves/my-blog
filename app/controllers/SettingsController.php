<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\SettingsModel;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\MissingParamException;

class SettingsController extends Controller
{
    public function updateSettings(Request $request, Response $response): void
    {
        $session = $request->getSession();
        $patch = $request->getPatch();

        extract($session);
        
        if (!$is_admin) {
            throw new UnauthorizedException();
        }

        foreach ($patch as $key => $value) {
            SettingsModel::set($key, $value);
        }

        $settings = SettingsModel::getAll();

        $request->reloadSession();

        $response->setStatus(200)->setJson($settings)->send();
    }

    public function updateSingleSetting(Request $request, Response $response): void
    {
        $session = $request->getSession();
        $path_params = $request->getParams();
        $patch = $request->getPut();

        extract($session);
        extract($path_params);
        extract($patch);

        if (!isset($admin) || !(bool)$admin) {
            throw new UnauthorizedException();
        }

        if (!isset($value)) {
            throw new MissingParamException("value");
        }

        SettingsModel::set($key, $value);

        $settings = SettingsModel::getAll();

        $request->reloadSession();

        $response->setStatus(200)->setJson($settings)->send();

    }
}
