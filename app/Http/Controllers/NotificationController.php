<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function clear(Request $request): RedirectResponse
    {
        $familyId = $request->user()->family_id;

        NotificationLog::query()
            ->where('family_id', $familyId)
            ->where('channel', 'system')
            ->delete();

        return back()->with('success', 'Notifikasi berhasil dibersihkan.');
    }
}
