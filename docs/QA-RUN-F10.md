# QA Run — Fasa 10 (hujung-ke-hujung)

Aliran corong penuh diuji secara automatik dalam `tests/Feature/Phase10/EndToEndTest.php` (AI melalui `Http::fake` — provider palsu). Setiap langkah + hasil:

| # | Langkah | Route/Perkhidmatan | Hasil |
|---|---|---|---|
| 1 | Borang minat awam | `POST /minat` | ✅ Lead dicipta, redirect terima-kasih |
| 2 | Layakkan & jemput | `LeadQualifier::qualify` | ✅ Project + Invitation (token hash) + notifikasi |
| 3 | Buka token PIC | `GET /b/{token}` (P1) | ✅ 200, hab wizard |
| 4 | Isi wizard (L0–L9) | `project_sections` + `project_pages` | ✅ Semua medan wajib diisi |
| 5 | Semak & hantar | `POST /b/{token}/hantar` (P3) | ✅ Gate 100% lulus → status `submitted` + notifikasi admin |
| 6 | Jana draf | `DraftGenerationService::request` | ✅ AI (fake) → validasi → render → `draft_ready`, kuota AI 1/3 |
| 7 | Lihat draf | `GET /b/{token}/draf/{gen}` (P5) | ✅ 200, banner "DRAF SAMPEL" |
| 8 | Tweak reka bentuk | `DesignRerenderService::rerender` | ✅ Render semula, **0 panggilan AI**, kuota reka 1/5 |
| 9 | Luluskan | `ApprovalService::approve` (P9) | ✅ Snapshot BEKU (spec + hash) + IP → `approved` |
| 10 | Eksport pakej serahan | `HandoverExporter::export` | ✅ ZIP dengan 6 artifak (spec.json, build-brief.md, sanity-seed.ndjson, draft/, README, assets/) |
| 11 | Notifikasi & audit | `notification_logs`, `audit_logs` | ✅ Log terhasil; audit lead.qualified/submitted/generation.succeeded/approval.recorded/handover.exported |

**build-brief.md:** tiada slot kosong `{{ }}` (diuji `build_brief_contains_real_values`).
**sanity-seed.ndjson:** setiap baris `json_decode` sah dengan `_type` dikenali (diuji `sanity_ndjson_valid`).

## Nota

- Ujian E2E mengesahkan aliran; QA manual dengan **telefon sebenar Azan** sebagai "PIC ujian" masih WAJIB sebelum PIC sebenar pertama (lihat GO-LIVE-CHECKLIST).
- Provider AI dalam ujian = fake; sambungan sebenar diuji melalui butang "Uji Sambungan" di panel admin selepas kunci diisi.
