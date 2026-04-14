<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Lkh;

use App\Repositories\Transaction\RencanaKerjaHarian\LkhRepository;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class LkhValidationService
{
    protected $lkhRepo;

    public function __construct(LkhRepository $lkhRepo)
    {
        $this->lkhRepo = $lkhRepo;
    }

    public function validateLkhUpdateRequest($request)
    {
        $isBlokActivity = (bool) $request->input('is_blok_activity', false);

        // Base rules
        $rules = [
            'keterangan' => 'nullable|string|max:500',
            'is_blok_activity' => 'nullable|boolean',

            'workers' => 'nullable|array',
            'workers.*.tenagakerjaid' => 'required_with:workers|string',
            'workers.*.jammasuk' => 'nullable|date_format:H:i:s',
            'workers.*.jamselesai' => 'nullable|date_format:H:i:s',
            'workers.*.totaljamkerja' => 'nullable|numeric|min:0',
            'workers.*.overtimehours' => 'nullable|numeric|min:0',
            'workers.*.premi' => 'nullable|numeric|min:0',
            'workers.*.upahharian' => 'nullable|numeric|min:0',
            'workers.*.upahborongan' => 'nullable|numeric|min:0',
            'workers.*.totalupah' => 'nullable|numeric|min:0',

            'materials' => 'nullable|array',
            'materials.*.id' => 'required_with:materials|integer',
            'materials.*.plot' => 'required_with:materials|string',
            'materials.*.itemcode' => 'required_with:materials|string',
            'materials.*.qtyditerima' => 'required_with:materials|numeric|min:0',
            'materials.*.qtydigunakan' => 'required_with:materials|numeric|min:0',
        ];

        if ($isBlokActivity) {
            // Blok activity: plot is null, luas fields are null — only blok is required
            $rules = array_merge($rules, [
                'plots' => 'nullable|array|min:1',
                'plots.*.blok' => 'required_with:plots|string',
                'plots.*.plot' => 'nullable|string',
                'plots.*.luasrkh' => 'nullable|numeric|min:0',
                'plots.*.luashasil' => 'nullable|numeric|min:0',
                'plots.*.luassisa' => 'nullable|numeric|min:0',
                'plots.*.keterangan' => 'nullable|string|max:500',
            ]);
        } else {
            // Normal activity: plot and luas fields are required
            $rules = array_merge($rules, [
                'plots' => 'nullable|array',
                'plots.*.blok' => 'required_with:plots|string',
                'plots.*.plot' => 'required_with:plots|string',
                'plots.*.luasrkh' => 'required_with:plots|numeric|min:0',
                'plots.*.luashasil' => 'required_with:plots|numeric|min:0',
                'plots.*.luassisa' => 'required_with:plots|numeric|min:0',
            ]);
        }

        $request->validate($rules);

        // Custom validation: qtydigunakan cannot exceed qtyditerima
        if ($request->has('materials')) {
            foreach ($request->materials as $index => $material) {
                $qtydigunakan = (float)($material['qtydigunakan'] ?? 0);
                $qtyditerima = (float)($material['qtyditerima'] ?? 0);

                if ($qtydigunakan > $qtyditerima) {
                    throw ValidationException::withMessages([
                        "materials.{$index}.qtydigunakan" => "Qty Used ({$qtydigunakan}) cannot exceed Qty Received ({$qtyditerima}) for item {$material['itemcode']}"
                    ]);
                }
            }
        }
    }

    public function validateCanSubmit($lkhno, $companycode)
    {
        $lkh = $this->lkhRepo->getForValidation($companycode, $lkhno);

        if (!$lkh) {
            return ['success' => false, 'message' => 'LKH tidak ditemukan'];
        }

        if ($lkh->issubmit) {
            return ['success' => false, 'message' => 'LKH sudah disubmit sebelumnya'];
        }

        if ($lkh->status !== 'DRAFT') {
            return ['success' => false, 'message' => 'LKH harus berstatus DRAFT untuk bisa disubmit'];
        }

        return ['success' => true];
    }
}