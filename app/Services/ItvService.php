<?php

namespace App\Services;

use App\Models\Itv;
use App\Models\ChLinks;
use Illuminate\Support\Facades\DB;

class ItvService
{
    public function storeChannel(array $validatedData)
    {
        try {
            DB::beginTransaction();

            $logoPath = '';
            if (array_key_exists('logo', $validatedData) && $validatedData['logo']->isValid()) {
                $logo = $validatedData['logo'];
                $logoSrc = $logo->store('channel-logo', 'public');
                $logoPath = "storage/{$logoSrc}";
            }

            $itv = Itv::create([
                'number' => $validatedData['number'],
                'name' => $validatedData['name'],
                'tv_genre_id' => $validatedData['genre'],
                'logo' => $logoPath,
                'enable_tv_archive' => $validatedData['enable_tv_archive'] ?? 0,
                'mc_cmd' => $validatedData['mc_cmd'] ?? '',
                'tv_archive_duration' => $validatedData['tv_archive_duration'] ?? 168,
                'xmltv_id' => $validatedData['xmltv_id'] ?? '',
                'censored' => $validatedData['censored'] ?? 0,
                'epg_offset' => $validatedData['epg_offset'] ?? 0,
            ]);

            if (array_key_exists('links', $validatedData)) {
                $streamingLinks = $validatedData['links'];

                if (isset($streamingLinks['url']) && is_array($streamingLinks['url'])) {
                    foreach ($streamingLinks['url'] as $index => $url) {
                        $priority = $streamingLinks['priority'][$index];
                        $nginx = $streamingLinks['nginx'][$index];
                        $flussonic = $streamingLinks['flussonic'][$index];

                        ChLinks::create([
                            'ch_id' => $itv->id,
                            'url' => $url,
                            'priority' => $priority,
                            'nginx_secure_link' => $nginx,
                            'flussonic_tmp_link' => $flussonic
                        ]);
                    }
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function updateChannel($itv, array $validatedData, $request)
    {
        try {
            DB::beginTransaction();

            $itv->update([
                'number' => $validatedData['number'],
                'name' => $validatedData['name'],
                'tv_genre_id' => $validatedData['genre'],
                'status' => $request->status ?? 0,
                'enable_tv_archive' => $request->enable_tv_archive,
                'mc_cmd' => $request->mc_cmd ? $request->mc_cmd : '',
                'tv_archive_duration' => $request->tv_archive_duration ? $request->tv_archive_duration : 168,
                'xmltv_id' => $request->xmltv_id ? $request->xmltv_id : '',
                'censored' => $request->censored ?? 0,
                'epg_offset' => $request->epg_offset ? $request->epg_offset : 0,
            ]);

            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoSrc = $logo->store('channel-logo', 'public');
                $logoPath = "storage/{$logoSrc}";
                $itv->update(['logo' => $logoPath]);
            }

            $streamingLinks = $request->links;

            if (empty($streamingLinks['url'])) {
                // If there are no links in the request, delete all existing links associated with $itv->id
                ChLinks::where('ch_id', $itv->id)->delete();
            } else {
                // Retrieve existing links associated with $itv->id
                $existingLinks = ChLinks::where('ch_id', $itv->id)->pluck('url')->toArray();

                foreach ($streamingLinks['url'] as $index => $url) {
                    $priority = $streamingLinks['priority'][$index];
                    $nginx = $streamingLinks['nginx'][$index];
                    $flussonic = $streamingLinks['flussonic'][$index];
                    $linkId = isset($streamingLinks['id'][$index]) ? $streamingLinks['id'][$index] : 0;

                    // Check if we are creating a new record or updating an existing one
                    if ($linkId) {
                        // If ID is provided, find the record by ID
                        $chLink = ChLinks::find($linkId);
                        $chLink->update([
                            'ch_id' => $itv->id,
                            'url' => $url,
                            'priority' => $priority,
                            'nginx_secure_link' => $nginx,
                            'flussonic_tmp_link' => $flussonic
                        ]);
                    } else {
                        // If ID is not provided, create a new record
                        ChLinks::create([
                            'ch_id' => $itv->id,
                            'url' => $url,
                            'priority' => $priority,
                            'nginx_secure_link' => $nginx,
                            'flussonic_tmp_link' => $flussonic
                        ]);
                    }
                    // Remove the current link from the existing links list
                    if (($key = array_search($url, $existingLinks)) !== false) {
                        unset($existingLinks[$key]);
                    }
                }
                // Delete links that are not found in the request
                if (!empty($existingLinks)) {
                    ChLinks::where('ch_id', $itv->id)->whereIn('url', $existingLinks)->delete();
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function deleteChannel($itv)
    {
        DB::beginTransaction();

        try {
            $itv->delete();

            $removedOrder = $itv->number;

            Itv::where('number', '>', $removedOrder)
                ->decrement('number');

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }
}
