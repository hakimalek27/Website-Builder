<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Enums\ProjectStatus;
use App\Enums\Tier;
use App\Models\AuditLog;
use App\Models\Invitation;
use App\Models\Lead;
use App\Models\Project;
use App\Notifications\InvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Kelayakan lead → cipta Project + Invitation + hantar jemputan (§4.1 langkah 2).
 * Token: Str::random(40); SIMPAN HASH SHA-256 SAHAJA (§11.1).
 *
 * Nota (R1): tier & jakim_zone diberi nilai provisional di sini kerana kedua-dua
 * dipilih PIC dalam wizard (L0/L1). Skema §10 dikekalkan non-nullable.
 */
class LeadQualifier
{
    /**
     * @return array{project: Project, invitation: Invitation, token: string}
     */
    public function qualify(Lead $lead, string $picEmail, int $tokenDays = 30, int $aiQuota = 3): array
    {
        return DB::transaction(function () use ($lead, $picEmail, $tokenDays, $aiQuota) {
            $project = Project::create([
                'lead_id' => $lead->id,
                'mosque_name' => $lead->mosque_name,
                'tier' => Tier::MasjidKariah,   // provisional — dipilih di L0
                'is_gov' => false,
                'state' => $lead->state,
                'jakim_zone' => '',             // provisional — dipilih di L1
                'status' => ProjectStatus::Invited,
                'quota_ai_total' => $aiQuota,
            ]);

            $token = Invitation::generateToken();
            $invitation = Invitation::create([
                'project_id' => $project->id,
                'token_hash' => Invitation::hashToken($token),
                'pic_name' => $lead->pic_name,
                'pic_phone' => $lead->pic_phone,
                'pic_email' => $picEmail,
                'expires_at' => now()->addDays($tokenDays),
            ]);

            $lead->update([
                'status' => LeadStatus::Qualified,
                'project_id' => $project->id,
            ]);

            AuditLog::record('admin', null, 'lead.qualified', $lead, ['project_id' => $project->id]);
            AuditLog::record('admin', null, 'invitation.created', $invitation, ['project_id' => $project->id]);

            // Hantar jemputan (mail; WA ditambah Fasa 9 tanpa ubah kod ini).
            Notification::route('mail', $picEmail)
                ->notify(new InvitationNotification($invitation, $token, $project->mosque_name));

            AuditLog::record('admin', null, 'invitation.sent', $invitation, ['channel' => 'mail']);

            return ['project' => $project, 'invitation' => $invitation, 'token' => $token];
        });
    }
}
