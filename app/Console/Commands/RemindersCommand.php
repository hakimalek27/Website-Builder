<?php

namespace App\Console\Commands;

use App\Enums\ProjectStatus;
use App\Models\NotificationLog;
use App\Models\Project;
use App\Services\Notifier;
use Illuminate\Console\Command;

/**
 * reka:reminders — (a) reminder wizard 3 hari idle (max 2×), (b) token luput ≤5 hari (§13).
 */
class RemindersCommand extends Command
{
    protected $signature = 'reka:reminders';

    protected $description = 'Hantar reminder wizard idle & amaran token hampir luput (§13)';

    public function handle(Notifier $notifier): int
    {
        $reminderCount = $this->sendWizardReminders($notifier);
        $expiryCount = $this->sendTokenExpiring($notifier);

        $this->info("Reminders: {$reminderCount} wizard, {$expiryCount} token luput.");

        return self::SUCCESS;
    }

    private function sendWizardReminders(Notifier $notifier): int
    {
        $count = 0;

        $projects = Project::query()
            ->where('status', ProjectStatus::InProgress)
            ->whereHas('invitation', fn ($q) => $q->where('last_active_at', '<', now()->subDays(3))->whereNull('revoked_at'))
            ->get();

        foreach ($projects as $project) {
            $sent = NotificationLog::where('project_id', $project->id)->where('event', 'wizard.reminder')->count();
            if ($sent >= 2) {
                continue; // max 2×
            }
            $notifier->wizardReminder($project, 'pautan borang anda');
            $count++;
        }

        return $count;
    }

    private function sendTokenExpiring(Notifier $notifier): int
    {
        $count = 0;

        $projects = Project::query()
            ->whereIn('status', [ProjectStatus::Invited, ProjectStatus::InProgress])
            ->whereHas('invitation', fn ($q) => $q
                ->whereNull('revoked_at')
                ->whereBetween('expires_at', [now(), now()->addDays(5)]))
            ->get();

        foreach ($projects as $project) {
            $already = NotificationLog::where('project_id', $project->id)
                ->where('event', 'token.expiring')
                ->where('created_at', '>=', now()->subDays(5))->exists();
            if ($already) {
                continue;
            }
            $notifier->tokenExpiring($project);
            $count++;
        }

        return $count;
    }
}
