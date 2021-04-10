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

    const LIMIT_LIFETIME_DAYS = 1;
    const HOURS_IN_DAY = 24;

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

        $limited = $this->checkLimited($limited, $lastScan);

        if ($limited) {
            $limitNum = $user ? self::MAX_LINKS_DAILY_USER : self::MAX_LINKS_DAILY_GUEST;

            return response()->json([
                'errors' => [
                    'limit' => "You have exceeded your limit of {$limitNum} requests"
                ]
            ]);
        }

        $link = $this->processUrl($data['url']);
        $this->saveUserLimitInfo($user, $linksDaily);

        return response()->json([
            'snippet' => $link->toArray()
        ]);
    }

    /**
     * @param string $url
     *
     * @return Link
     * @throws \Exception
     */
    private function processUrl($url) {

        /** @var Link $link */
        $link = Link::where([
            'url' => $url
        ])->first();

        if ($link) {
            $today = new \DateTime();
            $diffHours = ($today->getTimestamp() - $link->updated_at->getTimestamp()) / 60 / 60;

            if ($diffHours <= self::HOURS_IN_DAY) {
                return $link;
            }
        }

        if (!$link) {
            $link = new Link();
            $link->url = $url;
        }

        return $this->parseUrl($link);
    }

    /**
     * @param Link $link
     *
     * @return Link
     */
    private function parseUrl($link) {
        // TODO
        $link->robots_allowed = true;
        $link->save();

        return $link;
    }

    /**
     * @param User|null $user
     * @param int $linksDaily
     *
     * @throws \Exception
     */
    private function saveUserLimitInfo($user, $linksDaily) {

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
    private function checkLimited($limited, $lastScan) {

        if (!($lastScan instanceof \DateTime)) {
            return false;
        }

        $today = new \DateTime();
        if ($today->diff($lastScan)->days >= self::LIMIT_LIFETIME_DAYS) {
            return false;
        }

        return $limited;
    }
}
