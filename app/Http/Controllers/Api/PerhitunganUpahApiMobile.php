<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerhitunganUpahApiMobile extends Controller
{
    public function insertWorkerWage(Request $request)
    {
        try {
            $validated = $request->validate([
                'companycode' => 'required|string|max:4',
                'lkhno' => 'required|string',
                'tenagakerjaid' => 'required|string',
                'tenagakerjaurutan' => 'required|integer',
                'activitycode' => 'required|string',
                'lkhdate' => 'required|date',
                'jammulai' => 'required|date_format:H:i:s',
                'jamselesai' => 'required|date_format:H:i:s',
                'overtimehours' => 'nullable|numeric|min:0',
                'luashasil' => 'nullable|numeric|min:0',
                'keterangan' => 'nullable|string|max:255',
            ]);
            
            $activity = DB::table('activity')
                ->where('activitycode', $validated['activitycode'])
                ->first(['jenistenagakerja', 'activitygroup']);
            
            if (!$activity) {
                return response()->json([
                    'status' => 0,
                    'description' => "Activity code '{$validated['activitycode']}' tidak ditemukan"
                ], 404);
            }
            
            if (!$activity->jenistenagakerja) {
                return response()->json([
                    'status' => 0,
                    'description' => "Jenis tenaga kerja untuk activity '{$validated['activitycode']}' belum diset"
                ], 400);
            }
            
            $jenistenagakerja = $activity->jenistenagakerja;
            
            if (in_array($jenistenagakerja, [2, 5])) {
                return response()->json([
                    'status' => 0,
                    'description' => 'Jenis tenaga kerja ini tidak menggunakan API upah per pekerja'
                ], 400);
            }
            
            // Jenis 3: bypass, return success tanpa insert
            if ($jenistenagakerja == 3) {
                return response()->json([
                    'status' => 1,
                    'description' => 'Jenis tenaga kerja 3 - tidak memerlukan perhitungan upah',
                    'data' => [
                        'total_upah' => 0,
                        'jam_kerja' => 0,
                        'breakdown' => [
                            'upah_harian' => 0,
                            'upah_perjam' => 0,
                            'upah_lembur' => 0,
                            'premi' => 0
                        ]
                    ]
                ], 200);
            }
            
            if ($jenistenagakerja == 4) {
                $jenistenagakerja = 1;
            }
            
            $totalJamKerja = $this->calculateWorkHours($validated['jammulai'], $validated['jamselesai']);
            
            $wageData = $this->calculateWage($validated, $totalJamKerja, $activity->activitygroup);
            
            if (isset($wageData['error'])) {
                return response()->json([
                    'status' => 0,
                    'description' => $wageData['error']
                ], 404);
            }
            
            $existingRecord = DB::table('lkhdetailworker')
                ->where('companycode', $validated['companycode'])
                ->where('lkhno', $validated['lkhno'])
                ->where('tenagakerjaid', $validated['tenagakerjaid'])
                ->first();

            if (!$existingRecord) {
                return response()->json([
                    'status' => 0,
                    'description' => 'Error, Please ensure the worker is assigned to this LKH first'
                ], 404);
            }

            $updated = DB::table('lkhdetailworker')
                ->where('companycode', $validated['companycode'])
                ->where('lkhno', $validated['lkhno'])
                ->where('tenagakerjaid', $validated['tenagakerjaid'])
                ->update([
                    'tenagakerjaurutan' => $validated['tenagakerjaurutan'],
                    'jammasuk' => $validated['jammulai'],
                    'jamselesai' => $validated['jamselesai'],
                    'totaljamkerja' => $totalJamKerja,
                    'overtimehours' => $validated['overtimehours'] ?? 0,
                    'premi' => $wageData['premi'],
                    'upahharian' => $wageData['upahharian'],
                    'upahperjam' => $wageData['upahperjam'],
                    'upahlembur' => $wageData['upahlembur'],
                    'upahborongan' => $wageData['upahborongan'],
                    'totalupah' => $wageData['totalupah'],
                    'keterangan' => $validated['keterangan'] ?? 'Mobile upload',
                    'updatedat' => now()
                ]);

            if ($updated) {
                $this->syncLkhHeaderTotals($validated['companycode'], $validated['lkhno']);
                
                return response()->json([
                    'status' => 1,
                    'description' => 'Worker wage updated successfully',
                    'data' => [
                        'total_upah' => $wageData['totalupah'],
                        'jam_kerja' => $totalJamKerja,
                        'breakdown' => [
                            'upah_harian' => $wageData['upahharian'],
                            'upah_perjam' => $wageData['upahperjam'],
                            'upah_lembur' => $wageData['upahlembur'],
                            'premi' => $wageData['premi']
                        ]
                    ]
                ], 200);
            } else {
                return response()->json([
                    'status' => 0,
                    'description' => 'Failed to update worker wage'
                ], 400);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 0,
                'description' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('Error in insertWorkerWage API', [
                'description' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'status' => 0,
                'description' => 'Update failed: ' . (config('app.debug') ? $e->getMessage() : 'Internal server error')
            ], 500);
        }
    }

    public function insertUpahBorongan(Request $request)
    {
        try {
            $validated = $request->validate([
                'companycode' => 'required|string|max:4',
                'lkhno' => 'required|string',
            ]);
            
            $lkh = DB::table('lkhhdr')
                ->where('companycode', $validated['companycode'])
                ->where('lkhno', $validated['lkhno'])
                ->first();
            
            if (!$lkh) {
                return response()->json([
                    'status' => 0,
                    'description' => 'LKH tidak ditemukan'
                ], 404);
            }
            
            if ($lkh->jenistenagakerja != 2) {
                return response()->json([
                    'status' => 0,
                    'description' => 'LKH ini bukan jenis borongan'
                ], 400);
            }
            
            $totalArea = DB::table('lkhdetailplot')
                ->where('companycode', $validated['companycode'])
                ->where('lkhno', $validated['lkhno'])
                ->sum('luashasil');
            
            if ($totalArea <= 0) {
                return response()->json([
                    'status' => 0,
                    'description' => 'Tidak ada data plot atau luas hasil = 0'
                ], 400);
            }
            
            $rate = DB::table('upahborongan')
                ->where('companycode', $validated['companycode'])
                ->where('activitycode', $lkh->activitycode)
                ->where('effectivedate', '<=', $lkh->lkhdate)
                ->where(function($q) use ($lkh) {
                    $q->whereNull('enddate')
                      ->orWhere('enddate', '>=', $lkh->lkhdate);
                })
                ->orderBy('effectivedate', 'DESC')
                ->value('amount');
            
            if (!$rate) {
                return response()->json([
                    'status' => 0,
                    'description' => 'Tidak ditemukan upah borongan aktif untuk Company: ' . $validated['companycode'] . ', Activity: ' . $lkh->activitycode . ', Tanggal: ' . \Carbon\Carbon::parse($lkh->lkhdate)->format('d/m/Y')
                ], 404);
            }
            
            $totalUpah = round($totalArea * $rate, 2);
            
            $totalWorkers = DB::table('lkhdetailworker')
                ->where('companycode', $validated['companycode'])
                ->where('lkhno', $validated['lkhno'])
                ->count();
            
            DB::table('lkhhdr')
                ->where('companycode', $validated['companycode'])
                ->where('lkhno', $validated['lkhno'])
                ->update([
                    'totalupahall' => $totalUpah,
                    'totalhasil' => round($totalArea, 2),
                    'totalworkers' => $totalWorkers,
                    'updatedat' => now()
                ]);
            
            return response()->json([
                'status' => 1,
                'description' => 'Total upah borongan berhasil dihitung',
                'data' => [
                    'lkhno' => $validated['lkhno'],
                    'total_area' => round($totalArea, 2),
                    'rate_per_ha' => round($rate, 2),
                    'total_upah' => $totalUpah,
                    'total_workers' => $totalWorkers
                ]
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 0,
                'description' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('Error in insertUpahBorongan API', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'status' => 0,
                'description' => 'Calculation failed: ' . (config('app.debug') ? $e->getMessage() : 'Internal server error')
            ], 500);
        }
    }

    private function syncLkhHeaderTotals($companycode, $lkhno)
    {
        try {
            $workerTotals = DB::table('lkhdetailworker')
                ->where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->selectRaw('COUNT(*) as total_workers, SUM(totalupah) as total_upah')
                ->first();

            $plotTotals = DB::table('lkhdetailplot')
                ->where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->selectRaw('SUM(luashasil) as total_hasil, SUM(luassisa) as total_sisa')
                ->first();
            
            DB::table('lkhhdr')
                ->where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->update([
                    'totalworkers' => $workerTotals->total_workers ?? 0,
                    'totalupahall' => round($workerTotals->total_upah ?? 0, 2),
                    'totalhasil' => round($plotTotals->total_hasil ?? 0, 2),
                    'totalsisa' => round($plotTotals->total_sisa ?? 0, 2),
                    'updatedat' => now()
                ]);

        } catch (\Exception $e) {
            \Log::error('Error syncing LKH header totals', [
                'companycode' => $companycode,
                'lkhno' => $lkhno,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function calculateWage($data, $totalJamKerja, $activityGroup)
    {
        $dayType = $this->getDayType($data['lkhdate']);
        
        $wageData = [
            'premi' => 0,
            'upahharian' => 0,
            'upahperjam' => 0,
            'upahlembur' => 0,
            'upahborongan' => 0,
            'totalupah' => 0
        ];
        
        if ($totalJamKerja >= 7) {
            $dailyRate = $this->getHarianRate($data['companycode'], $activityGroup, $dayType, $data['lkhdate']);
            
            if ($dailyRate === null) {
                return [
                    'error' => "Data upah untuk Activity Group '{$activityGroup}' dengan tipe '{$dayType}' pada Company '{$data['companycode']}' belum tersedia. Silakan tambahkan data upah melalui Website terlebih dahulu."
                ];
            }
            
            $wageData['upahharian'] = round($dailyRate, 2);
        } else {
            $hourlyRate = $this->getHarianRate($data['companycode'], $activityGroup, 'HOURLY', $data['lkhdate']);
            
            if ($hourlyRate === null) {
                return [
                    'error' => "Data upah HOURLY untuk Activity Group '{$activityGroup}' pada Company '{$data['companycode']}' belum tersedia. Silakan tambahkan data upah melalui Website terlebih dahulu."
                ];
            }
            
            $wageData['upahperjam'] = round($hourlyRate, 2);
            $wageData['upahharian'] = round($totalJamKerja * $wageData['upahperjam'], 2);
        }
        
        $overtimeHours = $data['overtimehours'] ?? 0;
        if ($overtimeHours > 0) {
            $overtimeRate = $this->getHarianRate($data['companycode'], $activityGroup, 'OVERTIME', $data['lkhdate']);
            
            if ($overtimeRate === null) {
                return [
                    'error' => "Data upah OVERTIME untuk Activity Group '{$activityGroup}' pada Company '{$data['companycode']}' belum tersedia. Silakan tambahkan data upah melalui Website terlebih dahulu."
                ];
            }
            
            $wageData['upahlembur'] = round($overtimeHours * $overtimeRate, 2);
        }
        
        $wageData['totalupah'] = round($wageData['upahharian'] + $wageData['upahlembur'], 2);
        
        return $wageData;
    }

    private function getHarianRate($companycode, $activitygroup, $wagetype, $workDate)
    {
        $workDate = Carbon::parse($workDate)->format('Y-m-d');
        
        $rate = DB::table('upah')
            ->where('companycode', $companycode)
            ->where('activitygroup', $activitygroup)
            ->where('wagetype', $wagetype)
            ->where('effectivedate', '<=', $workDate)
            ->where(function ($q) use ($workDate) {
                $q->whereNull('enddate')
                    ->orWhere('enddate', '>=', $workDate);
            })
            ->orderBy('effectivedate', 'DESC')
            ->value('amount');
        
        return $rate;
    }
    
    private function calculateWorkHours($jamMasuk, $jamSelesai)
    {
        $start = Carbon::createFromFormat('H:i:s', $jamMasuk);
        $end = Carbon::createFromFormat('H:i:s', $jamSelesai);
        
        if ($end->lt($start)) {
            $end->addDay();
        }
        
        $totalHours = $start->diffInHours($end, false);
        
        $breakStart = Carbon::createFromFormat('H:i:s', '12:00:00');
        $breakEnd = Carbon::createFromFormat('H:i:s', '13:00:00');
        
        $breakDeduction = 0;
        
        if ($start->lt($breakEnd) && $end->gt($breakStart)) {
            $overlapStart = $start->gt($breakStart) ? $start : $breakStart;
            $overlapEnd = $end->lt($breakEnd) ? $end : $breakEnd;
            $breakDeduction = $overlapStart->diffInHours($overlapEnd, false);
        }
        
        return $totalHours - $breakDeduction;
    }
    
    private function getDayType($workDate)
    {
        $dayOfWeek = Carbon::parse($workDate)->dayOfWeek;
        
        if ($dayOfWeek === Carbon::SATURDAY) {
            return 'WEEKEND_SATURDAY';
        } elseif ($dayOfWeek === Carbon::SUNDAY) {
            return 'WEEKEND_SUNDAY';
        }
        
        return 'DAILY';
    }
    
    private function getActivityGroupFromCode($activitycode)
    {
        $activitygroup = DB::table('activity')
            ->where('activitycode', $activitycode)
            ->value('activitygroup');
        
        if ($activitygroup) {
            return $activitygroup;
        }
        
        if (preg_match('/^([IVX]+)/', $activitycode, $matches)) {
            return $matches[1];
        }
        
        return 'V';
    }
}