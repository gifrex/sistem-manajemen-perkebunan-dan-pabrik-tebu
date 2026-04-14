<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div 
        x-data="{
            open: @json($errors->any()),
            mode: 'create',
            form: { 
                companycode: '{{ session('companycode') }}', 
                blok: ''
            },
            resetForm() {
                this.mode = 'create';
                this.form = { 
                    companycode: '{{ session('companycode') }}', 
                    blok: ''
                };
                this.open = true;
            }
        }"
        class="mx-auto py-4 bg-white shadow-md rounded-md">

        <div class="flex items-center justify-between mx-4 gap-2">
            @can('masterdata.blok.create')
                <button @click="resetForm()"
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
                    onchange="this.form.submit()" autocomplete="off"
                    class="w-10 p-2 border border-gray-300 rounded-md text-xs text-center focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
            </form>
        </div>

        <div class="mx-auto px-4 py-4">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm text-center">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700 w-1">No.</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Blok</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Kode Company</th>
                            @canany(['masterdata.blok.edit', 'masterdata.blok.delete'])
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700 w-36">Aksi</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($blok as $item)
                            <tr>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300 w-1' }}">
                                    {{ $item->no }}.</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->blok }}</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->companycode }}</td>
                                @canany(['masterdata.blok.edit', 'masterdata.blok.delete'])
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300 w-36' }}">
                                        <div class="flex items-center justify-center">
                                            @can('masterdata.blok.edit')
                                                <button
                                                    @click="
                                                        mode = 'edit';
                                                        form.blokOriginal = '{{ $item->blok }}';
                                                        form.companycodeOriginal = '{{ $item->companycode }}';
                                                        form.blok = '{{ $item->blok }}';
                                                        form.companycode = '{{ $item->companycode }}';
                                                        open = true
                                                    "
                                                    class="group flex items-center edit-button">
                                                    <svg class="w-6 h-6 text-blue-500 dark:text-white group-hover:hidden"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                        <path stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"
                                                            d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z" />
                                                    </svg>
                                                    <svg class="w-6 h-6 text-blue-500 dark:text-white hidden group-hover:block"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                        width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                                        <path fill-rule="evenodd"
                                                            d="M11.32 6.176H5c-1.105 0-2 .949-2 2.118v10.588C3 20.052 3.895 21 5 21h11c1.105 0 2-.948 2-2.118v-7.75l-3.914 4.144A2.46 2.46 0 0 1 12.81 16l-2.681.568c-1.75.37-3.292-1.263-2.942-3.115l.536-2.839c.097-.512.335-.983.684-1.352l2.914-3.086Z"
                                                            clip-rule="evenodd" />
                                                        <path fill-rule="evenodd"
                                                            d="M19.846 4.318a2.148 2.148 0 0 0-.437-.692 2.014 2.014 0 0 0-.654-.463 1.92 1.92 0 0 0-1.544 0 2.014 2.014 0 0 0-.654.463l-.546.578 2.852 3.02.546-.579a2.14 2.14 0 0 0 .437-.692 2.244 2.244 0 0 0 0-1.635ZM17.45 8.721 14.597 5.7 9.82 10.76a.54.54 0 0 0-.137.27l-.536 2.84c-.07.37.239.696.588.622l2.682-.567a.492.492 0 0 0 .255-.145l4.778-5.06Z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    <span class="w-0.5"></span>
                                                </button>
                                            @endcan
                                            @can('masterdata.blok.delete')
                                                <form 
                                                    action="{{ route('masterdata.blok.destroy', ['blok' => $item->blok, 'companycode' => $item->companycode]) }}" 
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus data ini?');"
                                                    class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="group flex delete-button">
                                                        <svg class="w-6 h-6 text-red-500 dark:text-white group-hover:hidden"
                                                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                            width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                            <path stroke="currentColor" stroke-linecap="round"
                                                                stroke-linejoin="round" stroke-width="2"
                                                                d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                                                        </svg>
                                                        <svg class="w-6 h-6 text-red-500 dark:text-white hidden group-hover:block"
                                                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                            width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                                            <path fill-rule="evenodd"
                                                                d="M8.586 2.586A2 2 0 0 1 10 2h4a2 2 0 0 1 2 2v2h3a1 1 0 1 1 0 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a1 1 0 0 1 0-2h3V4a2 2 0 0 1 .586-1.414ZM10 6h4V4h-4v2Zm1 4a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Zm4 0a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                @endcanany
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mx-4 my-1">
            @if ($blok->hasPages())
                {{ $blok->appends(['perPage' => $blok->perPage()])->links() }}
            @else
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">{{ $blok->count() }}</span> of <span
                            class="font-medium">{{ $blok->total() }}</span> results
                    </p>
                </div>
            @endif
        </div>

        {{-- Modal - Form --}}
        <div x-show="open" x-cloak class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Modal - Backdrop --}}
            <div x-show="open" x-transition.opacity
                class="fixed inset-0 bg-gray-500/75" aria-hidden="true"></div>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="open"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg">

                        <form method="POST"
                            :action="mode === 'edit'
                                ? '{{ route('masterdata.blok.update', ['blok' => '__blok__', 'companycode' => '__companycode__']) }}'.replace('__blok__', form.blokOriginal).replace('__companycode__', form.companycodeOriginal)
                                : '{{ route('masterdata.blok.handle') }}'"
                            class="bg-white px-4 pt-2 pb-4 sm:p-6 sm:pt-1 sm:pb-4 space-y-6">
                            @csrf
                            <template x-if="mode === 'edit'">
                                <input type="hidden" name="_method" value="PUT">
                            </template>

                            <div class="text-center sm:text-left">
                                <h3 class="text-lg font-medium text-gray-900" id="modal-title" x-text="mode === 'edit' ? 'Edit Blok' : 'Create Blok'">
                                </h3>
                                <div class="mt-4 space-y-4">
                                    {{-- Company Code - Hidden --}}
                                    <div>
                                        <label for="companycode" class="block text-sm font-medium text-gray-700">Kode Company</label>
                                        <input type="hidden" name="companycode" x-model="form.companycode">
                                        <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm text-gray-700 font-medium"
                                             x-text="form.companycode"></div>
                                    </div>

                                    <div>
                                        <label for="blok" class="block text-sm font-medium text-gray-700">Kode Blok</label>
                                        <input type="text" name="blok" id="blok" x-model="form.blok" 
                                            x-init="form.blok = '{{ old('blok') }}'"
                                            @input="form.blok = form.blok.toUpperCase()"
                                            class="mt-1 block w-1/2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 uppercase"
                                            maxlength="2" required>
                                        @error('blok')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit"
                                        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-4 py-2 text-white text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto"
                                        x-text="mode === 'edit' ? 'Update' : 'Save'">
                                    Save
                                </button>
                                <button @click.prevent="open = false" type="button"
                                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-gray-700 text-sm font-medium shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Toast Notification --}}
        @if (session('success1'))
            <div x-data x-init="alert('{{ session('success1') }}')"></div>
        @endif
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const inputElement = document.getElementById("perPage");
            inputElement.addEventListener("input", (event) => {
                event.target.value = event.target.value.replace(/[^0-9]/g, '');
            });
        });
    </script>
</x-layout>