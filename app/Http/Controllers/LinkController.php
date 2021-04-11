<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use RobotsTxtParser\RobotsTxtParser;
use RobotsTxtParser\RobotsTxtValidator;

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
            $now = new \DateTime();
            $diffHours = ($now->getTimestamp() - $link->updated_at->getTimestamp()) / 60 / 60;

            if ($diffHours <= self::HOURS_IN_DAY) {
                return $link;
            }
        }

        return $this->parseUrl($url, $link);
    }

    /**
     * @param string $url
     * @param Link $link
     *
     * @return Link
     */
    private function parseUrl($url, $link = null) {

        if (!$link) {
            $link = new Link();
            $link->url = $url;
        }

        $link->robots_allowed = $this->checkRobotsTxt($url);
        if (!$link->robots_allowed) {
            $link->save();
            return $link;
        }

        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTMLFile($url);
        libxml_use_internal_errors(false);
        $metaEls = $doc->getElementsByTagName('meta');

        foreach ($metaEls as $meta) {
            $property = $meta->getAttribute('property');

            if ($property === 'og:title') {
                $link->title = $meta->getAttribute('content');
            } elseif ($property === 'og:description') {
                $link->description = $meta->getAttribute('content');
            } elseif ($property === 'og:image') {
                $link->image_url = $meta->getAttribute('content');
            }
        }

        $link->save();
        return $link;
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    private function checkRobotsTxt($url) {
        $urlParts = parse_url($url);

        if (!$urlParts || !array_key_exists('scheme', $urlParts)
            || !array_key_exists('host', $urlParts)) {
            return false;
        }

        $robotsPath = "{$urlParts['scheme']}://{$urlParts['host']}/robots.txt";
        $robotsData = file_get_contents($robotsPath);

        if (!$robotsData) {
            return true;
        }

        $parser = new RobotsTxtParser($robotsData);
        $validator = new RobotsTxtValidator($parser->getRules());

        return $validator->isUrlAllow($url);
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
