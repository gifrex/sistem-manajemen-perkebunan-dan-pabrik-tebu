// Global variables for managing edits and copy functionality
let editedRows = new Map();
let currentRkhno = '';
let currentCopyTarget = null;

$(document).ready(function() {
    // Initialize date inputs
    initializeDateInputs();

    // Initialize DataTable if data exists
    if (window.hasData) {
        initializeDataTable();
    }

    // Export buttons handlers
    $('#export-excel-btn').on('click', function() {
        if ($.fn.DataTable.isDataTable('#mapping-bsm-table')) {
            $('#mapping-bsm-table').DataTable().button('.buttons-excel').trigger();
        }
    });

    $('#print-btn').on('click', function() {
        if ($.fn.DataTable.isDataTable('#mapping-bsm-table')) {
            $('#mapping-bsm-table').DataTable().button('.buttons-print').trigger();
        }
    });
});

function initializeDateInputs() {
    const today = new Date().toISOString().split('T')[0];
    const tanggalAwal = document.getElementById('tanggalawal');
    const tanggalAkhir = document.getElementById('tanggalakhir');

    tanggalAwal.setAttribute('max', today);
    tanggalAkhir.setAttribute('max', today);

    function validateDateRange() {
        const startDate = new Date(tanggalAwal.value);
        const endDate = new Date(tanggalAkhir.value);

        if (startDate && endDate && startDate > endDate) {
            tanggalAkhir.setCustomValidity('Tanggal akhir harus setelah tanggal awal');
        } else {
            tanggalAkhir.setCustomValidity('');
        }
    }

    tanggalAwal.addEventListener('change', validateDateRange);
    tanggalAkhir.addEventListener('change', validateDateRange);
}

function initializeDataTable() {
    $('#mapping-bsm-table').DataTable({
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: 'Export Excel',
                title: window.exportTitle || 'Ringkasan RKH',
                className: 'hidden'
            },
            {
                extend: 'print',
                text: 'Print',
                title: 'Ringkasan Data RKH',
                messageTop: window.exportMessageTop || '',
                className: 'hidden'
            }
        ],
        columnDefs: [
            {
                targets: [0, 3, 4, 5],
                className: 'text-center'
            },
            {
                targets: [5],
                orderable: false
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        },
        drawCallback: function() {
            $('.dataTables_wrapper .dataTables_paginate .paginate_button').addClass('px-3 py-2 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700');
        }
    });
}

function showDetailModal(rkhno) {
    currentRkhno = rkhno;
    editedRows.clear();
    
    document.getElementById('detail_modal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    document.getElementById('modal_detail_subtitle').textContent = `RKH No: ${rkhno}`;
    document.getElementById('bulk-save-btn').classList.add('hidden');
    
    document.getElementById('modal_loading').classList.remove('hidden');
    document.getElementById('modal_detail_content').innerHTML = '';
    document.getElementById('modal_error').classList.add('hidden');
    
    fetch(`${window.getBsmDetailUrl}?rkhno=${encodeURIComponent(rkhno)}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.csrfToken
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        document.getElementById('modal_loading').classList.add('hidden');
        
        if (data.success && data.data && data.data.length > 0) {
            buildEditableTable(data.data, rkhno);
        } else {
            showNoDataMessage(rkhno);
        }
    })
    .catch(error => {
        document.getElementById('modal_loading').classList.add('hidden');
        document.getElementById('modal_error').classList.remove('hidden');
        document.getElementById('error_message').textContent = error.message || 'Terjadi kesalahan saat memuat data BSM';
        console.error('Error fetching BSM detail:', error);
    });
}

function buildEditableTable(data, rkhno) {
    const isBsmEmpty = item => (!item.nilaibersih || item.nilaibersih == 0) && (!item.nilaisegar || item.nilaisegar == 0) && (!item.nilaimanis || item.nilaimanis == 0);
    const hasEditableRows = data.some(item => isBsmEmpty(item));
    
    let tableHTML = `
        <div class="mb-4">
            <div class="flex justify-between items-center mb-3">
                <div class="text-sm text-gray-600">
                    Total: <span class="font-medium text-indigo-600">${data.length}</span> surat jalan ditemukan
                    ${hasEditableRows ? `<span class="ml-4 text-amber-600 font-medium">⚠️ ${data.filter(item => isBsmEmpty(item)).length} data dapat diedit</span>` : ''}
                </div>
                <div class="text-sm text-gray-500">
                    Data BSM untuk RKH: <span class="font-medium">${rkhno}</span>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <div>
                            <div class="text-xs font-medium text-blue-800">Mode 1: Input Manual</div>
                            <div class="text-xs text-blue-600">Edit nilai BSM secara langsung</div>
                        </div>
                    </div>
                </div>
                <div class="p-3 bg-purple-50 rounded-lg border border-purple-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <div class="text-xs font-medium text-purple-800">Mode 2: Copy Data</div>
                            <div class="text-xs text-purple-600">Copy dari plot yang sama</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-3 bg-blue-50 rounded-lg">
                <div class="text-xs text-blue-700">
                    <strong>Sistem Grading BSM:</strong> 
                    A (&lt; 1,200) • B (1,200 - 1,999) • C (≥ 2,000)
                </div>
            </div>
        </div>
        
        <div class="shadow ring-1 ring-black ring-opacity-5 rounded-lg">
            <table class="min-w-full divide-y divide-gray-300" id="detail-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Surat Jalan</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plot</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Bersih</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Segar</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Manis</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Average Score</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
    `;
    
    data.forEach((item, index) => {
        const isEditable = isBsmEmpty(item);
        const rowClass = isEditable ? 'bg-yellow-50 hover:bg-yellow-100' : 'hover:bg-gray-50';
        const hasParent = item.parentbsm && item.parentbsm > 0;
        
        tableHTML += `
            <tr class="${rowClass}" data-suratjalan="${item.suratjalanno}" data-plot="${item.plot}" data-id="${item.id || ''}">
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center">${index + 1}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                    <div class="flex items-center">
                        ${item.suratjalanno || '-'}
                        ${isEditable ? '<span class="ml-2 inline-block w-2 h-2 bg-amber-400 rounded-full" title="Dapat diedit"></span>' : ''}
                        ${hasParent ? '<span class="ml-2 inline-block w-2 h-2 bg-purple-400 rounded-full" title="Data hasil copy"></span>' : ''}
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                        ${item.plot || '-'}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                    ${isEditable ? 
                        `<input type="number" step="0.01" min="0" max="9999999" 
                                class="w-28 px-2 py-1 text-center border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm editable-input" 
                                data-field="nilaibersih" 
                                data-suratjalan="${item.suratjalanno}"
                                value="${parseFloat(item.nilaibersih || 0).toFixed(2)}"
                                onchange="trackEdit('${item.suratjalanno}', 'nilaibersih', this.value)">` 
                        : 
                        `<span class="text-gray-900">${formatNumber(item.nilaibersih || 0)}</span>`
                    }
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                    ${isEditable ? 
                        `<input type="number" step="0.01" min="0" max="9999999" 
                                class="w-28 px-2 py-1 text-center border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm editable-input" 
                                data-field="nilaisegar" 
                                data-suratjalan="${item.suratjalanno}"
                                value="${parseFloat(item.nilaisegar || 0).toFixed(2)}"
                                onchange="trackEdit('${item.suratjalanno}', 'nilaisegar', this.value)">` 
                        : 
                        `<span class="text-gray-900">${formatNumber(item.nilaisegar || 0)}</span>`
                    }
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                    ${isEditable ? 
                        `<input type="number" step="0.01" min="0" max="9999999" 
                                class="w-28 px-2 py-1 text-center border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm editable-input" 
                                data-field="nilaimanis" 
                                data-suratjalan="${item.suratjalanno}"
                                value="${parseFloat(item.nilaimanis || 0).toFixed(2)}"
                                onchange="trackEdit('${item.suratjalanno}', 'nilaimanis', this.value)">` 
                        : 
                        `<span class="text-gray-900">${formatNumber(item.nilaimanis || 0)}</span>`
                    }
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getBsmScoreColor(item.averagescore)}" id="avg-${item.suratjalanno}">
                        ${formatNumber(item.averagescore || 0)}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getBsmGradeColor(item.grade)}" id="grade-${item.suratjalanno}">
                        ${item.grade || '-'}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                    ${isEditable ? 
                        `<div class="flex flex-col space-y-1">
                            <button type="button" 
                                    onclick="saveRowChanges('${item.suratjalanno}')"
                                    class="hidden save-row-btn inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out"
                                    data-suratjalan="${item.suratjalanno}">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Simpan
                            </button>
                            <button type="button" 
                                    onclick="showCopyModal('${item.suratjalanno}', '${item.plot}')"
                                    class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-purple-700 bg-purple-100 hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition duration-150 ease-in-out">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                Copy
                            </button>
                        </div>` 
                        : 
                        `<span class="text-gray-400 text-xs">${hasParent ? 'Data Copy' : 'Tidak dapat diedit'}</span>`
                    }
                </td>
            </tr>
        `;
    });
    
    tableHTML += `
                </tbody>
            </table>
        </div>
    `;
    
    document.getElementById('modal_detail_content').innerHTML = tableHTML;
}

function showCopyModal(targetSuratjalanno, plot) {
    console.log('=== COPY MODAL DEBUG ===');
    console.log('Target:', targetSuratjalanno, 'Plot:', plot);
    
    currentCopyTarget = { suratjalanno: targetSuratjalanno, plot: plot };
    
    document.getElementById('copy_bsm_modal').classList.remove('hidden');
    document.getElementById('copy_modal_subtitle').textContent = `Copy ke: ${targetSuratjalanno} | Plot: ${plot}`;
    
    document.getElementById('copy_modal_loading').classList.remove('hidden');
    document.getElementById('copy_modal_content').innerHTML = '';
    document.getElementById('copy_modal_error').classList.add('hidden');
    
    console.log('Modal elements prepared, making API call...');
    
    // Pass target_suratjalanno to verify it's empty
    fetch(`${window.getBsmForCopyUrl}?rkhno=${encodeURIComponent(currentRkhno)}&plot=${encodeURIComponent(plot)}&target_suratjalanno=${encodeURIComponent(targetSuratjalanno)}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('API Response:', data);
        document.getElementById('copy_modal_loading').classList.add('hidden');
        
        if (data.success && data.data && data.data.length > 0) {
            console.log('Building dropdown interface with', data.data.length, 'items');
            buildCopyDropdownInterface(data.data);
        } else {
            console.log('No data or not successful, showing no data message');
            showNoCopyDataMessage(plot, data.message || 'Tidak ada data BSM untuk dicopy');
        }
    })
    .catch(error => {
        console.error('Error in showCopyModal:', error);
        document.getElementById('copy_modal_loading').classList.add('hidden');
        document.getElementById('copy_modal_error').classList.remove('hidden');
        document.getElementById('copy_error_message').textContent = error.message || 'Terjadi kesalahan saat memuat data untuk copy';
    });
}

function buildCopyDropdownInterface(data) {
    console.log('=== BUILD DROPDOWN DEBUG ===');
    console.log('Data received:', data);
    console.log('Data length:', data.length);
    
    let interfaceHTML = `
        <div class="mb-4">
            <div class="p-3 bg-purple-50 rounded-lg border border-purple-200">
                <div class="text-sm text-purple-700">
                    <strong>📋 Copy BSM dari surat jalan yang sudah ada nilai</strong><br>
                    <span class="text-xs">BSM kosong di ${currentCopyTarget.suratjalanno} (Plot: ${currentCopyTarget.plot}) dapat copy dari ${data.length} sumber BSM berikut:</span>
                </div>
            </div>
        </div>
        
        <div class="space-y-4">
            <div>
                <label for="source-bsm-select" class="block text-sm font-medium text-gray-700 mb-2">
                    Pilih sumber BSM yang akan dicopy:
                </label>
                <select id="source-bsm-select" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    <option value="">-- Pilih surat jalan sumber --</option>
    `;
    
    console.log('Building options for', data.length, 'items...');
    
    data.forEach((item, index) => {
        console.log(`Item ${index}:`, item);
        interfaceHTML += `
            <option value="${item.id}" 
                    data-bersih="${item.nilaibersih}" 
                    data-segar="${item.nilaisegar}" 
                    data-manis="${item.nilaimanis}" 
                    data-grade="${item.grade}" 
                    data-score="${item.averagescore}"
                    data-suratjalan="${item.suratjalanno}">
                ${item.display_text}
            </option>
        `;
    });
    
    interfaceHTML += `
                </select>
            </div>
            
            <!-- Preview of values to copy -->
            <div id="bsm-preview" class="hidden bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-900 mb-3">
                    📄 Preview - Nilai yang akan dicopy dari <span id="preview-source-sj" class="font-bold text-purple-600"></span>:
                </h4>
                <div class="grid grid-cols-3 gap-4 text-xs">
                    <div class="text-center p-3 bg-blue-100 rounded-lg">
                        <div class="font-medium text-blue-800">Nilai Bersih</div>
                        <div class="text-lg font-bold text-blue-900" id="preview-bersih">-</div>
                    </div>
                    <div class="text-center p-3 bg-green-100 rounded-lg">
                        <div class="font-medium text-green-800">Nilai Segar</div>
                        <div class="text-lg font-bold text-green-900" id="preview-segar">-</div>
                    </div>
                    <div class="text-center p-3 bg-yellow-100 rounded-lg">
                        <div class="font-medium text-yellow-800">Nilai Manis</div>
                        <div class="text-lg font-bold text-yellow-900" id="preview-manis">-</div>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <div class="text-xs text-gray-600 mb-1">Hasil copy akan mendapat:</div>
                    <span class="text-xs text-gray-600">Average Score: </span>
                    <span class="font-bold text-lg" id="preview-score">-</span>
                    <span class="ml-3 text-xs text-gray-600">Grade: </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" id="preview-grade-badge">-</span>
                </div>
            </div>
            
            <!-- Copy button -->
            <div class="flex justify-between items-center pt-4">
                <div class="text-xs text-gray-500">
                    ℹ️ Copy akan mengisi: Nilai Bersih, Nilai Segar, Nilai Manis, Average Score, Grade + parentbsm tracking
                </div>
                <button type="button" 
                        id="execute-copy-btn"
                        onclick="executeBsmCopy()"
                        disabled
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white transition duration-150 ease-in-out disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed enabled:bg-purple-600 enabled:hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Copy BSM
                </button>
            </div>
        </div>
    `;
    
    console.log('Setting innerHTML to modal content...');
    const modalContent = document.getElementById('copy_modal_content');
    console.log('Modal content element:', modalContent);
    
    if (modalContent) {
        modalContent.innerHTML = interfaceHTML;
        console.log('HTML set successfully. Content length:', interfaceHTML.length);
        
        // Add event listener for dropdown change
        const selectElement = document.getElementById('source-bsm-select');
        console.log('Select element found:', selectElement);
        
        if (selectElement) {
            selectElement.addEventListener('change', function() {
                console.log('Dropdown changed, value:', this.value);
                const selectedOption = this.options[this.selectedIndex];
                const copyBtn = document.getElementById('execute-copy-btn');
                const preview = document.getElementById('bsm-preview');
                
                if (this.value) {
                    // Show preview
                    const bersih = parseFloat(selectedOption.dataset.bersih);
                    const segar = parseFloat(selectedOption.dataset.segar);
                    const manis = parseFloat(selectedOption.dataset.manis);
                    const grade = selectedOption.dataset.grade;
                    const score = parseFloat(selectedOption.dataset.score);
                    const suratjalanno = selectedOption.dataset.suratjalan;
                    
                    console.log('Showing preview for:', { bersih, segar, manis, grade, score, suratjalanno });
                    
                    document.getElementById('preview-source-sj').textContent = suratjalanno;
                    document.getElementById('preview-bersih').textContent = formatNumber(bersih);
                    document.getElementById('preview-segar').textContent = formatNumber(segar);
                    document.getElementById('preview-manis').textContent = formatNumber(manis);
                    document.getElementById('preview-score').textContent = formatNumber(score);
                    
                    const gradeBadge = document.getElementById('preview-grade-badge');
                    gradeBadge.textContent = grade;
                    gradeBadge.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getBsmGradeColor(grade)}`;
                    
                    preview.classList.remove('hidden');
                    
                    // Enable button with proper purple styling
                    copyBtn.disabled = false;
                    copyBtn.className = "inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition duration-150 ease-in-out";
                    console.log('Button enabled with purple styling');
                } else {
                    preview.classList.add('hidden');
                    
                    // Disable button with gray styling
                    copyBtn.disabled = true;
                    copyBtn.className = "inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-300 text-gray-500 cursor-not-allowed transition duration-150 ease-in-out";
                    console.log('Button disabled with gray styling');
                }
            });
            console.log('Event listener added successfully');
        } else {
            console.error('Could not find select element!');
        }
    } else {
        console.error('Could not find copy_modal_content element!');
    }
    
    console.log('=== BUILD DROPDOWN COMPLETE ===');
}

function showNoCopyDataMessage(plot, customMessage = null) {
    const message = customMessage || `Belum ada data BSM lengkap untuk plot ${plot} yang dapat dicopy.`;
    
    document.getElementById('copy_modal_content').innerHTML = `
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak dapat copy BSM</h3>
            <p class="mt-1 text-sm text-gray-500">${message}</p>
        </div>
    `;
}

function executeBsmCopy() {
    const selectElement = document.getElementById('source-bsm-select');
    const sourceBsmId = selectElement.value;
    
    if (!sourceBsmId || !currentCopyTarget) {
        showNotification('Pilih data BSM yang akan dicopy', 'error');
        return;
    }
    
    const copyBtn = document.getElementById('execute-copy-btn');
    const originalHTML = copyBtn.innerHTML;
    
    // Show loading state
    copyBtn.disabled = true;
    copyBtn.innerHTML = `
        <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Copying...
    `;
    
    // Send copy request
    fetch(window.copyBsmUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            source_bsm_id: parseInt(sourceBsmId),
            target_suratjalanno: currentCopyTarget.suratjalanno,
            rkhno: currentRkhno
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Data BSM berhasil dicopy!', 'success');
            closeCopyModal();
            
            // Refresh main modal data
            setTimeout(() => {
                showDetailModal(currentRkhno);
            }, 1000);
        } else {
            throw new Error(data.message || 'Gagal copy data BSM');
        }
    })
    .catch(error => {
        console.error('Error copying BSM data:', error);
        showNotification(error.message || 'Terjadi kesalahan saat copy data BSM', 'error');
        
        // Restore button state
        copyBtn.disabled = false;
        copyBtn.innerHTML = originalHTML;
    });
}

function closeCopyModal() {
    console.log('=== CLOSING COPY MODAL ===');
    const modal = document.getElementById('copy_bsm_modal');
    if (modal) {
        modal.classList.add('hidden');
        console.log('Copy modal closed successfully');
    } else {
        console.error('Copy modal not found when trying to close');
    }
    
    // Reset modal state
    document.getElementById('copy_modal_loading').classList.add('hidden');
    document.getElementById('copy_modal_error').classList.add('hidden');
    document.getElementById('copy_modal_content').innerHTML = '';
    currentCopyTarget = null;
    
    console.log('Copy modal state reset');
}

function copyBsmData(sourceBsmId) {
    // This function is now deprecated, kept for compatibility
    // Use executeBsmCopy() instead with dropdown selection
    if (!currentCopyTarget) {
        showNotification('Target copy tidak valid', 'error');
        return;
    }
    
    // Direct copy without dropdown (legacy support)
    fetch(window.copyBsmUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            source_bsm_id: sourceBsmId,
            target_suratjalanno: currentCopyTarget.suratjalanno,
            rkhno: currentRkhno
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Data BSM berhasil dicopy!', 'success');
            closeCopyModal();
            setTimeout(() => {
                showDetailModal(currentRkhno);
            }, 1000);
        } else {
            throw new Error(data.message || 'Gagal copy data BSM');
        }
    })
    .catch(error => {
        console.error('Error copying BSM data:', error);
        showNotification(error.message || 'Terjadi kesalahan saat copy data BSM', 'error');
    });
}

function showNoDataMessage(rkhno) {
    document.getElementById('modal_detail_content').innerHTML = `
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v10z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data BSM</h3>
            <p class="mt-1 text-sm text-gray-500">Tidak ditemukan data BSM untuk RKH: ${rkhno}</p>
        </div>
    `;
}

function trackEdit(suratjalanno, field, value) {
    if (!editedRows.has(suratjalanno)) {
        editedRows.set(suratjalanno, {});
    }
    
    editedRows.get(suratjalanno)[field] = parseFloat(value) || 0;
    
    const currentEdit = editedRows.get(suratjalanno);
    if (currentEdit.nilaibersih !== undefined && 
        currentEdit.nilaisegar !== undefined && 
        currentEdit.nilaimanis !== undefined) {
        
        const avg = (currentEdit.nilaibersih + currentEdit.nilaisegar + currentEdit.nilaimanis) / 3;
        
        let newGrade = '';
        if (avg < 1200) {
            newGrade = 'A';
        } else if (avg < 1999) {
            newGrade = 'B';
        } else if (avg >= 2000) {
            newGrade = 'C';
        }
        
        const avgElement = document.getElementById(`avg-${suratjalanno}`);
        if (avgElement) {
            avgElement.textContent = formatNumber(avg);
            avgElement.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getBsmScoreColor(avg)}`;
        }
        
        const gradeElement = document.getElementById(`grade-${suratjalanno}`);
        if (gradeElement) {
            gradeElement.textContent = newGrade;
            gradeElement.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getBsmGradeColor(newGrade)}`;
        }
    }
    
    const saveBtn = document.querySelector(`.save-row-btn[data-suratjalan="${suratjalanno}"]`);
    if (saveBtn) {
        saveBtn.classList.remove('hidden');
    }
    
    if (editedRows.size > 0) {
        document.getElementById('bulk-save-btn').classList.remove('hidden');
    }
}

function saveRowChanges(suratjalanno) {
    const edits = editedRows.get(suratjalanno);
    if (!edits) return;
    
    const saveBtn = document.querySelector(`.save-row-btn[data-suratjalan="${suratjalanno}"]`);
    
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
    }
    
    fetch(window.updateBsmUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            suratjalanno: suratjalanno,
            rkhno: currentRkhno,
            updates: edits
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            editedRows.delete(suratjalanno);
            
            if (saveBtn) {
                saveBtn.classList.add('hidden');
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Simpan';
            }
            
            if (editedRows.size === 0) {
                document.getElementById('bulk-save-btn').classList.add('hidden');
            }
            
            showNotification('Data berhasil disimpan!', 'success');
            
            const row = document.querySelector(`tr[data-suratjalan="${suratjalanno}"]`);
            if (row) {
                row.classList.remove('bg-yellow-50', 'hover:bg-yellow-100');
                row.classList.add('hover:bg-gray-50');
                
                const inputs = row.querySelectorAll('.editable-input');
                inputs.forEach(input => {
                    const value = parseFloat(input.value);
                    input.outerHTML = `<span class="text-gray-900">${formatNumber(value)}</span>`;
                });
                
                const actionCell = row.querySelector('td:last-child');
                if (actionCell) {
                    actionCell.innerHTML = '<span class="text-gray-400 text-xs">Tersimpan</span>';
                }
                
                const indicator = row.querySelector('.bg-amber-400');
                if (indicator) {
                    indicator.remove();
                }
            }
            
        } else {
            throw new Error(data.message || 'Gagal menyimpan data');
        }
    })
    .catch(error => {
        console.error('Error saving row:', error);
        showNotification(error.message || 'Terjadi kesalahan saat menyimpan data', 'error');
        
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Simpan';
        }
    });
}

function saveBulkChanges() {
    if (editedRows.size === 0) return;
    
    const bulkSaveBtn = document.getElementById('bulk-save-btn');
    const originalHTML = bulkSaveBtn.innerHTML;
    
    bulkSaveBtn.disabled = true;
    bulkSaveBtn.innerHTML = '<svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Menyimpan...';
    
    const bulkData = [];
    editedRows.forEach((edits, suratjalanno) => {
        bulkData.push({
            suratjalanno: suratjalanno,
            updates: edits
        });
    });
    
    fetch(window.updateBsmBulkUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            rkhno: currentRkhno,
            bulk_updates: bulkData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            editedRows.clear();
            document.querySelectorAll('.save-row-btn').forEach(btn => btn.classList.add('hidden'));
            bulkSaveBtn.classList.add('hidden');
            
            showNotification(`${data.updated_count} data berhasil disimpan!`, 'success');
            
            setTimeout(() => {
                showDetailModal(currentRkhno);
            }, 1000);
            
        } else {
            throw new Error(data.message || 'Gagal menyimpan data');
        }
    })
    .catch(error => {
        console.error('Error bulk saving:', error);
        showNotification(error.message || 'Terjadi kesalahan saat menyimpan data', 'error');
    })
    .finally(() => {
        bulkSaveBtn.disabled = false;
        bulkSaveBtn.innerHTML = originalHTML;
    });
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 max-w-sm w-full ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;
    notification.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${type === 'success' 
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>'
                }
            </svg>
            <span class="text-sm font-medium">${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

function formatNumber(num) {
    const number = parseFloat(num || 0);
    return number.toLocaleString('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function getBsmGradeColor(grade) {
    if (!grade) return 'bg-gray-100 text-gray-800';
    
    switch(grade.toUpperCase()) {
        case 'A':
            return 'bg-green-100 text-green-800';
        case 'B':
            return 'bg-yellow-100 text-yellow-800';
        case 'C':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getBsmScoreColor(score) {
    const numScore = parseFloat(score || 0);
    if (numScore < 1200) {
        return 'bg-green-100 text-green-800';
    } else if (numScore < 1999) {
        return 'bg-yellow-100 text-yellow-800';
    } else if (numScore >= 2000) {
        return 'bg-red-100 text-red-800';
    } else {
        return 'bg-gray-100 text-gray-800';
    }
}

function closeDetailModal() {
    if (editedRows.size > 0) {
        if (!confirm('Ada perubahan yang belum disimpan. Yakin ingin menutup modal?')) {
            return;
        }
    }
    
    document.getElementById('detail_modal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    
    document.getElementById('modal_detail_content').innerHTML = '';
    document.getElementById('modal_loading').classList.add('hidden');
    document.getElementById('modal_error').classList.add('hidden');
    document.getElementById('bulk-save-btn').classList.add('hidden');
    
    editedRows.clear();
    currentRkhno = '';
}

// Event listeners
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        if (!document.getElementById('copy_bsm_modal').classList.contains('hidden')) {
            closeCopyModal();
        } else {
            closeDetailModal();
        }
    }
});

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const tanggalAwal = document.getElementById('tanggalawal').value;
            const tanggalAkhir = document.getElementById('tanggalakhir').value;

            if (!tanggalAwal || !tanggalAkhir) {
                e.preventDefault();
                alert('Mohon lengkapi tanggal awal dan tanggal akhir');
                return false;
            }

            if (new Date(tanggalAwal) > new Date(tanggalAkhir)) {
                e.preventDefault();
                alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir');
                return false;
            }

            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalHTML = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Mencari...
            `;

            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
            }, 10000);
        });
    }
});