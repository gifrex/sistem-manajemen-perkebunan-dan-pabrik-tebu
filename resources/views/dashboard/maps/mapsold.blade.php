<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <style>
        .header {
            background: linear-gradient(135deg, #4285F4, #34A853);
            color: #b6b9bd;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 300;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .controls {
            padding: 20px;
            background: #b6b9bd;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .info-panel {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .info-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
        }

        .marker-icon {
            background: #EA4335;
        }

        .polyline-icon {
            background: #4285F4;
        }

        .toggle-btn {
            background: linear-gradient(135deg, #34A853, #2E7D32);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 168, 83, 0.3);
        }

        .toggle-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 168, 83, 0.4);
        }

        .toggle-btn:active {
            transform: translateY(0);
        }

        .map-type-controls {
            display: flex;
            gap: 10px;
        }

        .map-type-btn {
            background: white;
            border: 2px solid #ddd;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .map-type-btn.active {
            background: #4285F4;
            color: white;
            border-color: #4285F4;
        }

        .map-type-btn:hover {
            border-color: #4285F4;
        }

        #map {
            height: 600px;
            width: 100%;
        }

        .info-window {
            font-family: 'Segoe UI', sans-serif;
            line-height: 1.6;
            max-width: 300px;
        }

        .info-title {
            font-weight: bold;
            color: #4285F4;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .info-detail {
            color: #666;
            font-size: 0.9rem;
        }

        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 600px;
            background: #f8f9fa;
            color: #666;
            font-size: 1.2rem;
        }

        .error {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 600px;
            background: #f8f9fa;
            color: #666;
            font-size: 1.2rem;
            text-align: center;
        }

        .error-note {
            margin-top: 20px;
            padding: 15px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            color: #856404;
            max-width: 500px;
        }

        @media (max-width: 768px) {
            .controls {
                flex-direction: column;
                text-align: center;
            }

            .info-panel {
                justify-content: center;
            }

            .header h1 {
                font-size: 2rem;
            }

            #map {
                height: 500px;
            }
        }

        /* Only minimal styles for the new features */
        .pulse { animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.6; } 100% { opacity: 1; } }
        .blink { animation: blink 1.5s infinite; }
        @keyframes blink { 0%, 50% { opacity: 1; } 51%, 100% { opacity: 0.4; } }
        .info-metrics { display: flex; gap: 15px; margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 8px; }
        .info-metric { display: flex; align-items: center; gap: 5px; font-size: 0.9rem; }
    </style>
    <div class="container">
        <!-- Dynamic Title showing all blocks -->
        <div class="bg-gradient-to-r from-blue-500 to-green-500 text-white p-4 rounded-lg shadow-lg mb-5">
            <h2 class="text-2xl font-bold text-center mb-2">🌾 Agricultural GPS Monitoring System</h2>
            <div class="text-center">
                <span class="text-lg font-semibold">Available Blocks: </span>
                <span id="blocksList" class="text-xl font-bold tracking-wider">Loading...</span>
            </div>
        </div>
        <!-- Enhanced Filter Section -->
        <div class="bg-white p-5 rounded-lg shadow-md mb-5">
            <!-- View Mode Controls -->
            <div class="flex flex-wrap gap-3 items-center mb-4">
                <span class="font-semibold text-gray-700">View Mode:</span>
                <button class="px-4 py-2 rounded-full bg-green-500 text-white font-medium hover:bg-green-600 transition-colors" onclick="setViewMode('combined')" id="viewCombined">📊 Combined</button>
                <button class="px-4 py-2 rounded-full bg-gray-200 text-gray-700 font-medium hover:bg-blue-500 hover:text-white transition-colors" onclick="setViewMode('progress')" id="viewProgress">🌱 Progress</button>
                <button class="px-4 py-2 rounded-full bg-gray-200 text-gray-700 font-medium hover:bg-blue-500 hover:text-white transition-colors" onclick="setViewMode('infection')" id="viewInfection">🐛 Infection</button>
                <button class="px-4 py-2 rounded-full bg-gray-200 text-gray-700 font-medium hover:bg-blue-500 hover:text-white transition-colors" onclick="setViewMode('success')" id="viewSuccess">⭐ Success</button>
            </div>

            <!-- Filter Controls -->
            <div class="flex flex-wrap gap-3 items-center mb-4">
                <span class="font-semibold text-gray-700">Filter by Health:</span>
                <button class="px-3 py-1 rounded-full bg-green-100 text-green-700 border-2 border-green-300 hover:bg-green-200 transition-colors" onclick="toggleFilter('healthy')" id="filterHealthy">🟢 Healthy</button>
                <button class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 border-2 border-yellow-300 hover:bg-yellow-200 transition-colors" onclick="toggleFilter('moderate')" id="filterModerate">🟡 Moderate</button>
                <button class="px-3 py-1 rounded-full bg-red-100 text-red-700 border-2 border-red-300 hover:bg-red-200 transition-colors" onclick="toggleFilter('high')" id="filterHigh">🔴 High Risk</button>
                <button class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 border-2 border-gray-300 hover:bg-gray-200 transition-colors" onclick="toggleFilter('burned')" id="filterBurned">🔥 Burned</button>
            </div>

            <div class="flex flex-wrap gap-3 items-center mb-4">
                <span class="font-semibold text-gray-700">Filter by Progress:</span>
                <button class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200 transition-colors" onclick="toggleFilter('planning')" id="filterPlanning">🌱 Planning</button>
                <button class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200 transition-colors" onclick="toggleFilter('growing')" id="filterGrowing">🌿 Growing</button>
                <button class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200 transition-colors" onclick="toggleFilter('harvest')" id="filterHarvest">🌾 Harvest</button>
            </div>

            <!-- Legend -->
            <div class="flex flex-wrap gap-8 mt-4 p-4 bg-gray-100 rounded-lg">
                <div class="flex flex-col gap-2">
                    <div class="font-semibold text-gray-700 text-sm">Progress Stages</div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">🌱 Planning/Planting (0-33%)</div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">🌿 Growing (34-66%)</div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">🌾 Harvest Ready (67-100%)</div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">🔥 Burned (101%+)</div>
                </div>
                <div class="flex flex-col gap-2">
                    <div class="font-semibold text-gray-700 text-sm">Infection Levels</div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">🟢🐛 Healthy (0-10%)</div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">🟡🐛 Moderate (11-30%)</div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">🔴🐛 High Risk (31-100%)</div>
                </div>
                <div class="flex flex-col gap-2">
                    <div class="font-semibold text-gray-700 text-sm">Success Rates</div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">⭐ Excellent (90%+)</div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">✅ Good (70-89%)</div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">⚠️ Needs Attention (<70%)</div>
                </div>
            </div>
        </div>

        <div class="controls">
            <div class="info-panel">
              <div class="flex items-center justify-center w-full">
                  <label for="avatar" class="flex flex-col items-center justify-center w-full max-w-lg border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                      <form action="{{ route('dashboard.maps.upload') }}" id="frm-submit" method="post" enctype="multipart/form-data"
                            onsubmit="return validateForm()">
                            @csrf
                        <input id="gpxFile" type="file" name="gpxFile"  accept=".gpx" />
                      </form>
                  </label>
              </div>
            </div>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <div class="map-type-controls">
                    <button class="map-type-btn active" onclick="setMapType('roadmap')">Roadmap</button>
                    <button class="map-type-btn" onclick="setMapType('satellite')">Satellite</button>
                    <button class="map-type-btn" onclick="setMapType('hybrid')">Hybrid</button>

                </div>

                <button class="toggle-btn" onclick="togglePolyline()">
                    <span id="toggleText">Sembunyikan Polyline</span>
                </button>

            </div>
        </div>

        <div id="map">
            <div class="error">
                <div>⚠️ Google Maps tidak dapat dimuat dalam environment ini</div>
                <div class="error-note">
                    <strong>Catatan:</strong> Google Maps API memerlukan konfigurasi khusus dan tidak dapat dijalankan langsung dalam artifact ini.
                    Untuk menggunakan Google Maps, Anda perlu:
                    <ul style="text-align: left; margin-top: 10px;">
                        <li>Mendapatkan API key dari Google Cloud Console</li>
                        <li>Mengaktifkan Google Maps JavaScript API</li>
                        <li>Menjalankan kode di server atau hosting web</li>
                    </ul>
                    <p>Sebagai alternatif, versi dengan Leaflet dapat digunakan langsung tanpa konfigurasi tambahan.</p>
                </div>
            </div>
        </div>
    </div>

<script>
        // Data GPS Header (Marker)
        document.addEventListener("DOMContentLoaded", () => {
            const fileInput = document.getElementById("gpxFile");
            const dropArea = document.querySelector("label[for='gpxFile']");
            const uploadPlaceholder = document.getElementById("uploadPlaceholder");
            const filePreview = document.getElementById("filePreview");
            const fileNameElement = document.getElementById("fileName");

            const handleFile = (file) => {
                if (file) {
                    uploadPlaceholder?.classList.add("invisible");
                    filePreview?.classList.remove("invisible");
                    if (fileNameElement) fileNameElement.textContent = file.name;
                } else {
                    uploadPlaceholder?.classList.remove("invisible");
                    filePreview?.classList.add("invisible");
                }
            };

            fileInput.addEventListener("change", (e) => handleFile(e.target.files[0]));

            dropArea.addEventListener("dragover", (e) => {
                e.preventDefault();
                dropArea.classList.add("border-green-500", "bg-green-100");
            });

            dropArea.addEventListener("dragleave", () => dropArea.classList.remove("border-green-500",
                "bg-green-100"));

            dropArea.addEventListener("drop", (e) => {
                e.preventDefault();
                dropArea.classList.remove("border-green-500", "bg-green-100");

                const file = e.dataTransfer.files[0];
                if (file) {
                    fileInput.files = e.dataTransfer.files;
                    handleFile(file);
                }
            });
        });

        $('#gpxFile').change(function(){
          $('#frm-submit').submit();
        })

        function validateForm() {
            const file = document.getElementById("gpxFile").files[0];
            if (!file) return alert("No file selected."), false;

            const fileExtension = file.name.split(".").pop().toLowerCase();
            if (file.type !== "application/gpx+xml" && fileExtension !== "gpx")
                return alert("Please upload a valid GPX file."), false;

            if (file.size > 20 * 1024 * 1024)
                return alert("File size exceeds the maximum limit of 20MB."), false;

            return true;
        }

        // Helper function to get progress percentage
        function getProgressPercentage(plotCode) {
            const blockLetter = plotCode.charAt(0);
            
            // Special cases for Q and S = 50%
            if (blockLetter === 'Q' || blockLetter === 'S') {
                return 50;
            }
            
            const blockIndex = blockLetter.charCodeAt(0) - 65;
            return (blockIndex / 22) * 100;
        }

        // Dummy data generation functions based on alphabetical position
        function getDummyProgress(plotCode) {
            const blockLetter = plotCode.charAt(0);
            
            // Special cases for Q and S = 50%
            if (blockLetter === 'Q' || blockLetter === 'S') {
                return 'growing'; // 50% falls in growing category (34-66%)
            }
            
            // Special case for W = burned
            if (blockLetter === 'W') {
                return 'burned';
            }
            
            const blockIndex = blockLetter.charCodeAt(0) - 65; // A=0, B=1, C=2, etc.
            const progressPercentage = (blockIndex / 22) * 100; // W is position 22 (W-A=22)
            
            // Check for 101%+ progress (burned)
            if (progressPercentage > 100) return 'burned';
            if (progressPercentage <= 33) return 'planning';     // 0-33% = Planning
            if (progressPercentage <= 66) return 'growing';      // 34-66% = Growing  
            return 'harvest';                                    // 67-100% = Harvest
        }

        function getDummyInfectionRate(plotCode) {
            const blockLetter = plotCode.charAt(0);
            
            // Special cases for Q and S = 50%
            if (blockLetter === 'Q' || blockLetter === 'S') {
                return 50;
            }
            
            const blockIndex = blockLetter.charCodeAt(0) - 65; // A=0, B=1, C=2, etc.
            
            // A=0%, W=100%, others proportional to position
            // W is at position 22 (since W-A = 22)
            const infectionRate = Math.round((blockIndex / 22) * 100);
            return Math.min(infectionRate, 100);
        }

        function getDummySuccessRate(plotCode) {
            const blockLetter = plotCode.charAt(0);
            
            // Special cases for Q and S = 50%
            if (blockLetter === 'Q' || blockLetter === 'S') {
                return 50;
            }
            
            const blockIndex = blockLetter.charCodeAt(0) - 65; // A=0, B=1, C=2, etc.
            
            // FIXED: Burned plots always have 0% success rate
            if (blockLetter === 'W' || (blockIndex / 22) * 100 > 100) {
                return 0;
            }
            
            // A=100%, W=0%, others inversely proportional to position
            const successRate = Math.round(100 - ((blockIndex / 22) * 100));
            return Math.max(successRate, 0);
        }

        // FIXED: Check if plot is burned based on PROGRESS (not infection rate)
        function isBurned(plotCode) {
            const blockLetter = plotCode.charAt(0);
            // Special case for W = burned
            if (blockLetter === 'W') {
                return true;
            }
            const blockIndex = blockLetter.charCodeAt(0) - 65;
            const progressPercentage = (blockIndex / 22) * 100;
            return progressPercentage > 100; // 101%+ progress = burned
        }

        // Enhanced utility functions
        function getProgressIcon(progress) {
            const icons = {
                'planning': '🌱',
                'growing': '🌿', 
                'harvest': '🌾',
                'burned': '🔥'
            };
            return icons[progress] || '🌱';
        }

        function getInfectionIcon(rate) {
            if (rate <= 10) return '🟢';
            if (rate <= 30) return '🟡';
            return '🔴';
        }

        function getInfectionLevel(rate) {
            // FIXED: Remove burned from infection levels, it's now progress-based
            if (rate <= 10) return 'healthy';
            if (rate <= 30) return 'moderate';
            return 'high';
        }

        function getSuccessIcon(rate) {
            if (rate >= 90) return '⭐';     // 90%+ = Excellent
            if (rate >= 70) return '✅';     // 70-89% = Good
            return '⚠️';                    // <70% = Needs Attention
        }

        function getProgressIconByPercentage(percentage) {
            // Special handling for W block (should be burned even at 100%)
            if (percentage === 100) return '🔥';       // W = 100% = Burned (special case)
            if (percentage > 100) return '🔥';         // 101%+ = Burned
            if (percentage <= 33) return '🌱';        // 0-33% = Planning
            if (percentage <= 66) return '🌿';        // 34-66% = Growing
            return '🌾';                              // 67-99% = Harvest
        }

        // Enhanced header data with dummy values
        const headerData = [
          @foreach( $header as $item )
            { 
                code: '{{ $item->plot }}', 
                lat: {{ $item->centerlatitude }}, 
                lng: {{ $item->centerlongitude }},
                progress: getDummyProgress('{{ $item->plot }}'),
                infection_rate: getDummyInfectionRate('{{ $item->plot }}'),
                success_rate: getDummySuccessRate('{{ $item->plot }}')
            },
          @endforeach
        ];

        // Data GPS List (Polyline)
        const listData = [
          @foreach($list as $item)
            { code: '{{ $item->plot }}', lat: {{ $item->latitude }}, lng: {{ $item->longitude }} },
          @endforeach
        ];

        let map;
        let polyline;
        let markers = [];
        let polygons = [];
        let polylineVisible = true;
        let currentViewMode = 'combined';
        let activeFilters = {
            healthy: true,
            moderate: true,
            high: true,
            planning: true,
            growing: true,
            harvest: true,
            burned: true
        };

        function createCustomMarkerIcon(point) {
            const progressIcon = getProgressIcon(point.progress);
            const infectionIcon = getInfectionIcon(point.infection_rate);
            const successIcon = getSuccessIcon(point.success_rate);
            
            let iconText = '';
            let fillColor = '#ffffff';
            let fillPercentage = 0;
            
            // FIXED: Always show burn icon with red fill for burned plots in ALL modes
            if (point.progress === 'burned') {
                iconText = '🔥';
                fillColor = '#EF4444'; // Red for burned
                fillPercentage = 100;  // Full red fill for burned
            } else {
                switch (currentViewMode) {
                    case 'progress':
                        // Special handling for Q and S = 50%
                        const blockLetter = point.code.charAt(0);
                        let progressPercentage;
                        
                        if (blockLetter === 'Q' || blockLetter === 'S') {
                            progressPercentage = 50;
                        } else {
                            const blockIndex = blockLetter.charCodeAt(0) - 65;
                            progressPercentage = (blockIndex / 22) * 100;
                        }
                        
                        iconText = getProgressIconByPercentage(progressPercentage);
                        fillColor = '#10B981'; // Green for progress
                        fillPercentage = progressPercentage;
                        break;
                    case 'infection':
                        iconText = '🐛';
                        fillColor = '#EF4444'; // Red for infection
                        fillPercentage = point.infection_rate;
                        break;
                    case 'success':
                        iconText = getSuccessIcon(point.success_rate);
                        fillColor = '#10B981'; // Green for success
                        fillPercentage = point.success_rate;
                        break;
                    default: // combined
                        iconText = progressIcon;
                        fillPercentage = 0; // No fill for combined mode
                        if (point.infection_rate > 30) fillColor = '#FEF3C7';
                        else if (point.success_rate > 80) fillColor = '#D1FAE5';
                        break;
                }
            }

            let svg = '';
            
            if (currentViewMode === 'combined' && point.progress !== 'burned') {
                // Combined mode - simple background color (except for burned)
                svg = `
                    <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="20" cy="20" r="18" fill="${fillColor}" stroke="#333" stroke-width="2"/>
                        <text x="20" y="26" font-size="16" text-anchor="middle" fill="#333">${iconText}</text>
                    </svg>
                `;
            } else {
                // Progress, Infection, Success modes - percentage fill OR burned with red fill
                svg = `
                    <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="fillGradient${point.code}" x1="0%" y1="100%" x2="0%" y2="0%">
                                <stop offset="0%" style="stop-color:${fillColor};stop-opacity:0.8" />
                                <stop offset="${fillPercentage}%" style="stop-color:${fillColor};stop-opacity:0.8" />
                                <stop offset="${fillPercentage}%" style="stop-color:#ffffff;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#ffffff;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                        <circle cx="20" cy="20" r="18" fill="url(#fillGradient${point.code})" stroke="#333" stroke-width="2"/>
                        <text x="20" y="26" font-size="16" text-anchor="middle" fill="#333">${iconText}</text>
                    </svg>
                `;
            }
            
            return {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
                scaledSize: new google.maps.Size(50, 50),
                anchor: new google.maps.Point(25, 25)
            };
        }

        function shouldShowMarker(point) {
            const infectionLevel = getInfectionLevel(point.infection_rate || 0);
            const progressLevel = point.progress || 'planning';
            
            return activeFilters[infectionLevel] && activeFilters[progressLevel];
        }

        function updateMarkers() {
            // Clear existing markers
            markers.forEach(marker => marker.setMap(null));
            markers = [];

            headerData.forEach((point, index) => {
                if (!shouldShowMarker(point)) return;

                const blockLetter = point.code.charAt(0);
                const customIcon = createCustomMarkerIcon(point);

                const marker = new google.maps.Marker({
                    position: { lat: point.lat, lng: point.lng },
                    map: map,
                    title: `${point.code} - ${point.progress} (${point.success_rate}% success)`,
                    icon: customIcon,
                    zIndex: point.progress === 'burned' ? 1000 : (point.infection_rate > 30 ? 500 : 100)
                });

                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div class="info-window">
                            <div class="info-title">📍 Block ${blockLetter} - ${point.code}</div>
                            <div class="info-detail">
                                <strong>Block:</strong> ${blockLetter}<br>
                                <strong>Plot:</strong> ${point.code}<br>
                                <strong>Latitude:</strong> ${point.lat.toFixed(6)}<br>
                                <strong>Longitude:</strong> ${point.lng.toFixed(6)}
                            </div>
                            <div class="info-metrics">
                                <div class="info-metric">
                                    ${getProgressIcon(point.progress)} ${point.progress.charAt(0).toUpperCase() + point.progress.slice(1)}
                                </div>
                                <div class="info-metric">
                                    ${getInfectionIcon(point.infection_rate)}🐛 ${point.infection_rate}%
                                </div>
                                <div class="info-metric">
                                    ${getSuccessIcon(point.success_rate)} ${point.success_rate}%
                                </div>
                            </div>
                        </div>
                    `
                });

                marker.addListener('click', () => {
                    infoWindow.open(map, marker);
                });

                markers.push(marker);
            });
        }

        function updatePolygons(blockColorMap) {
            // Clear existing polygons
            polygons.forEach(polygon => polygon.setMap(null));
            polygons = [];

            headerData.forEach((point, index) => {
                if (!shouldShowMarker(point)) return;

                const blockLetter = point.code.charAt(0);
                let blockColor = blockColorMap[blockLetter] || '#FF0000';
                
                // FIXED: Override color to BLACK for burned plots (based on progress)
                if (point.progress === 'burned') {
                    blockColor = '#000000'; // Black for burned plots
                }
                
                const filtered = filterByCodes(point.code);

                if (filtered.length < 3) return;

                const polygonCoordinates = filtered.map(item => ({
                    lat: parseFloat(item.lat),
                    lng: parseFloat(item.lng)
                }));

                let fillOpacity = 0.35;
                if (point.progress === 'burned') fillOpacity = 0.8; // Higher opacity for burned
                else if (point.infection_rate > 30) fillOpacity = 0.6;
                else if (point.infection_rate > 10) fillOpacity = 0.45;

                const polygon = new google.maps.Polygon({
                    paths: polygonCoordinates,
                    strokeColor: blockColor,
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: blockColor,
                    fillOpacity: fillOpacity,
                    map: polylineVisible ? map : null
                });

                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div class="info-window">
                            <div class="info-title">🏞️ Block ${blockLetter} - Plot ${point.code}</div>
                            <div class="info-detail">
                                <strong>Block:</strong> ${blockLetter}<br>
                                <strong>Plot:</strong> ${point.code}<br>
                                <strong>Points:</strong> ${filtered.length}<br>
                                <strong>Status:</strong> ${point.progress}<br>
                                <strong>Health:</strong> ${point.progress === 'burned' ? 'Burned 🔥' : getInfectionLevel(point.infection_rate)}
                            </div>
                            <div class="info-metrics">
                                <div class="info-metric">
                                    ${getProgressIcon(point.progress)} ${point.progress}
                                </div>
                                <div class="info-metric">
                                    ${getInfectionIcon(point.infection_rate)}🐛 ${point.infection_rate}%
                                </div>
                                <div class="info-metric">
                                    ${getSuccessIcon(point.success_rate)} ${point.success_rate}%
                                </div>
                            </div>
                        </div>
                    `
                });

                polygon.addListener('click', (e) => {
                    infoWindow.setPosition(e.latLng);
                    infoWindow.open(map);
                });

                polygons.push(polygon);
            });
        }

        // Enhanced control functions
        function setViewMode(mode) {
            currentViewMode = mode;
            
            // Update button states
            document.querySelectorAll('#viewCombined, #viewProgress, #viewInfection, #viewSuccess').forEach(btn => {
                btn.classList.remove('bg-green-500', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            });
            
            const activeBtn = document.getElementById('view' + mode.charAt(0).toUpperCase() + mode.slice(1));
            if (activeBtn) {
                activeBtn.classList.remove('bg-gray-200', 'text-gray-700');
                activeBtn.classList.add('bg-green-500', 'text-white');
            }
            
            updateMarkers();
            updateBlocksList(); // Update blocks list when view mode changes
        }

        function updateBlocksList() {
            const blocks = getUniqueBlocks();
            const blocksListElement = document.getElementById('blocksList');
            
            if (blocksListElement) {
                // Create a colorful display of blocks
                const blockElements = blocks.map(block => {
                    const blockData = headerData.find(item => item.code.charAt(0) === block);
                    let statusColor = '#10B981'; // Default green
                    
                    if (blockData) {
                        // FIXED: Check progress for burned status, not infection
                        if (blockData.progress === 'burned') statusColor = '#FF0000'; // Red for burned
                        else if (blockData.infection_rate > 30) statusColor = '#EF4444'; // Red for high infection
                        else if (blockData.infection_rate > 10) statusColor = '#F59E0B'; // Orange for moderate
                    }
                    
                    return `<span style="color: ${statusColor}; font-weight: bold; margin: 0 2px;">${block}</span>`;
                }).join(' • ');
                
                blocksListElement.innerHTML = blockElements;
            }
        }

        function toggleFilter(filterType) {
            activeFilters[filterType] = !activeFilters[filterType];
            
            // Update button state
            const btn = document.getElementById('filter' + filterType.charAt(0).toUpperCase() + filterType.slice(1));
            if (btn) {
                if (activeFilters[filterType]) {
                    btn.style.opacity = '1';
                    btn.style.borderWidth = '2px';
                } else {
                    btn.style.opacity = '0.5';
                    btn.style.borderWidth = '1px';
                }
            }
            
            updateMarkers();
            updatePolygons(createBlockColorMap());
        }

        // Extract unique blocks and create color mapping
        function getUniqueBlocks() {
            const blocks = [...new Set(listData.map(item => item.code.charAt(0)))];
            return blocks.sort(); // A, B, C, etc.
        }

        function createBlockColorMap() {
            const blocks = getUniqueBlocks();
            const colors = [
                '#FF0000', '#00FF00', '#0000FF', '#FFFF00', 
                '#FF00FF', '#00FFFF', '#FFA500', '#800080',
                '#FFC0CB', '#A52A2A', '#808080', '#000080',
                '#008000', '#800000', '#808000', '#C0C0C0'
            ];
            
            const colorMap = {};
            blocks.forEach((block, index) => {
                colorMap[block] = colors[index % colors.length];
            });
            
            console.log('Block Color Mapping:', colorMap);
            return colorMap;
        }

        function filterByCodes(codes) {
            return listData.filter(item => item.code === codes);
        }

        // Fungsi untuk inisialisasi Google Maps
        function initMap() {
            try {
                // Center map pada rata-rata koordinat
                const centerLat = (headerData[0].lat + listData[0].lat) / 2;
                const centerLng = (headerData[0].lng + listData[0].lng) / 2;

                map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 8,
                    center: { lat: centerLat, lng: centerLng },
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    styles: [
                        {
                            featureType: "poi",
                            elementType: "labels",
                            stylers: [{ visibility: "on" }]
                        }
                    ]
                });

                // Create block color mapping
                const blockColorMap = createBlockColorMap();

                updateMarkers();
                updatePolygons(blockColorMap);
                updateBlocksList(); // Initialize blocks list display

                // Fit bounds untuk menampilkan semua data
                const bounds = new google.maps.LatLngBounds();
                [...headerData, ...listData].forEach(point => {
                    bounds.extend(new google.maps.LatLng(point.lat, point.lng));
                });
                map.fitBounds(bounds);
                //tambahan cio
                const currentZoom = map.getZoom();
                console.log(currentZoom,'zum');
                map.fitBounds(bounds, {
                    maxZoom: 6  // This sets the maximum zoom level
                });
                //

                // Display block legend in console
                console.log('Blocks found:', getUniqueBlocks());

            } catch (error) {
                console.error('Error initializing Google Maps:', error);
                document.getElementById('map').innerHTML = `
                    <div class="error">
                        <div>❌ Error loading Google Maps</div>
                        <div class="error-note">
                            Google Maps API tidak tersedia atau tidak dikonfigurasi dengan benar.
                        </div>
                    </div>
                `;
            }
        }

        // Fungsi toggle polygons
        function togglePolyline() {
            const toggleText = document.getElementById('toggleText');

            if (polylineVisible) {
                // Hide polygons
                polygons.forEach(polygon => polygon.setMap(null));
                toggleText.textContent = 'Tampilkan Polygon';
                polylineVisible = false;
            } else {
                // Show polygons
                polygons.forEach(polygon => polygon.setMap(map));
                toggleText.textContent = 'Sembunyikan Polygon';
                polylineVisible = true;
            }
        }

        // Fungsi untuk mengubah tipe peta
        function setMapType(type) {
            if (!map) return;

            // Update active button
            document.querySelectorAll('.map-type-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');

            // Set map type
            switch(type) {
                case 'roadmap':
                    map.setMapTypeId(google.maps.MapTypeId.ROADMAP);
                    break;
                case 'satellite':
                    map.setMapTypeId(google.maps.MapTypeId.SATELLITE);
                    break;
                case 'hybrid':
                    map.setMapTypeId(google.maps.MapTypeId.HYBRID);
                    break;
            }
        }

    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCc2vFD26wD5ox_5EwLJhR6U1jcfKibxBQ&callback=initMap">
    </script>
</x-layout>