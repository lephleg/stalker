<?php

namespace App\Http\Controllers;

use App\Site;
use App\Visit;
use App\Visitor;
use Config;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use \Illuminate\Contracts\Filesystem\FileNotFoundException;

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
     * Registers a new site
     * @param Request $request
     * @return Site
     */
    public function store(Request $request)
    {

        // validate request payload
        $request->validate([
            'name' => 'required|string',
            'url' => 'required|url'
        ]);

        try {
            // create a new site record
            $site = Site::create([
                'name' => $request->name,
                'url' => $request->url
            ]);
        } catch (\Exception $e) {
            \Log::error($e->getLine() . '-' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('messages.site_stored_error')
            ], 500);
        }

        return $site;

    }

    /**
     * Returns the modified javascript snippet that should be placed on website's HTML
     * @param $id
     * @return \Illuminate\Http\JsonResponse|mixed|string
     */
    public function getSnippet($id)
    {

        // verify site existence
        $site = Site::findOrFail($id);

        try {
            // fetch snippet template from storage
            $content = Storage::get('snippet.js');
        } catch (FileNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.internal_error')
            ], 500);
        }

        // replace placeholders with stalker & sites details
        $url = parse_url(Config::get('app.url'));
        $content = str_replace("<stalker_host>", $url['host'], $content);
        $content = str_replace("<site_id>", $site->id, $content);

        // return modified snippet to user
        return $content;

    }

    /**
     * Serves the javascript tracking code
     * @param $id
     * @return string
     */
    public function getTrackingCode($id)
    {

        // verify site existence
        Site::findOrFail($id);

        try {
            // fetch tracking code from storage and serve it
            $file =  Storage::get('tracking.js');
        } catch (FileNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.internal_error')
            ], 500);
        }

        return $file;

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
                'ip_address' => $request->getClientIp(),
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
