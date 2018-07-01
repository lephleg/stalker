<?php

namespace App\Http\Controllers;

use App\Site;
use App\Visit;
use App\Visitor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Class SitesController
 * @package App\Http\Controllers
 */
class SitesController extends Controller
{
    /**
     * Returns the sites listing
     * @return Site[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {

        $sites = Site::all();

        return $sites;

    }

    /**
     * Serves the javascript tracking code
     * @param Request $request
     * @param $id
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getTrackingCode(Request $request, $id)
    {

        // validate request
        if (!$request->has('key')) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.site_key_missing')
            ], 400);
        }

        // verify site existence and site key
        $site = Site::where('id', $id)
            ->where('key', $request->key)
            ->first();

        if (!$site) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.site_not_found')
            ], 404);
        }

        // fetch tracking code from storage and serve it
        return Storage::get('tracking.js');

    }

    /**
     * Receives tracking data about a site, validates and store them
     * @param Request $request
     * @param $id
     * @return array
     */
    public function storeTrackingData(Request $request, $id)
    {
        // validate site existence
        $site = Site::findOrFail($id);

        // validate request payload
        $request->validate([
            'vid' => 'required|string',
            'visitsCount' => 'required|integer',
            'agent' => 'required|string',
            'url' => 'required|url',
            'visitedAt' => 'required|date',
            'ipAddress' => 'required|ip'
        ]);

        // verify this is a new or a returning user
        $visitor = Visitor::where('site_id', $site->id)
            ->where('vid', $request->vid)
            ->first();

        if (!$visitor) {
            $this->verifySync($request, true);
            try {
                $visitor = Visitor::create([
                    'site_id' => $site->id,
                    'vid' => $request->vid,
                    'agent' => $request->agent
                ]);
            } catch (\Exception $e) {
                \Log::error($e->getLine() . '-' . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => __('messages.visitor_store_error')
                ], 400);
            }
        } else {
            $this->verifySync($request, false, $visitor);
        }

        try {
            $visit = Visit::create([
                'visitor_id' => $visitor->id,
                'ip_address' => $request->ipAddress,
                'url' => $request->url,
                'visited_at' => new Carbon($request->visitedAt)
            ]);
        } catch (\Exception $e) {
            \Log::error($e->getLine() . '-' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('messages.visit_store_error')
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => __('messages.store_successful')
        ], 200);

    }


    /**
     * Verifies the validity of visits count received and prints any warnings
     * Note: this does NOT break the flow if synchronization flaws are detected
     * @param Request $request
     * @param bool $isNew
     * @param Visitor|null $visitor
     */
    private function verifySync(Request $request, bool $isNew, Visitor $visitor = null)
    {

        if ($isNew && $request->visitsCount !== 1) {
            \Log::warning(
                'A new user with increased visits count received. Server and client not in sync.',
                $request->all()
            );
        }

        if (!$isNew && $request->visitsCount !== $visitor->visits->count() + 1) {
            \Log::warning(
                'An existing user with faulty visits count received. Server and client not in sync.',
                [$request->all(), $visitor]
            );
        }

    }
}
