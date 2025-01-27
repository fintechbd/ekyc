<?php

namespace Fintech\Ekyc\Http\Controllers\Callback;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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
