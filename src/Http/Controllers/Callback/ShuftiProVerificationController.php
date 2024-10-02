<?php

namespace Fintech\Ekyc\Http\Controllers\Callback;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShuftiProVerificationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        logger('Shufti Pro Callback', $request->all());
    }
}
