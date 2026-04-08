<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div 
    x-data="{
      open: @json($errors->any()),
      mode: 'create',
      form: { 
        companycode:'{{ session('companycode') }}', 
        id: '', 
        kontraktorid: '',
        namasubkontraktor: ''
      },
      resetForm() {
        this.mode = 'create';
        this.form = { 
          companycode:'{{ session('companycode') }}', 
          id: '', 
          kontraktorid: '',
          namasubkontraktor: ''
        };
        this.open = true;
      }
    }"
    class="mx-auto py-1 bg-white rounded-md shadow-md">

    <div class="flex items-center justify-between px-4 py-2">

      {{-- Create Button --}}
      @can('masterdata.subkontraktor.create')
        <button @click="resetForm()"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2">
          <svg class="w-5 h-5 text-white dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                  width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M5 12h14m-7 7V5" />
          </svg> New Data
        </button>
      @endcan
        
      {{-- Search Form --}}
      <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        <label for="search" class="text-xs font-medium text-gray-700">Search:</label>
        <input
          type="text"
          name="search"
          id="search"
          value="{{ request('search') }}"
          class="text-xs mt-1 block w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          onkeydown="if(event.key==='Enter') this.form.submit()"
        />
      </form>

      {{-- Items Per Page --}}
      <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        <label for="perPage" class="text-xs font-medium text-gray-700">Items per page:</label>
        <select 
          name="perPage" id="perPage"
          onchange="this.form.submit()"
          class="text-xs mt-1 block w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="10" {{ (int)request('perPage', $perPage) === 10 ? 'selected' : '' }}>10</option>
            <option value="20" {{ (int)request('perPage', $perPage) === 20 ? 'selected' : '' }}>20</option>
            <option value="50" {{ (int)request('perPage', $perPage) === 50 ? 'selected' : '' }}>50</option>
        </select>
      </form>

      {{-- Modal Form --}}
      <div x-show="open" x-cloak class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="open" x-transition.opacity
            class="fixed inset-0 bg-gray-500/75" aria-hidden="true"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg">
              <form method="POST"
                :action="mode === 'edit'
                ? '{{ url('masterdata/subkontraktor') }}/' + form.companycodeoriginal +'/'+ form.idoriginal
                : '{{ url('masterdata/subkontraktor') }}'"
                class="bg-white px-4 pt-2 pb-4 sm:p-6 sm:pt-1 sm:pb-4 space-y-6">
                @csrf
                <template x-if="mode === 'edit'">
                  <input type="hidden" name="_method" value="PATCH">
                </template>
                
                <div class="text-center sm:text-left">
                  <h3 class="text-lg font-medium text-gray-900" id="modal-title" x-text="mode === 'edit' ? 'Edit Subkontraktor' : 'Create Subkontraktor'">
                  </h3>
                  
                  <div class="mt-4 space-y-4">
                    {{-- Company Code - Hidden --}}
                    <div>
                      <label for="companycode" class="block text-sm font-medium text-gray-700">Kode Company</label>
                      <template x-if="mode === 'create'">
                        <div class="mt-1 flex items-center">
                          <input type="hidden" name="companycode" x-model="form.companycode">
                          <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm text-gray-700 font-medium"
                               x-text="form.companycode"></div>
                        </div>
                      </template>
                      <template x-if="mode === 'edit'">
                        <div class="mt-1">
                          <input type="hidden" name="companycode" x-model="form.companycode">
                          <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm text-gray-700 font-medium"
                               x-text="form.companycode"></div>
                        </div>
                      </template>
                    </div>

                    {{-- ID Subkontraktor --}}
                    <div>
                      <label for="id" class="block text-sm font-medium text-gray-700">ID Subkontraktor</label>
                      <template x-if="mode === 'create'">
                        <div>
                          <input type="text" name="id" id="id" x-model="form.id"
                                x-init="form.id = '{{ old('id') }}'"
                                @input="form.id = form.id.toUpperCase()"
                                class="mt-1 block w-1/2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 uppercase"
                                maxlength="10" required>
                          @error('id')
                          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                          @enderror
                        </div>
                      </template>
                      <template x-if="mode === 'edit'">
                        <div class="mt-1">
                          {{-- ID tidak bisa diubah saat edit --}}
                          {{-- <input type="text" name="id" x-model="form.id" class="mt-1 block w-1/2 border border-gray-300 rounded-md shadow-sm uppercase" maxlength="10" required> --}}
                          <input type="hidden" name="id" x-model="form.id">
                          <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm text-gray-700 font-medium w-1/2 uppercase"
                               x-text="form.id"></div>
                        </div>
                      </template>
                    </div>

                    {{-- Kontraktor Dropdown --}}
                    <div>
                      <label for="kontraktorid" class="block text-sm font-medium text-gray-700">Kontraktor</label>
                      <select name="kontraktorid" id="kontraktorid" x-model="form.kontraktorid"
                              x-init="form.kontraktorid = '{{ old('kontraktorid') }}'"
                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              required>
                        <option value="">-- Pilih Kontraktor --</option>
                        @foreach($kontraktorList as $kontraktor)
                          <option value="{{ $kontraktor->id }}">{{ $kontraktor->id }} - {{ $kontraktor->namakontraktor }}</option>
                        @endforeach
                      </select>
                      @error('kontraktorid')
                      <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                      @enderror
                    </div>

                    {{-- Nama Subkontraktor --}}
                    <div>
                      <label for="namasubkontraktor" class="block text-sm font-medium text-gray-700">Nama Subkontraktor</label>
                      <input type="text" name="namasubkontraktor" id="namasubkontraktor" x-model="form.namasubkontraktor" 
                            x-init="form.namasubkontraktor = '{{ old('namasubkontraktor') }}'"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            maxlength="100" required>
                      @error('namasubkontraktor')
                      <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                      @enderror
                    </div>

                  </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                  <button type="submit"
                          class="inline-flex w-full justify-center rounded-md bg-blue-600 px-4 py-2 text-white text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto"
                          x-text="mode === 'edit' ? 'Update' : 'Create'">
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
    </div>

    {{-- Table --}}
    <div class="mx-auto px-4 py-2">
        <div class="overflow-x-auto border border-gray-300 rounded-md">
            <table class="min-w-full bg-white text-sm text-center">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="py-2 px-4 border-b">No.</th>
                        <th class="py-2 px-4 border-b">ID Subkontraktor</th>
                        <th class="py-2 px-4 border-b">Nama Subkontraktor</th>
                        <th class="py-2 px-4 border-b">Kontraktor</th>
                        <th class="py-2 px-4 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subkontraktor as $index => $data)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border-b">{{ $subkontraktor->firstItem() + $index }}</td>
                            <td class="py-2 px-4 border-b">{{ $data->id }}</td>
                            <td class="py-2 px-4 border-b text-left">{{ $data->namasubkontraktor }}</td>
                            <td class="py-2 px-4 border-b text-left">{{ $data->kontraktorid }} - {{ $data->namakontraktor ?? '-' }}</td>
                            <td class="py-2 px-4 border-b">
                                <div class="flex items-center justify-center space-x-2">
                                  {{-- Edit Button --}}
                                  @can('masterdata.subkontraktor.edit')
                                  <button
                                    @click="
                                      mode = 'edit';
                                      form.companycodeoriginal = '{{ $data->companycode }}';
                                      form.companycode = '{{ $data->companycode }}';
                                      form.idoriginal = '{{ $data->id }}';
                                      form.id = '{{ $data->id }}';
                                      form.kontraktorid = '{{ $data->kontraktorid }}';
                                      form.namasubkontraktor = '{{ $data->namasubkontraktor }}';
                                      open = true
                                    "
                                    class="group flex items-center text-blue-600 hover:text-blue-800 focus:ring-2 focus:ring-blue-500 rounded-md px-2 py-1 text-sm">
                                    <svg class="w-6 h-6 text-blue-500 dark:text-white group-hover:hidden"
                                      aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                      width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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
                                  </button>
                                  @endcan
                                  
                                  {{-- Delete Button --}}
                                  @can('masterdata.subkontraktor.delete')
                                    <form 
                                      action="{{ url("masterdata/subkontraktor/{$data->companycode}/{$data->id}") }}" 
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus data ini?');"
                                      class="inline">
                                      @csrf
                                      @method('DELETE')
                                      <button 
                                        type="submit"
                                        class="group flex items-center text-red-600 hover:text-red-800 focus:ring-2 focus:ring-red-500 rounded-md px-2 py-1 text-sm">
                                        <svg class="w-6 h-6 text-red-500 dark:text-white group-hover:hidden"
                                          aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                          width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mx-4 my-1">
        @if ($subkontraktor->hasPages())
            {{ $subkontraktor->appends(request()->query())->links() }}
        @else
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ $subkontraktor->count() }}</span> of <span class="font-medium">{{ $subkontraktor->total() }}</span> results
                </p>
            </div>
        @endif
    </div>

    {{-- Toast Notification --}}
    @if (session('success'))
      <div x-data x-init="alert('{{ session('success') }}')"></div>
    @endif
  </div>
</x-layout>