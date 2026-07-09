<?php

namespace App\Console\Commands;

use App\Enums\LeadStatus;
use App\Enums\ProjectStatus;
use App\Models\AuditLog;
use App\Models\Lead;
use App\Models\NotificationLog;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * reka:prune — pemadaman berkala mengikut prinsip penyimpanan §12.8.
 */
class PruneCommand extends Command
{
    protected $signature = 'reka:prune';

    protected $description = 'Padam data mengikut tempoh penyimpanan PDPA (§12.8)';

    public function handle(): int
    {
        // Lead ditolak > 6 bulan.
        $rejectedLeads = Lead::query()
            ->where('status', LeadStatus::Rejected)
            ->where('updated_at', '<', now()->subMonths(6))
            ->get();
        foreach ($rejectedLeads as $lead) {
            $lead->delete();
        }

        // Projek cancelled/expired > 12 bulan + padam fail aset.
        $staleProjects = Project::query()
            ->withTrashed()
            ->whereIn('status', [ProjectStatus::Cancelled, ProjectStatus::Expired])
            ->where('updated_at', '<', now()->subMonths(12))
            ->get();
        foreach ($staleProjects as $project) {
            foreach ($project->assets as $asset) {
                Storage::disk('local')->delete($asset->path);
            }
            $project->forceDelete();
        }

        // Log notifikasi & audit > 24 bulan.
        $notifCount = NotificationLog::query()->where('created_at', '<', now()->subMonths(24))->delete();
        $auditCount = AuditLog::query()->where('created_at', '<', now()->subMonths(24))->delete();

        $this->info(sprintf(
            'Prune: %d lead ditolak, %d projek lapuk, %d log notifikasi, %d log audit.',
            $rejectedLeads->count(),
            $staleProjects->count(),
            $notifCount,
            $auditCount,
        ));

        return self::SUCCESS;
    }
}
