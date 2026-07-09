<?php

namespace App\Console\Commands;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Console\Command;

/**
 * reka:sweep-expired — projek masih invited/in_progress tetapi tiada jemputan
 * yang boleh diguna (semua luput/revoked) → status expired (§4.2). Harian.
 */
class SweepExpiredCommand extends Command
{
    protected $signature = 'reka:sweep-expired';

    protected $description = 'Tandakan projek dengan token luput sebagai expired (§4.2)';

    public function handle(): int
    {
        $projects = Project::query()
            ->whereIn('status', [ProjectStatus::Invited, ProjectStatus::InProgress])
            ->get();

        $swept = 0;

        foreach ($projects as $project) {
            $hasUsable = $project->invitations()
                ->whereNull('revoked_at')
                ->where('expires_at', '>', now())
                ->exists();

            if (! $hasUsable) {
                $project->transitionTo(ProjectStatus::Expired, 'system', null, ['reason' => 'token_expired']);
                $swept++;
            }
        }

        $this->info("Sweep: {$swept} projek ditandakan expired.");

        return self::SUCCESS;
    }
}
