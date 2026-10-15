<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClaimStatus;
use App\Enums\ProviderStatus;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\AdminAction;
use App\Models\BadgeRequest;
use App\Models\Claim;
use App\Models\ContactMessage;
use App\Models\Provider;
use App\Models\ProviderReport;
use App\Models\ReviewReport;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Counters of what waits for moderation, and the latest admin actions.
     */
    public function index(): View
    {
        return view('admin.dashboard', [
            'counters' => [
                ['label' => 'claims', 'icon' => 'building', 'count' => Claim::query()->where('status', ClaimStatus::Pending)->count(), 'route' => 'admin.claims.index'],
                ['label' => 'badges', 'icon' => 'badge-check', 'count' => BadgeRequest::query()->where('status', ClaimStatus::Pending)->count(), 'route' => 'admin.badges.index'],
                ['label' => 'provider_reports', 'icon' => 'alert', 'count' => ProviderReport::query()->where('status', ReportStatus::Pending)->count(), 'route' => 'admin.provider-reports.index'],
                ['label' => 'review_reports', 'icon' => 'star', 'count' => ReviewReport::query()->where('status', ReportStatus::Pending)->count(), 'route' => 'admin.reviews.index'],
                ['label' => 'hidden_providers', 'icon' => 'lock', 'count' => Provider::query()->where('status', ProviderStatus::Hidden)->count(), 'route' => 'admin.providers.index', 'query' => ['status' => 'hidden']],
                ['label' => 'new_users', 'icon' => 'users', 'count' => User::query()->where('created_at', '>=', now()->subDays(7))->count(), 'route' => 'admin.users.index'],
                ['label' => 'messages', 'icon' => 'mail', 'count' => ContactMessage::query()->where('status', ReportStatus::Pending)->count(), 'route' => 'admin.contact.index'],
            ],
            'actions' => AdminAction::query()->with('user:id,name')->latest('created_at')->limit(15)->get(),
        ]);
    }

    /**
     * Full audit log of admin actions.
     */
    public function log(): View
    {
        return view('admin.log', [
            'actions' => AdminAction::query()->with('user:id,name')->latest('created_at')->latest('id')->paginate(50),
        ]);
    }
}
