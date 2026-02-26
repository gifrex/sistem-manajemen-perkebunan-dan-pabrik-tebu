<?php

/**
 * Navigation Menu Configuration
 */

return [

    // ============================================
    // MASTER DATA
    // ============================================
    [
        'name' => 'Master Data',
        'icon' => 'database',
        'permission' => 'masterdata.menu.view',
        'children' => [
            [
                'name' => 'Company',
                'route' => 'masterdata.company.index',
                'permission' => 'masterdata.company.view',
            ],
            [
                'name' => 'Manajemen Lahan',
                'children' => [
                    [
                        'name' => 'Master List',
                        'route' => 'masterdata.master-list.index',
                        'permission' => 'masterdata.masterlist.view',
                    ],
                    [
                        'name' => 'Blok',
                        'route' => 'masterdata.blok.index',
                        'permission' => 'masterdata.blok.view',
                    ],
                    [
                        'name' => 'Batch',
                        'route' => 'masterdata.batch.index',
                        'permission' => 'masterdata.batch.view',
                    ],
                    [
                        'name' => 'Rekonstruksi Plot',
                        'route' => 'masterdata.split-merge-plot.index',
                        'permission' => 'masterdata.splitmergeplot.view',
                    ],
                    [
                        'name' => 'Buka Kerja Ulang',
                        'route' => 'masterdata.open-rework.index',
                        'permission' => 'masterdata.open-rework.view',
                    ],
                ],
            ],
            [
                'name' => 'Data Agronomi',
                'children' => [
                    [
                        'name' => 'Herbisida',
                        'route' => 'masterdata.herbisida.index',
                        'permission' => 'masterdata.herbisida.view',
                    ],
                    [
                        'name' => 'Herbisida Group',
                        'route' => 'masterdata.herbisida-group.index',
                        'permission' => 'masterdata.herbisidagroup.view',
                    ],
                    [
                        'name' => 'Dosis Herbisida',
                        'route' => 'masterdata.herbisida-dosage.index',
                        'permission' => 'masterdata.herbisidadosage.view',
                    ],
                    [
                        'name' => 'Varietas',
                        'route' => 'masterdata.varietas.index',
                        'permission' => 'masterdata.varietas.view',
                    ],
                ],
            ],
            [
                'name' => 'Manajemen Personel & Aset',
                'children' => [
                    [
                        'name' => 'Mandor',
                        'route' => 'masterdata.mandor.index',
                        'permission' => 'masterdata.mandor.view',
                    ],
                    [
                        'name' => 'Tenaga Kerja',
                        'route' => 'masterdata.tenagakerja.index',
                        'permission' => 'masterdata.tenagakerja.view',
                    ],
                    [
                        'name' => 'Kendaraan',
                        'route' => 'masterdata.kendaraan.index',
                        'permission' => 'masterdata.kendaraan.view',
                    ],
                    [
                        'name' => 'Kontraktor',
                        'route' => 'masterdata.kontraktor.index',
                        'permission' => 'masterdata.kontraktor.view',
                    ],
                    [
                        'name' => 'Subkontraktor',
                        'route' => 'masterdata.subkontraktor.index',
                        'permission' => 'masterdata.subkontraktor.view',
                    ],
                ],
            ],
            [
                'name' => 'Approval',
                'route' => 'masterdata.approval.index',
                'permission' => 'masterdata.approval.view',
            ],
            [
                'name' => 'Aktivitas',
                'route' => 'masterdata.aktivitas.index',
                'permission' => 'masterdata.aktivitas.view',
            ],
            [
                'name' => 'Upah',
                'children' => [
                    [
                        'name' => 'Upah Harian',
                        'route' => 'masterdata.upah.index',
                        'permission' => 'masterdata.upah.view',
                    ],
                    [
                        'name' => 'Upah Borongan',
                        'route' => 'masterdata.upah-borongan.index',
                        'permission' => 'masterdata.upah.view',
                    ],
                ]
            ],
            [
                'name' => 'Accounting',
                'route' => 'masterdata.accounting.index',
                'permission' => 'masterdata.accounting.view',
            ],
        ],
    ],

    // ============================================
    // TRANSACTION (INPUT)
    // ============================================
    [
        'name' => 'Transaction',
        'icon' => 'edit',
        'permission' => 'transaction.menu.view',
        'children' => [
            [
                'name' => 'Rencana Kerja Harian',
                'route' => 'transaction.rencanakerjaharian.index',
                'permission' => 'transaction.rencanakerjaharian.view',
            ],
            [
                'name' => 'Rencana Kerja Mingguan',
                'route' => 'transaction.rencana-kerja-mingguan.index',
                'permission' => 'transaction.rencanakerjamingguan.view',
            ],
            [
                'name' => 'Agronomi',
                'route' => 'transaction.agronomi.index',
                'permission' => 'transaction.agronomi.view',
            ],
            [
                'name' => 'HPT',
                'route' => 'transaction.hpt.index',
                'permission' => 'transaction.hpt.view',
            ],
            [
                'name' => 'Gudang',
                'route' => 'transaction.gudang.index',
                'permission' => 'transaction.gudang.view',
            ],
            [
                'name' => 'Gudang BBM',
                'route' => 'transaction.gudang-bbm.index',
                'permission' => 'transaction.gudangbbm.view',
            ],
            [
                'name' => 'Kendaraan Workshop',
                'route' => 'transaction.kendaraan-workshop.index',
                'permission' => 'transaction.kendaraanworkshop.view',
            ],
            [
                'name' => 'Tebar Pias',
                'route' => 'transaction.pias.index',
                'permission' => 'transaction.pias.view',
            ],
            [
                'name' => 'NFC',
                'route' => 'transaction.nfc.index',
                'permission' => 'transaction.nfc.view',
            ],
            [
                'name' => 'Mapping BSM',
                'route' => 'transaction.mapping-bsm.index',
                'permission' => 'transaction.mappingbsm.view',
            ],
            [
                'name' => 'Koreksi',
                'route' => 'transaction.gudang.koreksi',
                'permission' => 'transaction.gudang.view',
            ],
        ],
    ],

    // ============================================
    // REPORT
    // ============================================
    [
        'name' => 'Report',
        'icon' => 'report',
        'permission' => 'report.menu.view',
        'children' => [
            [
                'name' => 'Absen',
                'route' => 'report.absen.index',
                'permission' => 'report.absen.view',
            ],
            [
                'name' => 'Agronomi',
                'route' => 'report.agronomi.index',
                'permission' => 'report.agronomi.view',
            ],
            [
                'name' => 'HPT',
                'route' => 'report.hpt.index',
                'permission' => 'report.hpt.view',
            ],
            [
                'name' => 'ZPK',
                'route' => 'report.report-zpk.index',
                'permission' => 'report.zpk.view',
            ],
            [
                'name' => 'Manajemen Lahan',
                'route' => 'report.report-manajemen-lahan.index',
                'permission' => 'report.manajemenlahan.view',
            ],
            [
                'name' => 'Berita Acara Panen Tebu Giling',
                'route' => 'report.panen-tebu-report.index',
                'permission' => 'report.panentebu.view',
            ],
            [
                'name' => 'Surat Jalan',
                'route' => 'report.report-surat-jalan.index',
                'permission' => 'report.suratjalan.view',
            ],
            [
                'name' => 'Surat Jalan & Timbangan',
                'route' => 'report.report-surat-jalan-timbangan.index',
                'permission' => 'report.suratjalantimbangan.view',
            ],
            [
                'name' => 'Panen Track Plot',
                'route' => 'report.panen-track-plot.index',
                'permission' => 'report.panentrackplot.view',
            ],
            [
                'name' => 'Rekap Upah Mingguan',
                'route' => 'report.rekap-upah-mingguan.index',
                'permission' => 'report.rekapupahminggu.view',
            ],
            [
                'name' => 'Trash',
                'route' => 'report.trash-report.index',
                'permission' => 'report.trash.view',
            ],
            [
                'name' => 'Track Waktu Pias',
                'route' => 'report.track-pias.index',
                'permission' => 'report.track-pias.view',
            ],
            [
                'name' => 'Biaya Per Plot',
                'route' => 'report.biaya-per-plot.index',
                'permission' => 'report.biayaperplot.view',
            ],
        ],
    ],

    // ============================================
    // DASHBOARD
    // ============================================
    [
        'name' => 'Dashboard',
        'icon' => 'dashboard',
        'permission' => 'dashboard.menu.view',
        'children' => [
            [
                'name' => 'Panen',
                'route' => 'dashboard.panen',
                'permission' => 'dashboard.panen.view',
            ],
            [
                'name' => 'Agronomi',
                'route' => 'dashboard.agronomi',
                'permission' => 'dashboard.agronomi.view',
            ],
            [
                'name' => 'HPT',
                'route' => 'dashboard.hpt',
                'permission' => 'dashboard.hpt.view',
            ],
            [
                'name' => 'Timeline',
                'route' => 'dashboard.timeline',
                'permission' => 'dashboard.timeline.view',
            ],
            [
                'name' => 'Timeline Plot',
                'route' => 'dashboard.timeline-plot',
                'permission' => 'dashboard.timelineplot.view',
            ],
            [
                'name' => 'Maps',
                'route' => 'dashboard.maps',
                'permission' => 'dashboard.maps.view',
            ],
        ],
    ],

    // ============================================
    // PROCESS
    // ============================================
    [
        'name' => 'Process',
        'icon' => 'settings',
        'permission' => 'process.menu.view',
        'children' => [
            [
                'name' => 'Posting',
                'route' => 'process.posting',
                'permission' => 'process.posting.view',
            ],
            [
                'name' => 'Unposting',
                'route' => 'process.unposting',
                'permission' => 'process.unposting.view',
            ],
            [
                'name' => 'Upload GPX File',
                'route' => 'process.uploadgpx',
                'permission' => 'process.uploadgpx.view',
            ],
            [
                'name' => 'Export KML File',
                'route' => 'process.exportkml',
                'permission' => 'process.exportkml.view',
            ],
            // [
            //     'name' => 'Closing',
            //     'route' => 'process.closing',
            //     'permission' => 'process.closing.view',
            // ],
        ],
    ],

    // ============================================
    // PABRIK
    // ============================================
    [
        'name' => 'Pabrik',
        'icon' => 'building',
        'permission' => 'pabrik.menu.view',
        'children' => [
            [
                'name' => 'Trash',
                'route' => 'pabrik.trash.index',
                'permission' => 'pabrik.trash.view',
            ],
            [
                'name' => 'Dashboard Panen Pabrik',
                'route' => 'pabrik.panen-pabrik.index',
                'permission' => 'pabrik.panenpabrik.view',
            ],
        ],
    ],

    // ============================================
    // USER MANAGEMENT
    // ============================================
    [
        'name' => 'User Management',
        'icon' => 'users',
        'permission' => 'usermanagement.menu.view',
        'children' => [
            [
                'name' => 'User',
                'route' => 'usermanagement.user.index',
                'permission' => 'usermanagement.user.view',
            ],
            [
                'name' => 'User Company Access',
                'route' => 'usermanagement.user-company.index',
                'permission' => 'usermanagement.user-company.view',
            ],
            [
                'name' => 'User Permissions',
                'route' => 'usermanagement.user-permission.index',
                'permission' => 'usermanagement.user-permission.view',
            ],
            [
                'name' => 'User Activity Permission',
                'route' => 'usermanagement.user-activity.index',
                'permission' => 'usermanagement.user-activity.view',
            ],
            [
                'name' => 'Master Permissions',
                'route' => 'usermanagement.permission.index',
                'permission' => 'usermanagement.permission.view',
            ],
            [
                'name' => 'Jabatan Management',
                'route' => 'usermanagement.jabatan.index',
                'permission' => 'usermanagement.jabatan.view',
            ],
            [
                'name' => 'Support Ticket',
                'route' => 'usermanagement.support-ticket.index',
                'permission' => 'usermanagement.support-ticket.view',
            ],
        ],
    ],

    // ============================================
    // IT SUPPORT
    // ============================================
    [
        'name' => 'IT Support',
        'icon' => 'wrench',
        'permission' => 'it-support.menu.view',
        'children' => [
            [
                'name' => 'Delete RKH',
                'route' => 'it-support.delete-rkh.index',
                'permission' => 'it-support.delete-rkh.view',
            ],
            [
                'name' => 'Ganti Tanggal RKH',
                'route' => 'it-support.change-date-rkh.index',
                'permission' => 'it-support.change-date-rkh.view',
            ],
        ],
    ],

    // ============================================
    // INFO & UPDATES
    // ============================================
    [
        'name' => 'Info & Updates',
        'icon' => 'megaphone',
        'permission' => 'infoupdates.menu.view',
        'children' => [
            [
                'name' => 'Notifications',
                'route' => 'info-updates.notifications.admin.index',
                'permission' => 'infoupdates.notification.view',
            ],
            [
                'name' => 'Announcements',
                'route' => 'info-updates.announcements.admin.index',
                'permission' => 'infoupdates.announcement.view',
            ],
        ],
    ],

    // ============================================
    // DEVELOPER
    // ============================================
    [
        'name' => 'Developer',
        'icon' => 'code',
        'permission' => 'developer.menu.view',
        'children' => [
            [
                'name' => 'Log Viewer',
                'route' => 'log-viewer.index',
                'permission' => 'developer.logviewer.view',
            ],
        ],
    ],
    [
        'name' => 'Settings',
        'icon' => 'cog',
        'permission' => 'settings.menu.view',
        'children' => [
            [
                'name' => 'Tabel Harga Panen',
                'route' => 'settings.harga-panen.index',
                'permission' => 'settings.harga-panen.index',
            ],
           [
                'name' => 'Target Kontraktor',
                'route' => 'settings.target-kontraktor.index',
                'permission' => 'settings.target-kontraktor.index',
            ],
        ],
    ]
];