<?php

namespace Modules\Sms\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sms\Entities\SmsMessage;

class SmsLogController extends Controller
{
    public function index(Request $request)
    {
        $query = SmsMessage::query()->latest();

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($qbuilder) use ($q) {
                $qbuilder->where('to', 'like', "%{$q}%")
                    ->orWhere('message', 'like', "%{$q}%")
                    ->orWhere('template_key', 'like', "%{$q}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $messages = $query->paginate()->withQueryString();

        return view('sms::user.logs.index', [
            'messages' => $messages,
        ]);
    }
}
