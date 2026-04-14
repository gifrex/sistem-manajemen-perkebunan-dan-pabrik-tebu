# Sistem ERP Perkebunan Tebu

Sistem Enterprise Resource Planning (ERP) untuk manajemen operasional perkebunan tebu, dibangun dengan Laravel.

**Live Website:** [sugarcane.sblampung.com](https://sugarcane.sblampung.com)

---

## Deskripsi

Sistem ini mengelola seluruh alur kerja operasional perkebunan tebu, mulai dari perencanaan kerja harian, eksekusi lapangan, juga pelaporan dan integrasi dengan pabrik. Sistem mencakup manajemen sumber daya (tenaga kerja, alat, material), alur persetujuan bertingkat, hingga monitoring hasil panen.

---

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Framework | Laravel (PHP) |
| Database | MariaDB |
| Authentication | Laravel Session-based |
| Frontend | Blade Template, JavaScript, Alpine JS |

---

## Arsitektur Sistem

Sistem menggunakan pola arsitektur 3-layer untuk separation of concerns:

```
┌─────────────────────────────────────┐
│           Controllers               │  → Routing HTTP, validasi, response
├─────────────────────────────────────┤
│            Services                 │  → Business logic, orkestrasi
├─────────────────────────────────────┤
│          Repositories               │  → Data access, query database
└─────────────────────────────────────┘
```

**Prinsip:**

- **Controllers** - Menangani routing, validasi request, dan response. Tidak ada query database.
- **Services** - Berisi logika bisnis dan orkestrasi antar repository. Tidak akses database langsung (kecuali `DB::transaction()`).
- **Repositories** - Semua operasi database (read/write, joins, raw SQL, query optimization).

---

## Fitur Utama

### 1. Perencanaan dan Pelaksanaan Kerja

- RKH (Rencana Kerja Harian) - Perencanaan kerja harian mandor dan pekerja
- LKH (Laporan Kegiatan Harian) - Pencatatan realisasi pekerjaan harian
- Monitoring progress pekerjaan real-time
- Manajemen aktivitas berbasis plot dan batch

### 2. Manajemen Sumber Daya

- Penugasan dan absensi tenaga kerja
- Alokasi kendaraan dan alat berat
- Kontrol penggunaan material (pupuk, herbisida, dll)
- Perhitungan upah harian dan borongan

### 3. Manajemen Inventori dan Logistik

- Stock pupuk dan herbisida
- Surat jalan material
- Tracking distribusi material ke lapangan
- Rekonsiliasi penggunaan material

### 4. Agronomi

- Manajemen batch tanam
- Tracking siklus tanaman per plot
- Pencatatan aktivitas pemeliharaan
- Monitoring kesehatan tanaman

### 5. Integrasi Pabrik

- Integrasi dengan sistem timbangan pabrik
- Tracking hasil panen per batch
- Sinkronisasi data tebang muat
- Monitoring produktivitas panen

### 6. Alur Persetujuan

- Sistem approval multi-level berbasis jabatan
- Routing approval otomatis sesuai jenis kegiatan
- Audit trail lengkap untuk setiap transaksi
- Notifikasi status persetujuan

### 7. Pelaporan

- Laporan DTH (Daily Time and Hour)
- Rekap LKH per periode
- Laporan produktivitas operator
- Dashboard monitoring kegiatan

---

## Modul-Modul Utama

### Rencana Kerja Harian (RKH)

Perencanaan kerja harian yang dibuat oleh mandor:

- Aktivitas yang akan dikerjakan
- Plot dan luas yang ditarget
- Penugasan pekerja dan kendaraan
- Estimasi material yang dibutuhkan

### Laporan Kegiatan Harian (LKH)

Realisasi pekerjaan lapangan:

- Plot dan luas yang diselesaikan
- Jam kerja pekerja (harian/borongan)
- Material yang terpakai
- Hasil panen (untuk aktivitas tebang)

### Stock Pupuk dan Herbisida

Manajemen inventori material:

- Penerimaan barang (PO)
- Stock opname
- Alokasi ke lapangan
- Rekonsiliasi penggunaan

### Surat Jalan

Tracking distribusi material:

- Pembuatan surat jalan
- Validasi penerimaan di lapangan
- Integrasi dengan LKH untuk penggunaan aktual

### Integrasi Pabrik

Sinkronisasi data pabrik:

- Data timbangan hasil panen
- Matching dengan LKH tebang
- Kalkulasi produktivitas per batch
- Rekonsiliasi tonase

### Agronomi

Data teknis perkebunan:

- Master varietas tebu
- Standar aktivitas per fase tanaman
- Monitoring pertumbuhan batch
- Analisis produktivitas per varietas

---

## Konsep Domain

### Sistem Masterlist dan Batch

| Konsep | Deskripsi |
|--------|-----------|
| Masterlist | Data master plot perkebunan dengan tracking batch aktif |
| Batch | Siklus tanam untuk sebuah plot (nomor batch, tanggal tanam, varietas, luas) |
| Active Batch | Batch yang sedang berjalan, direferensikan via `masterlist.activebatchno` |

Setiap plot memiliki satu batch aktif pada satu waktu. Batch baru dibuat otomatis saat approval LKH untuk aktivitas tanam.

### Manajemen Aktivitas

- Aktivitas dikelompokkan berdasarkan jenis (tanam, rawat, panen, pupuk, herbisida)
- Tracking pekerjaan per plot dengan perhitungan luas
- Alokasi material dan tenaga kerja per aktivitas
- Validasi aktivitas berbasis kondisi plot dan batch aktif

### Hirarki Persetujuan

- Konfigurasi level approval per grup aktivitas
- Routing berbasis jabatan: Mandor → Asisten → Manager
- Progress status otomatis sesuai level persetujuan
- Trigger otomatis: Generate LKH dari RKH approved, generate material usage, generate batch baru untuk aktivitas tanam

---

## Alur Kerja Sistem

### Flow RKH → LKH → Approval

```
Mandor membuat RKH (Rencana Kerja Harian)
         │
         ▼
RKH di-approve oleh atasan
         │
         ▼
Sistem generate LKH template dari RKH
         │
         ▼
Mandor input realisasi pekerjaan di LKH
         │
         ▼
LKH di-submit untuk approval
         │
         ▼
LKH di-approve oleh atasan
         │
         ▼
Sistem generate batch baru (jika aktivitas tanam)
         │
         ▼
Update stock material (jika ada penggunaan)
         │
         ▼
Data siap untuk pelaporan dan integrasi pabrik
```

### Flow Material dan Logistik

```
Purchase Order material (pupuk/herbisida)
         │
         ▼
Penerimaan barang & update stock
         │
         ▼
Pembuatan surat jalan untuk distribusi
         │
         ▼
Alokasi material ke RKH/LKH
         │
         ▼
Pencatatan penggunaan aktual di LKH
         │
         ▼
Rekonsiliasi stock vs penggunaan
```

### Flow Integrasi Pabrik

```
LKH tebang di-approve
         │
         ▼
Data tonase dari timbangan pabrik
         │
         ▼
Matching otomatis berdasarkan tanggal & batch
         │
         ▼
Kalkulasi produktivitas per batch
         │
         ▼
Update status panen batch
         │
         ▼
Generate laporan hasil panen
```

---

## Keamanan dan Audit

| Aspek | Implementasi |
|-------|--------------|
| Authentication | Session-based dengan Laravel Auth |
| Authorization | Role-based access control (RBAC) berbasis jabatan |
| Audit Trail | Semua transaksi mencatat user, tanggal, dan perubahan data |
| Data Validation | Validasi di level controller dan business logic di service |
| Transaction Safety | Operasi multi-step dibungkus dalam database transaction |

---

## Performance Optimization

| Strategi | Implementasi |
|----------|--------------|
| Query Optimization | Semua query dioptimasi di repository layer |
| Batch Loading | Menghindari N+1 problem dengan eager loading |
| Indexing | Index database untuk kolom yang sering di-query |
| Caching | Cache untuk data master yang jarang berubah |

---

## Status Pengembangan

Sistem ini sedang dalam proses refactoring dari arsitektur monolitik (God Controller) ke arsitektur 3-layer yang lebih terstruktur. Refactoring dilakukan secara bertahap dengan menjaga 100% backward compatibility dengan blade template dan JavaScript yang sudah ada.

**Fokus Perbaikan:**

- Separation of concerns yang lebih jelas
- Query optimization untuk performance
- Code maintainability dan testability
- Developer experience yang lebih baik

---

## Lisensi

Proprietary - All rights reserved

---

## Kontak

Untuk pertanyaan atau issue, silakan hubungi tim development.

---

*Dokumentasi ini diperbarui secara berkala sesuai perkembangan sistem.*