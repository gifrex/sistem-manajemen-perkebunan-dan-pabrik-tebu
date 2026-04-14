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
        plot: '', 
        blok: '',
        activebatchno: '',
        isactive: '1'
      },
      resetForm() {
        this.mode = 'create';
        this.form = { 
          companycode:'{{ session('companycode') }}', 
          plot: '', 
          blok: '',
          activebatchno: '',
          isactive: '1'
        };
        this.open = true;
      }
    }"
    class="mx-auto py-1 bg-white rounded-md shadow-md">

    <div class="flex items-center justify-between px-4 py-2">

      {{-- Create Button --}}
      @can('masterdata.masterlist.create')
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

      {{-- Item Per Page --}}
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
                ? '{{ url('masterdata/master-list') }}/' + form.companycodeoriginal +'/'+ form.plotoriginal
                : '{{ url('masterdata/master-list') }}'"
                class="bg-white px-4 pt-2 pb-4 sm:p-6 sm:pt-1 sm:pb-4 space-y-6">
                @csrf
                <template x-if="mode === 'edit'">
                  <input type="hidden" name="_method" value="PATCH">
                </template>
                <div class="text-center sm:text-left">
                  <h3 class="text-lg font-medium text-gray-900" id="modal-title" x-text="mode === 'edit' ? 'Edit Master List' : 'Create Master List'">
                  </h3>
                  <div class="mt-4 space-y-4">
                    {{-- Company Code --}}
                    <div>
                      <label for="companycode" class="block text-sm font-medium text-gray-700">Kode Company</label>
                      <template x-if="mode === 'create'">
                        <div class="mt-1 flex items-center">
                          <input type="hidden" name="companycode" x-model="form.companycode">
                          <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm text-gray-700 font-medium"
                               x-text="form.companycode"></div>
                          <span class="ml-2 text-xs text-gray-500">(Company session aktif)</span>
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

                    {{-- Row 1: Plot, Blok --}}
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label for="plot" class="block text-sm font-medium text-gray-700">Plot</label>
                        <input type="text" name="plot" id="plot" x-model="form.plot" 
                              x-init="form.plot = '{{ old('plot') }}'"
                              @input="form.plot = form.plot.toUpperCase()"
                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 uppercase"
                              maxlength="5" required>
                          @error('plot')
                          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                          @enderror
                      </div>
                      <div>
                        <label for="blok" class="block text-sm font-medium text-gray-700">Blok</label>
                        <input type="text" name="blok" id="blok" x-model="form.blok" 
                              x-init="form.blok = '{{ old('blok') }}'"
                              @input="form.blok = form.blok.toUpperCase()"
                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 uppercase"
                              maxlength="2">
                      </div>
                    </div>

                    {{-- Row 2: Active Batch No --}}
                    <div>
                      <label for="activebatchno" class="block text-sm font-medium text-gray-700">Active Batch No</label>
                      <input type="text" name="activebatchno" id="activebatchno" x-model="form.activebatchno" 
                            x-init="form.activebatchno = '{{ old('activebatchno') }}'"
                            @input="form.activebatchno = form.activebatchno.toUpperCase()"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 uppercase"
                            maxlength="20">
                      <p class="mt-1 text-xs text-gray-500">Batch yang sedang aktif untuk plot ini</p>
                      @error('activebatchno')
                      <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                      @enderror
                    </div>

                    {{-- Row 3: Is Active (only show on edit) --}}
                    <div x-show="mode === 'edit'">
                      <label for="isactive" class="block text-sm font-medium text-gray-700">Status Plot</label>
                      <select name="isactive" id="isactive" x-model="form.isactive" 
                             class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                      </select>
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
                        <th class="py-2 px-4 border-b">Plot</th>
                        <th class="py-2 px-4 border-b">Blok</th>
                        <th class="py-2 px-4 border-b">Active Batch</th>
                        <th class="py-2 px-4 border-b">Batch Info</th>
                        <th class="py-2 px-4 border-b">Status</th>
                        <th class="py-2 px-4 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($masterlist as $index => $data)
                        <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">{{ $masterlist->firstItem() + $index }}</td>
                            <td class="py-2 px-4 border-b font-medium">{{ $data->plot }}</td>
                            <td class="py-2 px-4 border-b">{{ $data->blok ?? '-' }}</td>
                            <td class="py-2 px-4 border-b">
                                @if($data->activebatchno)
                                    <a href="{{ url('masterdata/batch?search=' . $data->activebatchno) }}" 
                                       class="font-medium text-blue-600 hover:text-blue-800 hover:underline">
                                        {{ $data->activebatchno }}
                                    </a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="py-2 px-4 border-b">
                                @if($data->activeBatch)
                                    <div class="text-xs space-y-1">
                                        <div class="flex items-center justify-center gap-1">
                                            <span class="text-gray-500">Cycle:</span>
                                            <span class="px-2 py-1 rounded-full font-medium
                                                {{ $data->activeBatch->lifecyclestatus === 'PC' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $data->activeBatch->lifecyclestatus === 'RC1' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $data->activeBatch->lifecyclestatus === 'RC2' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $data->activeBatch->lifecyclestatus === 'RC3' ? 'bg-purple-100 text-purple-800' : '' }}">
                                                {{ $data->activeBatch->lifecyclestatus }}
                                            </span>
                                        </div>
                                        <div class="text-gray-600">
                                            Area: <span class="font-medium">{{ number_format($data->activeBatch->batcharea, 2) }} ha</span>
                                        </div>
                                        <div class="text-gray-600">
                                            Panen: <span class="font-medium">{{ $data->activeBatch->tanggalpanen ? \Carbon\Carbon::parse($data->activeBatch->tanggalpanen)->format('d/m/Y') : '-' }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">No active batch</span>
                                @endif
                            </td>
                            <td class="py-2 px-4 border-b">
                                @if($data->isactive)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                @endif
                            </td>
                            {{-- Edit Button --}}
                              <td class="py-2 px-4 border-b">
                                <div class="flex items-center justify-center space-x-2">
                                  @can('masterdata.masterlist.edit')
                                  <button
                                    @click="
                                      mode = 'edit';
                                      form.companycodeoriginal = '{{ $data->companycode }}';
                                      form.companycode = '{{ $data->companycode }}';
                                      form.plotoriginal = '{{ $data->plot }}';
                                      form.plot = '{{ $data->plot }}';
                                      form.blok = '{{ $data->blok }}';
                                      form.activebatchno = '{{ $data->activebatchno }}';
                                      form.isactive = '{{ $data->isactive }}';
                                      open = true
                                    "
                                    class="group flex items-center text-blue-600 hover:text-blue-800 focus:ring-2 focus:ring-blue-500 rounded-md px-2 py-1 text-sm">
                                    <svg class="w-6 h-6 text-blue-500 dark:text-white group-hover:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <use xlink:href="#icon-edit-outline" />
                                    </svg>
                                    <svg class="w-6 h-6 text-blue-500 dark:text-white hidden group-hover:block" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                        <use xlink:href="#icon-edit-solid" />
                                        <use xlink:href="#icon-edit-solid2" />
                                    </svg>
                                    <span class="w-0.5"></span>
                                  </button>
                                  @endcan
                                  {{-- Delete Button --}}
                                  @can('masterdata.masterlist.delete')
                                    <form action="{{ url("masterdata/master-list/{$data->companycode}/{$data->plot}") }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');" class="inline">
                                      @csrf
                                      @method('DELETE')
                                      <button type="submit" class="group flex items-center text-red-600 hover:text-red-800 focus:ring-2 focus:ring-red-500 rounded-md px-2 py-1 text-sm">
                                        <svg class="w-6 h-6 text-red-500 dark:text-white group-hover:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <use xlink:href="#icon-trash-outline" />
                                        </svg>
                                        <svg class="w-6 h-6 text-red-500 dark:text-white hidden group-hover:block" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                            <use xlink:href="#icon-trash-solid" />
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
        @if ($masterlist->hasPages())
            {{ $masterlist->appends(request()->query())->links() }}
        @else
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ $masterlist->count() }}</span> of <span class="font-medium">{{ $masterlist->total() }}</span> results
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