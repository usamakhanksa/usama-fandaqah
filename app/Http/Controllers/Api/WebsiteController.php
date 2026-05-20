<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebsitePage;
use App\Models\WebsiteSetting;
use App\Services\WebsiteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class WebsiteController extends Controller
{
    protected WebsiteService $service;

    public function __construct(WebsiteService $service)
    {
        $this->service = $service;
    }

    /**
     * Get website settings.
     */
    public function settings(Request $request)
    {
        $settings = $this->service->getSettings($request->user()->currentTeam->id);
        return Response::json($settings);
    }

    /**
     * Update website settings.
     */
    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'site_name' => 'string',
            'logo_path' => 'nullable|string',
            'primary_color' => 'nullable|string',
            'secondary_color' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'address' => 'nullable|string',
            'social_links' => 'nullable|array',
        ]);

        $settings = $this->service->updateSettings($request->user()->currentTeam->id, $data);
        return Response::json($settings);
    }

    /**
     * Get pages.
     */
    public function pages(Request $request)
    {
        $pages = $this->service->getPages($request->user()->currentTeam->id);
        return Response::json($pages);
    }

    /**
     * Store/Update a page.
     */
    public function savePage(Request $request, $id = null)
    {
        $data = $request->validate([
            'title_en' => 'required|string',
            'title_ar' => 'nullable|string',
            'slug' => 'required|string',
            'content_en' => 'required|string',
            'content_ar' => 'nullable|string',
            'is_published' => 'boolean',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
        ]);

        $page = $this->service->savePage($request->user()->currentTeam->id, $data, $id);
        return Response::json($page);
    }

    /**
     * Get gallery.
     */
    public function gallery(Request $request)
    {
        $gallery = $this->service->getGallery($request->user()->currentTeam->id);
        return Response::json($gallery);
    }
}
