<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\SettingsService;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\MissingParamException;

class SettingsController extends Controller
{
    public function updateSettings(Request $request, Response $response)
    {
        $session = $request->getSession();
        $put = $request->getPut();

        extract($session);
        
        if (!$admin) {
            throw new UnauthorizedException();
        }

        foreach ($put as $key => $value) {
            SettingsService::set($key, $value);
        }

        $settings = SettingsService::getAll();

        $response->setStatus(200)->setJson($settings)->send();
    }

    public function updateSingleSetting(Request $request, Response $response)
    {
        $session = $request->getSession();
        $path_params = $request->getParams();
        $put = $request->getPut();

        extract($session);
        extract($path_params);
        extract($put);

        if (!$admin) {
            throw new UnauthorizedException();
        }

        if (!$value) {
            throw new MissingParamException();
            
        }

        SettingsService::set($key, $value);

        $settings = SettingsService::getAll();

        $response->setStatus(200)->setJson($settings)->send();

    }
}
