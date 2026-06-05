<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // Wajib di-import

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── TUGAS OTOMATISASI RETREIVAL STOK (PROSES PB) AMANDAMART ──
// Memerintahkan sistem untuk melakukan pengecekan stok kritis secara mandiri setiap jam 00.00 malam
Schedule::command('amandamart:auto-restock')->daily();