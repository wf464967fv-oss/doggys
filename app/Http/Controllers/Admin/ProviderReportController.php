<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProviderReportType;
use App\Enums\ProviderStatus;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\AdminAction;
use App\Models\ProviderReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Reports sent from the public listings: errors to fix, and GDPR removal requests.
 */
class ProviderReportController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->validate(['status' => ['nullable', Rule::enum(ReportStatus::class)]])['status'] ?? ReportStatus::Pending->value;

        return view('admin.provider-reports.index', [
            'status' => $status,
            'reports' => ProviderReport::query()->where('status', $status)
                ->with('provider:id,name,slug,city,status')
                ->oldest()->paginate(25)->withQueryString(),
        ]);
    }

    /**
     * Marks the report handled (the admin fixed the listing) or dismissed.
     */
    public function close(Request $request, ProviderReport $report): RedirectResponse
    {
        $status = ReportStatus::from($request->validate(['status' => ['required', Rule::in([ReportStatus::Handled->value, ReportStatus::Dismissed->value])]])['status']);

        $report->forceFill(['status' => $status, 'handled_at' => now()])->save();
        AdminAction::record('provider_report.'.$status->value, $report);

        return back()->with('status', __('admin.reports.closed'));
    }

    /**
     * Applies a GDPR removal request: the listing is removed (and never imported again).
     */
    public function applyRemoval(ProviderReport $report): RedirectResponse
    {
        abort_unless($report->type === ProviderReportType::RemovalRequest && $report->status === ReportStatus::Pending, 409);

        DB::transaction(function () use ($report) {
            $report->provider->forceFill(['status' => ProviderStatus::Removed])->save();
            $report->forceFill(['status' => ReportStatus::Handled, 'handled_at' => now()])->save();
            AdminAction::record('provider_report.apply_removal', $report, $report->provider->name);
        });

        return back()->with('status', __('admin.reports.removal_applied'));
    }
}
