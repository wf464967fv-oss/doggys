<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\ContactMessage;
use App\Notifications\ContactMessageReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('pages.contact');
    }

    /**
     * Stores the message (admin "messages" screen) and forwards it to the Doggys mailbox.
     */
    public function store(ContactRequest $request): RedirectResponse
    {
        $message = new ContactMessage($request->validated() + ['locale' => app()->getLocale()]);
        $message->user()->associate($request->user());
        $message->save();

        Notification::route('mail', config('mail.from.address'))->notify(new ContactMessageReceived($message));

        return redirect()->to(localized_route('contact'))->with('status', __('contact.sent'));
    }
}
