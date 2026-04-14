<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md">
        <div class="flex items-center justify-between mx-4 gap-2">

            @can('masterdata.company.create')
                <button onclick="openCreateModal()"
                    class="bg-blue-500 text-white px-4 py-2 text-sm border border-transparent shadow-sm font-medium rounded-md hover:bg-blue-600 flex items-center gap-2">
                    <svg class="w-5 h-5 text-white dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 12h14m-7 7V5" />
                    </svg>
                    <span class="text-sm">New Data</span>
                </button>
            @endcan
            <form method="POST" action="{{ url()->current() }}" class="flex items-center justify-end gap-2">
                @csrf
                <label for="perPage" class="text-xs font-medium text-gray-700">Items per page:</label>
                <input type="text" name="perPage" id="perPage" value="{{ $perPage }}" min="1"
                    onchange="this.form.submit()"
                    class="w-10 p-2 border border-gray-300 rounded-md text-xs text-center focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />

            </form>
        </div>

        <div class="mx-auto px-4 py-4">
            <div class="overflow-x-auto border border-gray-300 rounded-md">
                <table class="min-w-full bg-white text-sm text-center">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Kode
                                Company</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Nama
                                Company</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Alamat
                            </th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Periode
                            </th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Kode Gudang
                            </th>
                            @can('masterdata.company.edit')
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700 w-32">Aksi
                                </th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($companies as $item)
                            <tr>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->companycode }}</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->name }}</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->address }}</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->companyperiod }}</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->companyinventory }}</td>
                                @can('masterdata.company.edit')
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }} w-32">
                                        <div class="flex items-center justify-center">
                                            @can('masterdata.company.edit')
                                                <button
                                                    onclick="openEditModal('{{ $item->companycode }}', '{{ $item->name }}', '{{ $item->companyperiod }}', '{{ $item->address }}', '{{ $item->companyinventory }}')"
                                                    class="group flex items-center edit-button"><svg
                                                        class="w-6 h-6 text-blue-500 dark:text-white group-hover:hidden"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                        width="24" height="24" fill="none"
                                                        viewBox="0 0 24 24">
                                                        <path stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"
                                                            d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z" />
                                                    </svg>
                                                    <svg class="w-6 h-6 text-blue-500 dark:text-white hidden group-hover:block"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                        width="24" height="24" fill="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path fill-rule="evenodd"
                                                            d="M11.32 6.176H5c-1.105 0-2 .949-2 2.118v10.588C3 20.052 3.895 21 5 21h11c1.105 0 2-.948 2-2.118v-7.75l-3.914 4.144A2.46 2.46 0 0 1 12.81 16l-2.681.568c-1.75.37-3.292-1.263-2.942-3.115l.536-2.839c.097-.512.335-.983.684-1.352l2.914-3.086Z"
                                                            clip-rule="evenodd" />
                                                        <path fill-rule="evenodd"
                                                            d="M19.846 4.318a2.148 2.148 0 0 0-.437-.692 2.014 2.014 0 0 0-.654-.463 1.92 1.92 0 0 0-1.544 0 2.014 2.014 0 0 0-.654.463l-.546.578 2.852 3.02.546-.579a2.14 2.14 0 0 0 .437-.692 2.244 2.244 0 0 0 0-1.635ZM17.45 8.721 14.597 5.700 9.82 10.76a.54.54 0 0 0-.137.27l-.536 2.84c-.07.37.239.696.588.622l2.682-.567a.492.492 0 0 0 .255-.145l4.778-5.06Z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    <span class="w-0.5"></span>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mx-4 my-1">
            @if ($companies->hasPages())
                {{ $companies->appends(['perPage' => $companies->perPage()])->links() }}
            @else
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">{{ $companies->count() }}</span> of <span
                            class="font-medium">{{ $companies->total() }}</span> results
                    </p>
                </div>
            @endif
        </div>
    </div>

    <div id="crud-modal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 ease-out invisible opacity-0 transform scale-95"
        style="opacity: 0; transform: scale(0.95);">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div
                class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700 transition-transform duration-300 ease-out transform">
                <div
                    class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="modal-title">Create Data</h3>
                    <button type="button" onclick="closeModal()"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>

                <form class="p-4 md:p-5" id="crud-form" action="" method="POST">
                    @csrf

                    <input type="hidden" name="_method" id="crud-method" value="POST">

                    <div class="mb-4">
                        <label class="block">Kode Company</label>
                        <input type="text" id="companycode" name="companycode" value="" autocomplete="off"
                            maxlength="4" class="rounded-md p-2 w-full max-w-[12ch] border-gray-300" required>
                    </div>
                    <div class="mb-4">
                        <label class="block">Nama Company</label>
                        <input type="text" id="nama" name="nama" value="" autocomplete="off"
                            maxlength="50" class="rounded-md p-2 w-full max-w-[50ch] border-gray-300" required>
                    </div>
                    <div class="mb-4">
                        <label class="block">Periode Mulai</label>
                        <input type="date" id="tgl" name="tgl" placeholder="dd/mm/yyyy"
                            pattern="\d{2}/\d{2}/\d{4}" value=""
                            class="border rounded-md border-gray-300 p-2 w-full max-w-[50ch] placeholder-gray-400 text-gray-400 focus:text-black valid:text-black"
                            required>
                    </div>
                    <div class="mb-4">
                        <label class="block">Alamat</label>
                        <textarea id="alamat" name="alamat" class="border rounded-md border-gray-300 p-2 w-full max-w-[50ch]"
                            rows="5" value="" required></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block">Kode Gudang</label>
                        <input type="text" id="companyinventory" name="companyinventory" value="" autocomplete="off"
                            maxlength="4" class="rounded-md p-2 w-full max-w-[12ch] border-gray-300" required>
                    </div>
                    <div class="mt-6 flex gap-2">
                        <button type="submit"
                            class="text-white inline-flex space-x-2 items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            <svg class="w-6 h-6 text-white dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M5 11.917 9.724 16.5 19 7.5" />
                            </svg>
                            <span>Save</span>
                        </button>
                        <button type="button" onclick="closeModal()"
                            class="text-white inline-flex space-x-2 items-center bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                            <svg class="w-6 h-6 text-white dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />
                            </svg>
                            <span>Cancel</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .invisible {
            visibility: hidden;
            pointer-events: none;
        }

        .visible {
            visibility: visible;
            pointer-events: auto;
        }
    </style>
    <script>
        const modal = document.getElementById("crud-modal");
        const form = document.getElementById("crud-form");
        const modalTitle = document.getElementById("modal-title");
        const kdCompInput = document.getElementById("companycode");
        const namaInput = document.getElementById("nama");
        const tglInput = document.getElementById("tgl");
        const alamatInput = document.getElementById("alamat");
        const crudMethod = document.getElementById("crud-method");

        function openCreateModal() {
            modal.classList.remove("invisible");
            modal.classList.add("visible");
            modalTitle.textContent = "Create Data";
            form.action = "{{ route('masterdata.company.handle') }}";
            crudMethod.value = "POST";
            kdCompInput.value = "";
            kdCompInput.readOnly = false;
            namaInput.value = "";
            tglInput.value = "";
            alamatInput.value = "";
            companyinventory.value = "";
            setTimeout(() => {
                modal.style.opacity = "1";
                modal.style.transform = "scale(1)";
            }, 50);
        }

        function openEditModal(kdComp, nama, tgl, alamat, inventory) {
            modal.classList.remove("invisible");
            modal.classList.add("visible");
            modalTitle.textContent = "Edit Data";
            var editRoute = "{{ route('masterdata.company.update', ['companycode' => '__kdComp__']) }}";
            form.action = editRoute.replace('__kdComp__', kdComp);
            crudMethod.value = "PUT";
            kdCompInput.value = kdComp;
            kdCompInput.readOnly = true;
            namaInput.value = nama;
            tglInput.value = tgl;
            alamatInput.value = alamat;
            companyinventory.value = inventory;
            setTimeout(() => {
                modal.style.opacity = "1";
                modal.style.transform = "scale(1)";
            }, 50);
        }

        function closeModal() {
            modal.style.opacity = "0";
            modal.style.transform = "scale(0.95)";

            setTimeout(() => {
                modal.classList.add("invisible");
                modal.classList.remove("visible");
            }, 300);
        }

        form.addEventListener("submit", function(event) {
            if (crudMethod.value === "POST") {
                event.preventDefault();
            }
            const formData = new FormData(form);
            fetch(form.action, {
                    method: crudMethod.value,
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Data berhasil disimpan!");
                        updateTable(data.newData);
                        form.reset();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error("Error:", error));
        });

        function updateTable(newData) {
            const tableBody = document.querySelector("tbody");

            let newRow = document.createElement("tr");
            newRow.innerHTML = `
                <td class="py-2 px-4 border-b border-gray-300">
                    ${newData.companycode}
                </td>
                <td class="py-2 px-4 border-b border-gray-300">${newData.name}</td>
                <td class="py-2 px-4 border-b border-gray-300">${newData.address}</td>
                <td class="py-2 px-4 border-b border-gray-300">${newData.companyperiod}</td>
                <td class="py-2 px-4 border-b border-gray-300">${newData.companyinventory}</td>
                @can('masterdata.company.edit')
                <td class="py-2 px-4 border-b border-gray-300">
                    <div class="flex items-center justify-center">
                        <button class="group flex items-center edit-button" onclick="openEditModal('${newData.companycode}', '${newData.name}', '${newData.companyperiod}', '${newData.address}', '${newData.companyinventory}')">
                            <svg
                                class="w-6 h-6 text-blue-500 dark:text-white group-hover:hidden"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="2"
                                    d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z" />
                            </svg>
                            <svg class="w-6 h-6 text-blue-500 dark:text-white hidden group-hover:block"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" fill="currentColor"
                                viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M11.32 6.176H5c-1.105 0-2 .949-2 2.118v10.588C3 20.052 3.895 21 5 21h11c1.105 0 2-.948 2-2.118v-7.75l-3.914 4.144A2.46 2.46 0 0 1 12.81 16l-2.681.568c-1.75.37-3.292-1.263-2.942-3.115l.536-2.839c.097-.512.335-.983.684-1.352l2.914-3.086Z"
                                    clip-rule="evenodd" />
                                <path fill-rule="evenodd"
                                    d="M19.846 4.318a2.148 2.148 0 0 0-.437-.692 2.014 2.014 0 0 0-.654-.463 1.92 1.92 0 0 0-1.544 0 2.014 2.014 0 0 0-.654.463l-.546.578 2.852 3.02.546-.579a2.14 2.14 0 0 0 .437-.692 2.244 2.244 0 0 0 0-1.635ZM17.45 8.721 14.597 5.700 9.82 10.76a.54.54 0 0 0-.137.27l-.536 2.84c-.07.37.239.696.588.622l2.682-.567a.492.492 0 0 0 .255-.145l4.778-5.06Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="w-0.5"></span>
                        </button>
                    </div>
                </td>
                @endcan
            `;
            tableBody.prepend(newRow);
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const inputElement = document.getElementById("perPage");

            inputElement.addEventListener("input", (event) => {
                event.target.value = event.target.value.replace(/[^0-9]/g, '');
            });
        });
    </script>
</x-layout>