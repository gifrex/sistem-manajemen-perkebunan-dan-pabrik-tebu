<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div 
    x-data="{
      open: @json($errors->any()),
      mode: 'create',
      form: { kodevarietas: '', description: '' },
      resetForm() {
        this.mode = 'create';
        this.form = { kodevarietas: '', description: '' };
        this.open = true;
      }
    }"
    class="mx-auto py-1 bg-white rounded-md shadow-md"
  >

    <div class="flex items-center justify-between px-4 py-2">
        {{-- Create Button --}}
      @can('masterdata.varietas.create')
        <button @click="resetForm()"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2">
          <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5" />
          </svg>
          New Data
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
      {{-- Items per page --}}
      <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        <label for="perPage" class="text-xs font-medium text-gray-700">Items per page:</label>
        <select
          name="perPage"
          id="perPage"
          onchange="this.form.submit()"
          class="text-xs mt-1 block w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="10" {{ (int)request('perPage', $perPage) === 10 ? 'selected' : '' }}>10</option>
          <option value="20" {{ (int)request('perPage', $perPage) === 20 ? 'selected' : '' }}>20</option>
          <option value="50" {{ (int)request('perPage', $perPage) === 50 ? 'selected' : '' }}>50</option>
        </select>
      </form>

      {{-- Modal --}}
      <div x-show="open" x-cloak class="relative z-10" role="dialog">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-500/75"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="open" x-transition class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg">
              <form method="POST"
                    :action="mode === 'edit'
                      ? '{{ url('masterdata/varietas') }}/' + form.kodevarietasOriginal
                      : '{{ url('masterdata/varietas') }}'"
                    class="bg-white px-4 pt-2 pb-4 sm:p-6 sm:pt-1 sm:pb-4 space-y-6">
                @csrf
                <template x-if="mode === 'edit'">
                  <input type="hidden" name="_method" value="PATCH">
                </template>

                <h3 class="text-lg font-medium text-gray-900" x-text="mode === 'edit' ? 'Edit Varietas' : 'Create Varietas'"></h3>

                <div class="mt-4 space-y-4">
                  <div>
                    <label for="kodevarietas" class="block text-sm font-medium text-gray-700">Kode Varietas</label>
                    <input
                      type="text"
                      name="kodevarietas"
                      id="kodevarietas"
                      x-model="form.kodevarietas"
                      @input="form.kodevarietas = form.kodevarietas.toUpperCase()"
                      class="mt-1 block w-1/2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 uppercase"
                      maxlength="10"
                      required
                      x-init="form.kodevarietas = '{{ old('kodevarietas') }}'"
                    >
                    @error('kodevarietas')
                      <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                  </div>

                  <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Keterangan</label>
                    <input
                      type="text"
                      name="description"
                      id="description"
                      x-model="form.description"
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      maxlength="255"
                      required
                      x-init="form.description = '{{ old('description') }}'"
                    >
                    @error('description')
                      <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
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
        <table class="table-fixed w-full bg-white text-sm text-center">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="w-3/12 py-2 px-4 border-b">Kode</th>
              <th class="w-6/12 py-2 px-4 border-b">Keterangan</th>
              <th class="w-3/12 py-2 px-4 border-b">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($varietas as $data)
              <tr class="hover:bg-gray-50">
                <td class="py-2 px-4 border-b">{{ $data->kodevarietas }}</td>
                <td class="py-2 px-4 border-b">{{ $data->description }}</td>
                <td class="py-2 px-4 border-b">
                  <div class="flex items-center justify-center space-x-2">
                    @can('masterdata.varietas.edit')
                      <button @click="
                        mode = 'edit';
                        form.kodevarietasOriginal = '{{ $data->kodevarietas }}';
                        form.kodevarietas = '{{ $data->kodevarietas }}';
                        form.description = '{{ $data->description }}';
                        open = true;
                      "
                      class="group flex items-center text-blue-600 hover:text-blue-800 focus:ring-2 focus:ring-blue-500 rounded-md px-2 py-1 text-sm">
                        <!-- icon edit -->
                        <svg class="w-6 h-6 text-blue-500 group-hover:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <use xlink:href="#icon-edit-outline"/>
                        </svg>
                        <svg class="w-6 h-6 text-blue-500 hidden group-hover:block" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                          <use xlink:href="#icon-edit-solid"/> <use xlink:href="#icon-edit-solid2" />
                        </svg>
                      </button>
                    @endcan
                    @can('masterdata.varietas.delete')
                      <form action="{{ url("masterdata/varietas/{$data->kodevarietas}") }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="group flex items-center text-red-600 hover:text-red-800 focus:ring-2 focus:ring-red-500 rounded-md px-2 py-1 text-sm">
                          <!-- icon trash -->
                          <svg class="w-6 h-6 text-red-500 group-hover:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <use xlink:href="#icon-trash-outline"/>
                          </svg>
                          <svg class="w-6 h-6 text-red-500 hidden group-hover:block" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                            <use xlink:href="#icon-trash-solid"/>
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
      @if($varietas->hasPages())
        {{ $varietas->appends(['perPage' => $varietas->perPage(), 'search' => $search])->links() }}
      @else
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-700">
            Showing <span class="font-medium">{{ $varietas->count() }}</span> of <span class="font-medium">{{ $varietas->total() }}</span> results
          </p>
        </div>
      @endif
    </div>

    {{-- Toast --}}
    @if(session('success'))
      <div x-data x-init="alert('{{ session('success') }}')"></div>
    @endif

  </div>
</x-layout>
