<?php

namespace App\Services;

use App\Models\WebsitePage;
use App\Models\WebsiteSetting;
use App\Models\WebsiteGallery;
use Illuminate\Support\Facades\Cache;

class WebsiteService
{
    /**
     * Get all website settings.
     */
    public function getSettings($teamId)
    {
        return WebsiteSetting::where('team_id', $teamId)->first();
    }

    /**
     * Update website settings.
     */
    public function updateSettings($teamId, array $data)
    {
        return WebsiteSetting::updateOrCreate(
            ['team_id' => $teamId],
            $data
        );
    }

    /**
     * Get all pages for a team.
     */
    public function getPages($teamId)
    {
        return WebsitePage::where('team_id', $teamId)->get();
    }

    /**
     * Create or update a page.
     */
    public function savePage($teamId, array $data, $id = null)
    {
        return WebsitePage::updateOrCreate(
            ['id' => $id, 'team_id' => $teamId],
            $data
        );
    }

    /**
     * Get gallery images.
     */
    public function getGallery($teamId)
    {
        return WebsiteGallery::where('team_id', $teamId)->get();
    }

    /**
     * Add image to gallery.
     */
    public function addToGallery($teamId, array $data)
    {
        return WebsiteGallery::create(array_merge($data, ['team_id' => $teamId]));
    }
}
