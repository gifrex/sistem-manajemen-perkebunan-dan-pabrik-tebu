
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
    </style>
    <div class="container">
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
                    uploadPlaceholder.classList.add("invisible");
                    filePreview.classList.remove("invisible");
                    fileNameElement.textContent = file.name;
                } else {
                    uploadPlaceholder.classList.remove("invisible");
                    filePreview.classList.add("invisible");
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

        const headerData = [
          @foreach( $header as $item )
            { code: '{{ $item->plot }}', lat: {{ $item->centerlatitude }}, lng: {{ $item->centerlongitude }} },
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
        let polylineVisible = true;

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

                // Tambahkan marker untuk header data
                headerData.forEach((point, index) => {
                    const marker = new google.maps.Marker({
                        position: { lat: point.lat, lng: point.lng },
                        map: map,
                        title: `Header Marker - ${point.code}`,
                        icon: {
                            scale: 12,
                            fillColor: '#EA4335',
                            fillOpacity: 1,
                            strokeColor: '#ffffff',
                            strokeWeight: 3
                        }
                    });

                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div class="info-window">
                                <div class="info-title">📍 Header Marker</div>
                                <div class="info-detail">
                                    <strong>Code:</strong> ${point.code}<br>
                                    <strong>Latitude:</strong> ${point.lat.toFixed(6)}<br>
                                    <strong>Longitude:</strong> ${point.lng.toFixed(6)}
                                </div>
                            </div>
                        `
                    });

                    marker.addListener('click', () => {
                        infoWindow.open(map, marker);
                    });

                    markers.push(marker);
                });

                function filterByCodes(codes) {
                    return listData.filter(item => item.code === codes);
                }

                function getRandomColor() {
                    const colors = ['#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF'];
                    return colors[Math.floor(Math.random() * colors.length)];
                }

                headerData.forEach((point, index) => {
                    filtered = filterByCodes(point.code)

                    // Buat polyline dari list data
                    const polygonCoordinates = filtered.map(item => ({
                        lat: item.lat,
                        lng: item.lng
                    }));

                    polygon = new google.maps.Polygon({
                      paths: polygonCoordinates,
                      strokeColor: '#FF0000',      // Warna garis tepi
                      strokeOpacity: 0.8,          // Opacity garis tepi
                      strokeWeight: 2,             // Ketebalan garis tepi
                      fillColor: '#FF0000',        // Warna isi area
                      fillOpacity: 0.35
                    });

                    polygon.setMap(map);

                    polygon.setOptions({
                        fillColor: getRandomColor(),        // Ubah isi ke hijau
                        fillOpacity: 0.5,
                        strokeColor: getRandomColor()      // Ubah garis tepi ke hijau
                    });
                });


                // Fit bounds untuk menampilkan semua data
                const bounds = new google.maps.LatLngBounds();
                [...headerData, ...listData].forEach(point => {
                    bounds.extend(new google.maps.LatLng(point.lat, point.lng));
                });
                map.fitBounds(bounds);

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

        // Fungsi toggle polyline
        function togglePolyline() {
            if (!polyline) return;

            const toggleText = document.getElementById('toggleText');

            if (polylineVisible) {
                polyline.setMap(null);
                // Sembunyikan marker polyline
                markers.slice(1).forEach(marker => marker.setMap(null));
                toggleText.textContent = 'Tampilkan Polyline';
                polylineVisible = false;
            } else {
                polyline.setMap(map);
                // Tampilkan marker polyline
                markers.slice(1).forEach(marker => marker.setMap(map));
                toggleText.textContent = 'Sembunyikan Polyline';
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
