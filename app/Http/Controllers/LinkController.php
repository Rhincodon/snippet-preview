<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LinkController extends Controller
{

    const MAX_LINKS_DAILY_GUEST = 5;
    const MAX_LINKS_DAILY_USER = 100;

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function submit(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'url' => 'required|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 400);
        }

        $data = $validator->validated();

        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            $linksDaily = session('links_daily', 0);
            $lastScan = session('last_scan', null);
            $limited = $linksDaily >= self::MAX_LINKS_DAILY_GUEST;
        } else {
            $linksDaily = $user->links_daily;
            $lastScan = $user->last_scan;
            $limited = $linksDaily >= self::MAX_LINKS_DAILY_USER;
        }

        $limited = $this->validateLimit($limited, $lastScan);

        if ($limited) {
            $limitNum = $user ? self::MAX_LINKS_DAILY_USER : self::MAX_LINKS_DAILY_GUEST;

            return response()->json([
                'errors' => [
                    'limit' => "You have exceeded your limit of {$limitNum} requests"
                ]
            ]);
        }

        /** @var Link $link */
        $link = Link::where([
            'url' => $data['url']
        ])->first();

        $today = new \DateTime();
        if ($link && $today->diff($link->updated_at)->days < 1) {
            $this->saveDailyInfo($user, $linksDaily);
            return response()->json([
                'snippet' => $link->toArray()
            ]);
        }

        if (!$link) {
            $link = new Link();
            $link->url = $data['url'];
        }

        $this->parseLink($link);
        $this->saveDailyInfo($user, $linksDaily);

        return response()->json([
            'snippet' => $link->toArray()
        ]);
    }

    /**
     * @param Link $link
     */
    private function parseLink($link) {
        // TODO
        $link->robots_allowed = true;
        $link->save();
    }

    /**
     * @param User|null $user
     * @param int $linksDaily
     *
     * @throws \Exception
     */
    private function saveDailyInfo($user, $linksDaily) {

        $linksDaily++;
        $now = new \DateTime();

        if (!$user) {
            session(['links_daily' => $linksDaily]);
            session(['last_scan' => $now]);
        } else {
            $user->links_daily = $linksDaily;
            $user->last_scan = $now;
            $user->save();
        }
    }

    /**
     * @param bool $limited
     * @param \DateTime|null $lastScan
     *
     * @return bool
     * @throws \Exception
     */
    private function validateLimit($limited, $lastScan) {

        if (!($lastScan instanceof \DateTime)) {
            return false;
        }

        if (!$limited) {
            return false;
        }

        $today = new \DateTime();
        if ($today->diff($lastScan)->days >= 1) {
            return false;
        }

        return true;
    }
}
