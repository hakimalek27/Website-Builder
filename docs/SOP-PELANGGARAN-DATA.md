# SOP Pelanggaran Data Peribadi — REKA

> ⚠️ **Untuk semakan perundangan Azan.** Dokumen operasi ini dijana mengikut §12.5, §12.7 & §12.10 spek. Sahkan dengan penasihat undang-undang sebelum bergantung padanya secara rasmi.

Rangka: **PDPA 2010 (Akta 709) + Pindaan 2024 [A1727]**. Azan/entiti perniagaan = **pengawal data**; penyedia AI & hosting = **pemproses data**.

## 1. Prosedur bila berlaku pelanggaran (s.12B, berkuat kuasa 1 Jun 2025)

1. **Kesedaran insiden** — rekod masa & sumber pengesanan.
2. **Bendung & rekod** — hentikan pendedahan; simpan bukti (log, tangkap skrin).
3. **Nilai "kemudaratan signifikan"** — kecederaan fizikal / kerugian kewangan / jejas kredit / dsb.
4. **Notifikasi Pesuruhjaya PDP** — secepat praktik, **dalam 72 jam** dari saat pelanggaran berlaku atau dari saat pengawal data dimaklumkan (pemberitahuan berperingkat dibenarkan; kelewatan mesti didokumen & dijustifikasi).
5. **Jika kemudaratan signifikan** — maklum subjek data **tanpa kelewatan, ≤7 hari** selepas notifikasi Pesuruhjaya.

Kegagalan notifikasi: denda ≤RM250k dan/atau penjara ≤2 tahun.

## 2. Query eksport subjek terjejas (per projek)

```sql
-- PIC & lead bagi projek terjejas
SELECT 'lead' AS jenis, pic_name, pic_phone, pic_email FROM leads WHERE project_id = ?
UNION ALL
SELECT 'invitation', pic_name, pic_phone, pic_email FROM invitations WHERE project_id = ?
UNION ALL
SELECT 'approval', pic_name, pic_phone, NULL FROM approvals WHERE project_id = ?;
```

Atau melalui Tinker:
```php
$project = App\Models\Project::find('...');
[$project->lead, $project->invitations, $project->approval];
```

## 3. Lampiran: Templat TIA (Transfer Impact Assessment) — 1 muka (§12.7)

Untuk setiap penyedia AI yang menerima data (rentas sempadan):

| Medan | Nilai |
|---|---|
| Penyedia AI | _(cth: Anthropic / OpenAI / GLM / Ollama-tempatan)_ |
| Negara destinasi data | _(cth: Amerika Syarikat / dalam negara jika Ollama)_ |
| Kategori data dihantar (SELEPAS minimisasi §8.3) | Nama masjid, bandar/negeri, tahun, kapasiti, senarai khidmat/fasiliti/kelas, mood. **TIADA** telefon/emel individu, no. akaun bank, nama+telefon PIC, IC. |
| Asas pemindahan | Persetujuan L9 (consent_pdpa) + keperluan kontrak |
| Mitigasi terbina | PII-minimisasi prompt (§8.3/§12.7), input_snapshot untuk audit, pilihan Ollama tempatan |

## 4. Penilaian ADMP / DPIA / DPbD (Garis Panduan JPDP 30 April 2026 — §12.10)

- **ADMP (Automated Decision-Making & Profiling):** Penjanaan draf kandungan **bukan** keputusan automatik terhadap individu dan **bukan** pemprofilan individu. Pencetus ADMP kemungkinan besar **TIDAK terpakai** pada skop MVP.
- **DPIA:** Tidak diperlukan pada skop semasa. **Jika** ciri masa depan membuat keputusan automatik tentang individu (cth pemarkahan/penapisan permohonan), DPIA formal diperlukan SEBELUM pelancaran ciri itu.
- **DPbD (Data Protection by Design)** — amalan tertanam dalam reka bentuk REKA:
  - Minimisasi PII dalam prompt AI (§8.3, §12.7)
  - Buang EXIF/GPS semasa muat naik imej (§11.4)
  - Mask() pada logging (§11.3) — telefon/emel/token tidak pernah penuh dalam log
  - Retensi & pemadaman berjadual (§12.8, command `reka:prune`)
  - Token PIC: SHA-256 hash sahaja disimpan (§11.1)

## 5. DPO

Pada skala MVP (ratusan PIC), Azan hampir pasti **DI BAWAH ambang** pelantikan DPO formal. Saluran subjek data: emel `privasi@{domain}`. Nilai semula bila skala membesar.
