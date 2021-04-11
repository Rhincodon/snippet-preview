<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get user links.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function links()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([]);
        }

        return response()->json($user->links->toArray());
    }
}
