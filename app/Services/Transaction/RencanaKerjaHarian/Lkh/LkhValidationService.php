<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Lkh;

use App\Repositories\Transaction\RencanaKerjaHarian\LkhRepository;
use Illuminate\Validation\ValidationException;

/**
 * LkhValidationService
 * 
 * Handles LKH validation business rules.
 * RULE: Can use repos for validation queries, no writes.
 */
class LkhValidationService
{
    protected $lkhRepo;

    public function __construct(LkhRepository $lkhRepo)
    {
        $this->lkhRepo = $lkhRepo;
    }

    /**
     * Validate LKH update request
     * Throws ValidationException on error
     * 
     * @param \Illuminate\Http\Request $request
     * @return void
     * @throws ValidationException
     */
    public function validateLkhUpdateRequest($request)
    {
        $request->validate([
            'keterangan' => 'nullable|string|max:500',
            
            'plots' => 'nullable|array',
            'plots.*.blok' => 'required_with:plots|string',
            'plots.*.plot' => 'required_with:plots|string',
            'plots.*.luasrkh' => 'required_with:plots|numeric|min:0',
            'plots.*.luashasil' => 'required_with:plots|numeric|min:0',
            'plots.*.luassisa' => 'required_with:plots|numeric|min:0',
            
            'workers' => 'nullable|array',
            'workers.*.tenagakerjaid' => 'required_with:workers|string',
            'workers.*.jammasuk' => 'nullable|date_format:H:i:s', // FIXED: Changed from H:i to H:i:s
            'workers.*.jamselesai' => 'nullable|date_format:H:i:s', // FIXED: Changed from H:i to H:i:s
            'workers.*.totaljamkerja' => 'nullable|numeric|min:0',
            'workers.*.overtimehours' => 'nullable|numeric|min:0',
            'workers.*.premi' => 'nullable|numeric|min:0',
            'workers.*.upahharian' => 'nullable|numeric|min:0',
            'workers.*.upahborongan' => 'nullable|numeric|min:0',
            'workers.*.totalupah' => 'nullable|numeric|min:0',
            
            'materials' => 'nullable|array',
            'materials.*.itemcode' => 'required_with:materials|string',
            'materials.*.qtyditerima' => 'required_with:materials|numeric|min:0',
            'materials.*.qtysisa' => 'required_with:materials|numeric|min:0',
        ]);
    }

    /**
     * Validate LKH can be submitted
     * 
     * @param string $lkhno
     * @param string $companycode
     * @return array ['success' => bool, 'message' => string]
     */
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