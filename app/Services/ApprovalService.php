<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Exceptions\GateException;
use App\Models\Approval;
use App\Models\AuditLog;
use App\Models\Generation;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;

/**
 * §5.2 P9 / §14.2 — kelulusan: snapshot BEKU (spec penuh + draft path + hash) +
 * identiti + IP/UA → status approved (TITIK BEKU: wizard & tweak baca-sahaja).
 */
class ApprovalService
{
    public function __construct(private SpecBuilder $specBuilder) {}

    /**
     * @param  array{pic_name:string, pic_position:string, pic_phone:string}  $identity
     */
    public function approve(Project $project, Generation $generation, array $identity, string $ip, string $userAgent): Approval
    {
        if ($project->isFrozen()) {
            throw new GateException('Draf telah diluluskan.');
        }

        $spec = $this->specBuilder->build($project);

        $draftPath = $generation->rendered_path;
        $draftHash = ($draftPath && Storage::disk('local')->exists($draftPath))
            ? hash('sha256', Storage::disk('local')->get($draftPath))
            : null;

        $approval = $project->approval()->create([
            'generation_id' => $generation->id,
            'snapshot' => [
                'spec' => $spec,
                'draft_path' => $draftPath,
                'draft_hash' => $draftHash,
            ],
            'pic_name' => $identity['pic_name'],
            'pic_position' => $identity['pic_position'],
            'pic_phone' => $identity['pic_phone'],
            'consent_pdpa' => true,
            'declare_authority' => true,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'approved_at' => now(),
        ]);

        $project->transitionTo(ProjectStatus::Approved, 'pic');

        AuditLog::record('pic', null, 'approval.recorded', $approval, [], $ip);

        return $approval;
    }
}
