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
        namakontraktor: ''
      },
      resetForm() {
        this.mode = 'create';
        this.form = { 
          companycode:'{{ session('companycode') }}', 
          id: '', 
          namakontraktor: ''
        };
        this.open = true;
      }
    }"
    class="mx-auto py-1 bg-white rounded-md shadow-md">

    <div class="flex items-center justify-between px-4 py-2">

      {{-- Create Button --}}
      @can('masterdata.kontraktor.create')
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
        {{-- Preserve filter --}}
        @if(request('status') !== null && request('status') !== '')
          <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
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

      {{-- Status Filter --}}
      <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        @if(request('search'))
          <input type="hidden" name="search" value="{{ request('search') }}">
        @endif
        @if(request('perPage'))
          <input type="hidden" name="perPage" value="{{ request('perPage') }}">
        @endif
        <label for="status" class="text-xs font-medium text-gray-700">Status:</label>
        <select 
          name="status" id="status"
          onchange="this.form.submit()"
          class="text-xs mt-1 block w-28 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>Semua</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Non-Active</option>
        </select>
      </form>

      {{-- Items Per Page --}}
      <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        @if(request('search'))
          <input type="hidden" name="search" value="{{ request('search') }}">
        @endif
        @if(request('status') !== null && request('status') !== '')
          <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
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
                ? '{{ url('masterdata/kontraktor') }}/' + form.companycodeoriginal +'/'+ form.idoriginal
                : '{{ url('masterdata/kontraktor') }}'"
                class="bg-white px-4 pt-2 pb-4 sm:p-6 sm:pt-1 sm:pb-4 space-y-6">
                @csrf
                <template x-if="mode === 'edit'">
                  <input type="hidden" name="_method" value="PATCH">
                </template>
                
                <div class="text-center sm:text-left">
                  <h3 class="text-lg font-medium text-gray-900" id="modal-title" x-text="mode === 'edit' ? 'Edit Kontraktor' : 'Create Kontraktor'">
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

                    {{-- ID Kontraktor --}}
                    <div>
                      <label for="id" class="block text-sm font-medium text-gray-700">ID Kontraktor</label>
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
                          <input type="hidden" name="id" x-model="form.id">
                          <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm text-gray-700 font-medium w-1/2 uppercase"
                               x-text="form.id"></div>
                        </div>
                      </template>
                    </div>

                    {{-- Nama Kontraktor --}}
                    <div>
                      <label for="namakontraktor" class="block text-sm font-medium text-gray-700">Nama Kontraktor</label>
                      <input type="text" name="namakontraktor" id="namakontraktor" x-model="form.namakontraktor" 
                            x-init="form.namakontraktor = '{{ old('namakontraktor') }}'"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            maxlength="100" required>
                      @error('namakontraktor')
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
                        <th class="py-2 px-4 border-b">ID Kontraktor</th>
                        <th class="py-2 px-4 border-b">Nama Kontraktor</th>
                        <th class="py-2 px-4 border-b">Status</th>
                        <th class="py-2 px-4 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kontraktor as $index => $data)
                        <tr class="hover:bg-gray-50 {{ !$data->isactive ? 'opacity-50' : '' }}">
                            <td class="py-2 px-4 border-b">{{ $kontraktor->firstItem() + $index }}</td>
                            <td class="py-2 px-4 border-b">{{ $data->id }}</td>
                            <td class="py-2 px-4 border-b">{{ $data->namakontraktor }}</td>
                            <td class="py-2 px-4 border-b">
                              @if($data->isactive)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                              @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Non-Active</span>
                              @endif
                            </td>
                            <td class="py-2 px-4 border-b">
                                <div class="flex items-center justify-center space-x-2">
                                  {{-- Edit Button --}}
                                  @can('masterdata.kontraktor.edit')
                                  <button
                                    @click="
                                      mode = 'edit';
                                      form.companycodeoriginal = '{{ $data->companycode }}';
                                      form.companycode = '{{ $data->companycode }}';
                                      form.idoriginal = '{{ $data->id }}';
                                      form.id = '{{ $data->id }}';
                                      form.namakontraktor = '{{ $data->namakontraktor }}';
                                      open = true
                                    "
                                    class="group flex items-center text-blue-600 hover:text-blue-800 focus:ring-2 focus:ring-blue-500 rounded-md px-2 py-1 text-sm">
                                    <svg class="w-6 h-6 text-blue-500 dark:text-white group-hover:hidden"
                                      aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                      width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <use xlink:href="#icon-edit-outline" />
                                    </svg>
                                    <svg class="w-6 h-6 text-blue-500 dark:text-white hidden group-hover:block"
                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                        <use xlink:href="#icon-edit-solid" />
                                        <use xlink:href="#icon-edit-solid2" />
                                    </svg>
                                  </button>
                                  @endcan
                                  
                                  {{-- Toggle Active/Deactive Button --}}
                                  @can('masterdata.kontraktor.delete')
                                    <form 
                                      action="{{ url("masterdata/kontraktor/{$data->companycode}/{$data->id}/toggle-active") }}" 
                                      method="POST"
                                      onsubmit="return confirm('{{ $data->isactive ? 'Yakin ingin menonaktifkan kontraktor ini?' : 'Yakin ingin mengaktifkan kembali kontraktor ini?' }}');"
                                      class="inline">
                                      @csrf
                                      @method('PATCH')
                                      @if($data->isactive)
                                        <button 
                                          type="submit"
                                          title="Nonaktifkan"
                                          class="group flex items-center text-orange-600 hover:text-orange-800 focus:ring-2 focus:ring-orange-500 rounded-md px-2 py-1 text-sm">
                                          {{-- Ban/Block icon for deactivate --}}
                                          <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                                                d="m6 6 12 12m3-6a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                          </svg>
                                        </button>
                                      @else
                                        <button 
                                          type="submit"
                                          title="Aktifkan kembali"
                                          class="group flex items-center text-green-600 hover:text-green-800 focus:ring-2 focus:ring-green-500 rounded-md px-2 py-1 text-sm">
                                          {{-- Check circle icon for activate --}}
                                          <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                          </svg>
                                        </button>
                                      @endif
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
        @if ($kontraktor->hasPages())
            {{ $kontraktor->appends(request()->query())->links() }}
        @else
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ $kontraktor->count() }}</span> of <span class="font-medium">{{ $kontraktor->total() }}</span> results
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