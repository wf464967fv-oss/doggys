<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\AdminAction;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->validate(['status' => ['nullable', Rule::enum(ReportStatus::class)]])['status'] ?? ReportStatus::Pending->value;

        return view('admin.contact.index', [
            'status' => $status,
            'messages' => ContactMessage::query()->where('status', $status)->latest()->paginate(25)->withQueryString(),
        ]);
    }

    public function handle(ContactMessage $message): RedirectResponse
    {
        $message->forceFill(['status' => ReportStatus::Handled, 'handled_at' => now()])->save();
        AdminAction::record('contact_message.handle', $message);

        return back()->with('status', __('admin.contact.handled'));
    }
}
