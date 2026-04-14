<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $title }}</x-slot:nav>

    <div class="max-w-4xl mx-auto p-6">
        <!-- Success Notification -->
        @if (session('success1'))
            <div id="successNotification"
                class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-xl shadow-lg overflow-hidden animate-slide-down">
                <div class="p-4 flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div
                            class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center animate-scale-in">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 pt-1">
                        <h3 class="text-lg font-semibold text-green-800 mb-1">Upload Successful! 🎉</h3>
                        <p class="text-green-700 text-sm">{{ session('success1') }}</p>
                    </div>
                    <button onclick="closeNotification()"
                        class="flex-shrink-0 text-green-500 hover:text-green-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="h-1 bg-green-200">
                    <div class="h-full bg-green-500 animate-progress-timer"></div>
                </div>
            </div>
        @endif

        <!-- Error Notification -->
        @if (session('error') || $errors->any())
            <div id="errorNotification"
                class="mb-6 bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 rounded-xl shadow-lg overflow-hidden animate-slide-down">
                <div class="p-4 flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div
                            class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center animate-scale-in">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 pt-1">
                        <h3 class="text-lg font-semibold text-red-800 mb-1">Upload Failed</h3>
                        <p class="text-red-700 text-sm">
                            @if (session('error'))
                                {{ session('error') }}
                            @else
                                {{ $errors->first() }}
                            @endif
                        </p>
                    </div>
                    <button onclick="closeErrorNotification()"
                        class="flex-shrink-0 text-red-500 hover:text-red-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="h-1 bg-red-200">
                    <div class="h-full bg-red-500 animate-progress-timer"></div>
                </div>
            </div>
        @endif

        <!-- Header Section -->
        <div class="mb-8 text-center">
            <div
                class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-green-400 to-green-600 rounded-2xl shadow-lg mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Upload GPX File</h1>
            <p class="text-gray-600">Import your GPS tracking data to visualize on the map</p>
        </div>

        <!-- Upload Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <h2 class="text-lg font-semibold text-gray-800">Select File</h2>
                </div>
            </div>

            <form action="{{ route('process.uploadgpx.submit') }}" method="post" enctype="multipart/form-data"
                onsubmit="return handleSubmit(event)" class="p-6" id="uploadForm">
                @csrf

                <!-- Upload Area -->
                <div class="mb-6">
                    <div class="flex items-center justify-center w-full">
                        <label for="gpxFile"
                            class="group flex flex-col items-center justify-center w-full h-80 border-3 border-gray-300 border-dashed rounded-2xl cursor-pointer bg-gradient-to-br from-gray-50 to-white hover:from-green-50 hover:to-emerald-50 transition-all duration-300 ease-in-out relative overflow-hidden hover:border-green-400 hover:shadow-lg">

                            <!-- Animated Background Pattern -->
                            <div class="absolute inset-0 opacity-5 group-hover:opacity-10 transition-opacity">
                                <div class="absolute inset-0"
                                    style="background-image: radial-gradient(circle at 2px 2px, rgb(34 197 94) 1px, transparent 0); background-size: 32px 32px;">
                                </div>
                            </div>

                            <!-- Default Content -->
                            <div id="uploadPlaceholder"
                                class="flex flex-col items-center justify-center pt-5 pb-6 transition-all duration-300 relative z-10">

                                <!-- Icon with animation -->
                                <div class="relative mb-6">
                                    <div
                                        class="absolute inset-0 bg-green-500 rounded-full blur-xl opacity-20 group-hover:opacity-40 transition-opacity animate-pulse">
                                    </div>
                                    <div
                                        class="relative bg-white rounded-full p-6 shadow-lg group-hover:shadow-xl transition-all group-hover:scale-110">
                                        <svg class="w-16 h-16 text-green-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Text Content -->
                                <div class="text-center px-4">
                                    <p class="mb-3 text-lg font-semibold text-gray-700">
                                        <span class="text-green-600">Click to upload</span> or drag and drop
                                    </p>
                                    <p class="text-sm text-gray-500 mb-4">Support for GPX format files</p>

                                    <!-- File Requirements -->
                                    <div
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-sm border border-gray-200">
                                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-xs font-medium text-gray-600">Max file size: 20MB</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview File Container -->
                            <div id="filePreview"
                                class="invisible absolute inset-0 flex flex-col items-center justify-center transition-all duration-300 bg-gradient-to-br from-green-50 to-emerald-50 z-10">

                                <!-- Success Icon -->
                                <div class="relative mb-4">
                                    <div
                                        class="absolute inset-0 bg-green-500 rounded-full blur-xl opacity-30 animate-pulse">
                                    </div>
                                    <div class="relative bg-white rounded-full p-6 shadow-xl">
                                        <svg class="w-16 h-16 text-green-500" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- File Info -->
                                <div class="text-center px-6">
                                    <p class="text-sm text-gray-500 mb-2 font-medium">File selected:</p>
                                    <p id="fileName" class="text-lg text-gray-800 font-semibold mb-1 break-all"></p>
                                    <p id="fileSize" class="text-sm text-gray-600"></p>
                                </div>

                                <!-- Change File Button -->
                                <button type="button" onclick="document.getElementById('gpxFile').click()"
                                    class="mt-6 px-6 py-2 bg-white text-green-600 rounded-full font-medium shadow-md hover:shadow-lg transition-all hover:scale-105 border border-green-200">
                                    Change File
                                </button>
                            </div>

                            <!-- Processing Overlay -->
                            <div id="processingOverlay"
                                class="invisible absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br from-green-500 to-emerald-600 z-20 transition-all duration-300">

                                <!-- Animated Upload Icon -->
                                <div class="relative mb-6">
                                    <!-- Spinning circles -->
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-32 h-32 border-4 border-white/30 rounded-full"></div>
                                    </div>
                                    <div class="absolute inset-0 flex items-center justify-center animate-spin-slow">
                                        <div class="w-32 h-32 border-4 border-transparent border-t-white rounded-full">
                                        </div>
                                    </div>

                                    <!-- Center Icon -->
                                    <div class="relative bg-white rounded-full p-8 shadow-2xl animate-pulse-slow">
                                        <svg class="w-16 h-16 text-green-500 animate-bounce-slow" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3v-5" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Progress Text -->
                                <div class="text-center text-white px-6">
                                    <h3 class="text-2xl font-bold mb-2 animate-pulse">Processing...</h3>
                                    <p class="text-white/90 text-sm mb-6" id="progressText">Uploading your GPX file
                                    </p>

                                    <!-- Progress Bar -->
                                    <div class="w-64 h-2 bg-white/20 rounded-full overflow-hidden mb-4">
                                        <div class="h-full bg-white rounded-full animate-progress"></div>
                                    </div>

                                    <!-- Processing Steps -->
                                    <div class="flex items-center justify-center gap-2 text-sm">
                                        <div class="flex items-center gap-2 opacity-70">
                                            <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                                            <span>Validating</span>
                                        </div>
                                        <span class="opacity-50">•</span>
                                        <div class="flex items-center gap-2 opacity-70">
                                            <div class="w-2 h-2 bg-white rounded-full animate-pulse"
                                                style="animation-delay: 0.2s"></div>
                                            <span>Parsing</span>
                                        </div>
                                        <span class="opacity-50">•</span>
                                        <div class="flex items-center gap-2 opacity-70">
                                            <div class="w-2 h-2 bg-white rounded-full animate-pulse"
                                                style="animation-delay: 0.4s"></div>
                                            <span>Importing</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input id="gpxFile" type="file" name="gpxFile" class="hidden" accept=".gpx" />
                        </label>
                    </div>
                </div>

                <!-- Info Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="flex items-start gap-3 p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-sm text-gray-800 mb-1">Valid Format</h3>
                            <p class="text-xs text-gray-600">Only .gpx files accepted</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-4 bg-purple-50 rounded-xl border border-purple-100">
                        <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-sm text-gray-800 mb-1">File Size</h3>
                            <p class="text-xs text-gray-600">Maximum 20MB per file</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-4 bg-amber-50 rounded-xl border border-amber-100">
                        <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-sm text-gray-800 mb-1">Fast Process</h3>
                            <p class="text-xs text-gray-600">Quick upload & parsing</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" id="submitBtn"
                        class="group relative px-8 py-3 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-lg text-white font-semibold hover:from-green-600 hover:to-emerald-700 transition-all duration-300 hover:shadow-xl hover:scale-105 overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">

                        <!-- Button shimmer effect -->
                        <div
                            class="absolute inset-0 w-1/2 h-full bg-gradient-to-r from-transparent via-white to-transparent opacity-0 group-hover:opacity-30 transform -skew-x-12 group-hover:animate-shimmer">
                        </div>

                        <span class="flex items-center gap-3 relative z-10">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" id="submitIcon">
                                <path
                                    d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 00-1-1h-.5a1.5 1.5 0 010-3H4a1 1 0 001-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z" />
                            </svg>
                            <span id="submitText">Upload & Process</span>
                            <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24" id="arrowIcon">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Help Section -->
        <div class="mt-8 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-800 mb-2">Need Help?</h3>
                    <p class="text-sm text-gray-600 mb-3">
                        GPX files contain GPS tracking data including coordinates, elevation, and timestamps.
                        Make sure your file is properly formatted and doesn't exceed the size limit.
                    </p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Supported format: .gpx only</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Maximum file size: 20 megabytes</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>File will be validated before processing</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes shimmer {
            0% {
                transform: translateX(-100%) skewX(-12deg);
            }

            100% {
                transform: translateX(300%) skewX(-12deg);
            }
        }

        .group:hover .group-hover\:animate-shimmer {
            animation: shimmer 1.5s infinite;
        }

        @keyframes spin-slow {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin-slow {
            animation: spin-slow 3s linear infinite;
        }

        @keyframes pulse-slow {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }
        }

        .animate-pulse-slow {
            animation: pulse-slow 2s ease-in-out infinite;
        }

        @keyframes bounce-slow {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .animate-bounce-slow {
            animation: bounce-slow 2s ease-in-out infinite;
        }

        @keyframes progress {
            0% {
                width: 0%;
            }

            100% {
                width: 100%;
            }
        }

        .animate-progress {
            animation: progress 2s ease-in-out infinite;
        }

        @keyframes slide-down {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-down {
            animation: slide-down 0.5s ease-out;
        }

        @keyframes scale-in {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }

        .animate-scale-in {
            animation: scale-in 0.4s ease-out;
        }

        @keyframes progress-timer {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        .animate-progress-timer {
            animation: progress-timer 5s linear forwards;
        }

        @keyframes fade-out {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }

        .animate-fade-out {
            animation: fade-out 0.3s ease-out forwards;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const fileInput = document.getElementById("gpxFile");
            const dropArea = document.querySelector("label[for='gpxFile']");
            const uploadPlaceholder = document.getElementById("uploadPlaceholder");
            const filePreview = document.getElementById("filePreview");
            const fileNameElement = document.getElementById("fileName");
            const fileSizeElement = document.getElementById("fileSize");

            const formatFileSize = (bytes) => {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
            };

            const handleFile = (file) => {
                if (file) {
                    uploadPlaceholder.classList.add("invisible");
                    filePreview.classList.remove("invisible");
                    fileNameElement.textContent = file.name;
                    fileSizeElement.textContent = formatFileSize(file.size);
                } else {
                    uploadPlaceholder.classList.remove("invisible");
                    filePreview.classList.add("invisible");
                }
            };

            fileInput.addEventListener("change", (e) => handleFile(e.target.files[0]));

            dropArea.addEventListener("dragover", (e) => {
                e.preventDefault();
                dropArea.classList.add("border-green-500", "bg-green-50");
            });

            dropArea.addEventListener("dragleave", () => {
                dropArea.classList.remove("border-green-500", "bg-green-50");
            });

            dropArea.addEventListener("drop", (e) => {
                e.preventDefault();
                dropArea.classList.remove("border-green-500", "bg-green-50");

                const file = e.dataTransfer.files[0];
                if (file) {
                    fileInput.files = e.dataTransfer.files;
                    handleFile(file);
                }
            });

            // Auto close notifications after 5 seconds
            const successNotification = document.getElementById('successNotification');
            if (successNotification) {
                setTimeout(() => {
                    closeNotification();
                }, 5000);
            }

            const errorNotification = document.getElementById('errorNotification');
            if (errorNotification) {
                setTimeout(() => {
                    closeErrorNotification();
                }, 5000);
            }
        });

        function closeNotification() {
            const notification = document.getElementById('successNotification');
            if (notification) {
                notification.classList.add('animate-fade-out');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }
        }

        function closeErrorNotification() {
            const notification = document.getElementById('errorNotification');
            if (notification) {
                notification.classList.add('animate-fade-out');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }
        }

        function handleSubmit(event) {
            event.preventDefault();

            const file = document.getElementById("gpxFile").files[0];
            if (!file) {
                showCustomAlert("No file selected. Please choose a GPX file to upload.", "error");
                return false;
            }

            const fileExtension = file.name.split(".").pop().toLowerCase();
            if (file.type !== "application/gpx+xml" && fileExtension !== "gpx") {
                showCustomAlert("Invalid file type. Please upload a valid GPX file.", "error");
                return false;
            }

            if (file.size > 20 * 1024 * 1024) {
                showCustomAlert("File size exceeds the maximum limit of 20MB. Please choose a smaller file.", "error");
                return false;
            }

            // Show processing overlay
            const processingOverlay = document.getElementById("processingOverlay");
            const filePreview = document.getElementById("filePreview");
            const submitBtn = document.getElementById("submitBtn");

            filePreview.classList.add("invisible");
            processingOverlay.classList.remove("invisible");
            submitBtn.disabled = true;

            // Animate progress text
            const progressTexts = [
                "Uploading your GPX file...",
                "Validating file format...",
                "Parsing GPS coordinates...",
                "Importing data to database...",
                "Almost done..."
            ];

            let textIndex = 0;
            const progressTextElement = document.getElementById("progressText");

            const textInterval = setInterval(() => {
                textIndex = (textIndex + 1) % progressTexts.length;
                progressTextElement.textContent = progressTexts[textIndex];
            }, 1500);

            // Submit form
            const form = document.getElementById("uploadForm");

            // Clear interval when page unloads
            window.addEventListener('beforeunload', () => {
                clearInterval(textInterval);
            });

            form.submit();

            return true;
        }

        function showCustomAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `fixed top-4 right-4 z-50 max-w-md p-4 rounded-xl shadow-2xl animate-slide-down ${
                type === 'error' 
                ? 'bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500' 
                : 'bg-gradient-to-r from-blue-50 to-cyan-50 border-l-4 border-blue-500'
            }`;

            alertDiv.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 ${type === 'error' ? 'bg-red-500' : 'bg-blue-500'} rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                ${type === 'error' 
                                    ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />'
                                    : '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />'
                                }
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium ${type === 'error' ? 'text-red-800' : 'text-blue-800'}">${message}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="flex-shrink-0 ${type === 'error' ? 'text-red-500 hover:text-red-700' : 'text-blue-500 hover:text-blue-700'}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            `;

            document.body.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.classList.add('animate-fade-out');
                setTimeout(() => alertDiv.remove(), 300);
            }, 3000);
        }
    </script>
</x-layout>
