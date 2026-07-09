<?php

namespace App\Filament\Widgets;

use App\Enums\ProjectStatus;
use App\Models\Generation;
use App\Models\Lead;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

// §5.3 — corong + kos AI bulan ini + queue health.
class FunnelStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $monthCost = Generation::query()
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('cost_estimate');

        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();

        return [
            Stat::make('Lead baru', Lead::where('status', 'new')->count())->color('info'),
            Stat::make('Dijemput', Project::where('status', ProjectStatus::Invited)->count()),
            Stat::make('Sedang isi', Project::where('status', ProjectStatus::InProgress)->count()),
            Stat::make('Dihantar', Project::where('status', ProjectStatus::Submitted)->count())->color('warning'),
            Stat::make('Draf sedia', Project::where('status', ProjectStatus::DraftReady)->count()),
            Stat::make('Diluluskan', Project::where('status', ProjectStatus::Approved)->count())->color('success'),
            Stat::make('Kos AI bulan ini', 'RM '.number_format((float) $monthCost, 2)),
            Stat::make('Queue', "{$pendingJobs} menunggu / {$failedJobs} gagal")
                ->color($failedJobs > 0 ? 'danger' : 'success'),
        ];
    }
}
