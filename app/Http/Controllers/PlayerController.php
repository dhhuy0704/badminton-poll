<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\JsonResponse;

class PlayerController extends Controller
{
    /**
     * Get a list of user names ordered alphabetically.
     *
     * @return JsonResponse
     */
    public function getPlayerList(): JsonResponse
    {
        $userNames = Player::orderBy('name', 'asc')->pluck('name');

        return response()->json($userNames);
    }
}
