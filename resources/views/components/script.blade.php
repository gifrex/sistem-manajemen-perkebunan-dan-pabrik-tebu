<script>
    window.addEventListener('load', function() {

        updateNavbarDot();
    });

    function updateNavbarDot() {
        const url = '{{ route('info-updates.notifications.unread-count') }}';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const navbarDots = [
            document.getElementById('notification-dot'),
            document.getElementById('notification-dot-mobile')
        ];

        fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                const unreadCount = data.unread_count || 0;
                const isNotificationPage = window.location.pathname.includes(
                    '{{ route('info-updates.notifications.index') }}');
                const dialog = document.getElementById('unread-notification-dialog');

                if (dialog) {
                    unreadCount > 0 && !isNotificationPage ? showNotificationDialog() : dismissNotificationDialog();
                }

                navbarDots.forEach(dot => {
                    if (dot) {
                        dot.textContent = unreadCount;
                        dot.style.display = unreadCount > 0 ? 'inline-block' : 'none';
                    }
                });
            })
            .catch(error => console.error('Error fetching unread notifications count:', error));
    }


    function showNotificationDialog() {
        const dialog = document.getElementById('unread-notification-dialog');
        dialog.classList.remove('invisible', 'opacity-0');
        dialog.classList.add('opacity-100');
    }

    function dismissNotificationDialog() {
        const dialog = document.getElementById('unread-notification-dialog');
        const wrapper = document.getElementById('notification-wrapper');
        dialog.classList.add('invisible', 'opacity-0');
        dialog.classList.remove('opacity-100');
        dialog.style.display = 'none';
        wrapper.style.display = 'none';
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let paginationContainer = document.getElementById("pagination-links");

        if (paginationContainer) {
            paginationContainer.addEventListener("click", function(event) {
                event.preventDefault();

                let target = event.target;
                if (target.tagName === "A") {
                    let url = target.href;
                    fetchData(url);
                }
            });
        }

        function fetchData(url) {
            fetch(url, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                .then(response => response.text())
                .then(html => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, "text/html");
                    let newTable = doc.querySelector("#tables");
                    document.querySelector("#tables").innerHTML = newTable.innerHTML;

                    let newPagination = doc.querySelector("#pagination-links");
                    document.querySelector("#pagination-links").innerHTML = newPagination.innerHTML;
                })
                .catch(error => console.error("Error fetching data:", error));
        }
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const dataContainer = document.getElementById("ajax-data");
        if (!dataContainer) return;

        const baseUrl = dataContainer.dataset.url;
        const searchInput = document.getElementById("search");
        const perPageInput = document.getElementById("perPage");
        const startDateInput = document.getElementById("start_date");
        const endDateInput = document.getElementById("end_date");
        const tables = document.getElementById("tables");
        const pages = document.getElementById("pagination-links");

        let timeout = null;

        const exportConfigs = {
            'hpt-export': {
                baseUrl: '{{ route('transaction.hpt.exportExcel') }}',
                buttonSelector: '[data-export="hpt"]'
            },
            'agronomi-export': {
                baseUrl: '{{ route('transaction.agronomi.exportExcel') }}',
                buttonSelector: '[data-export="agronomi"]'
            }
        };

        function updateAllExportUrls() {
            const startDate = startDateInput ? startDateInput.value : "";
            const endDate = endDateInput ? endDateInput.value : "";
            const search = searchInput ? searchInput.value : "";


            Object.keys(exportConfigs).forEach(configKey => {
                const config = exportConfigs[configKey];
                let exportUrl = config.baseUrl;
                const params = [];

                if (startDate) params.push(`start_date=${encodeURIComponent(startDate)}`);
                if (endDate) params.push(`end_date=${encodeURIComponent(endDate)}`);
                if (search) params.push(`search=${encodeURIComponent(search)}`);

                if (params.length > 0) {
                    exportUrl += '?' + params.join('&');
                }

                document.querySelectorAll(config.buttonSelector).forEach(button => {
                    button.onclick = function() {
                        window.location.href = exportUrl;
                    };
                });
            });
        }

        function showTableLoading() {
            const tables = document.getElementById("tables");
            if (!tables) return;

            // Pastikan parent element punya position relative sebagai anchor overlay
            const wrapper = tables.closest(".overflow-x-auto") || tables.parentElement;
            if (!wrapper) return;

            // Hindari duplikasi overlay
            if (wrapper.querySelector("#table-loading-overlay")) return;

            // Set position relative pada wrapper agar overlay bisa absolute di dalamnya
            const prevPosition = wrapper.style.position;
            wrapper.style.position = "relative";
            wrapper.dataset.prevPosition = prevPosition;

            const overlay = document.createElement("div");
            overlay.id = "table-loading-overlay";
            overlay.innerHTML = `
                <div class="flex flex-col items-center justify-center gap-3">
                    <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-gray-600 text-sm font-semibold tracking-wide">Memuat data...</span>
                </div>
            `;

            Object.assign(overlay.style, {
                position: "absolute",
                inset: "0",
                top: "0",
                left: "0",
                right: "0",
                bottom: "0",
                display: "flex",
                alignItems: "center",
                justifyContent: "center",
                backgroundColor: "rgba(255, 255, 255, 0.75)",
                backdropFilter: "blur(2px)",
                zIndex: "50",
                borderRadius: "inherit",
                minHeight: "100px",
            });

            wrapper.appendChild(overlay);
        }

        function hideTableLoading() {
            const overlay = document.getElementById("table-loading-overlay");
            if (!overlay) return;

            const wrapper = overlay.parentElement;
            overlay.remove();

            if (wrapper) {
                wrapper.style.position = wrapper.dataset.prevPosition || "";
                delete wrapper.dataset.prevPosition;
            }
        }

        function fetchData(url = baseUrl) {
            showTableLoading();

            const search = searchInput ? searchInput.value : "";
            const perPage = perPageInput ? perPageInput.value : 10;
            const startDate = startDateInput ? startDateInput.value : "";
            const endDate = endDateInput ? endDateInput.value : "";

            const formData = new FormData();
            formData.append("_token", document.querySelector('meta[name="csrf-token"]').content);
            formData.append("search", search);
            formData.append("perPage", perPage);
            formData.append("start_date", startDate);
            formData.append("end_date", endDate);

            fetch(url, {
                    method: "POST",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(html, "text/html");

                    const newTable = newDoc.querySelector("#tables");
                    const newPagination = newDoc.querySelector("#pagination-links");

                    if (newTable && newPagination) {
                        tables.innerHTML = newTable.innerHTML;
                        pages.innerHTML = newPagination.innerHTML;
                    }

                    hideTableLoading();
                    updateAllExportUrls();
                })
                .catch(error => {
                    console.error("AJAX Fetch Error:", error);
                    hideTableLoading();
                });
        }

        window._triggerAjaxFetch = fetchData;


        if (searchInput) {
            searchInput.addEventListener("input", () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    fetchData();
                    updateAllExportUrls();
                }, 300);
            });
        }

        if (perPageInput) {
            perPageInput.addEventListener("input", () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    fetchData();
                    updateAllExportUrls();
                }, 300);
            });
        }

        const applyFilterBtn = document.getElementById("btn-apply-filter");
        if (applyFilterBtn) {
            applyFilterBtn.addEventListener("click", function() {
                fetchData();
                updateAllExportUrls();
            });
        }


        document.addEventListener("click", function(event) {
            const target = event.target.closest("#pagination-links a");
            if (target) {
                event.preventDefault();
                fetchData(target.href);
            }
        });

        updateAllExportUrls();
    });
</script>

<script>
    if (document.getElementById("sorting-table") && typeof simpleDatatables.DataTable !== 'undefined') {
        const dataTable = new simpleDatatables.DataTable("#sorting-table", {
            searchable: false,
            perPageSelect: false,
            ordering: true,
            paging: false
        });
    }
</script>

<script>
    document.querySelectorAll('.numeric-input').forEach((input) => {
        input.addEventListener('input', function(e) {
            const value = e.target.value;
            if (!/^\d*\.?\d{0,2}$/.test(value)) {
                e.target.value = value.slice(0, -1);
            }
        });
    });
</script>

<script>
    window.addEventListener('scroll', function() {
        const scrollToTopButton = document.getElementById('scrollToTop');
        if (!scrollToTopButton) return;

        scrollToTopButton.style.display = (window.scrollY > 100) ? 'block' : 'none';
    });

    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
</script>
