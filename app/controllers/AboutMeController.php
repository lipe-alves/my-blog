<?php

namespace App\Controllers;

use App\Core\Request;

class AboutMeController extends ComponentsController
{
    public function index(): void
    {
        $session = $this->request->getSession();
        $settings = $session["settings"];
        extract($settings);

        $this->page("about-me", [
            "layout" => "base-page",
            "title" => $blog_name,
            "description" => $blog_catchline,
            "keywords" => [],
        ]);
    }

    public function html(Request $request): void
    {
        $this->views["index"] = "index";
        $this->views["default"] = "index";
        parent::html($request);
    }
}
