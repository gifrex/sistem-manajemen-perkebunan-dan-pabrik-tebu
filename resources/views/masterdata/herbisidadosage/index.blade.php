{{--resources\views\masterdata\herbisidadosage\index.blade.php--}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div 
    x-data="{
      open: @json($errors->any()),
      mode: 'create',
      form: { herbisidagroupid: '', herbisidagroupid_original: '', itemcode: '', itemcodeoriginal: '', dosageperha: '' },
      items: [],
      groups: [],
      loading: false,
      loadItems() {
        fetch('{{ route("masterdata.herbisida.items") }}')
          .then(res => res.json())
          .then(data => this.items = data);
      },
      loadGroups() {
        fetch('{{ route("masterdata.herbisida.group") }}')
          .then(res => res.json())
          .then(data => this.groups = data);
      },
      resetForm() {
        this.mode = 'create';
        this.form = { herbisidagroupid: '', herbisidagroupid_original: '', itemcode: '', itemcodeoriginal: '', dosageperha: '' };
        this.open = true;
      },
      editRow(data) {
        this.mode = 'edit';
        this.form = {
          herbisidagroupid: data.herbisidagroupid,
          herbisidagroupid_original: data.herbisidagroupid,
          itemcode: data.itemcode,
          itemcodeoriginal: data.itemcode,
          dosageperha: data.dosageperha
        };
        this.open = true;
      }
    }"
    x-init="loadItems(); loadGroups()"
    class="bg-white rounded-xl border-2 border-gray-300 shadow-sm">

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b-2 border-gray-200 bg-gray-50">
      @can('masterdata.herbisidadosage.create')
        <button @click="resetForm()"
                class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-xs font-bold uppercase transition-colors flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah Dosage
        </button>
      @endcan

      <div class="flex items-center gap-3">
        {{-- Company indicator --}}
        <div class="px-3 py-1.5 bg-gray-200 rounded-md text-xs font-bold text-gray-700 uppercase">
          {{ Session::get('companycode') }}
        </div>

        <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
          <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari group/item..."
                 class="text-xs w-56 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-gray-400 focus:border-gray-400"
                 onkeydown="if(event.key==='Enter') this.form.submit()" />
        </form>

        <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-1">
          <input type="hidden" name="search" value="{{ request('search') }}">
          <select name="perPage" onchange="this.form.submit()"
                  class="text-xs border border-gray-300 rounded-lg px-2 py-2 focus:ring-2 focus:ring-gray-400">
            @foreach([10, 20, 50] as $pp)
              <option value="{{ $pp }}" {{ (int)request('perPage', $perPage) === $pp ? 'selected' : '' }}>{{ $pp }}</option>
            @endforeach
          </select>
        </form>
      </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
      <table class="w-full border-collapse text-sm">
        <thead>
          <tr class="bg-gray-800 text-white">
            <th class="px-3 py-3 text-xs font-bold uppercase w-12">No.</th>
            <th class="px-3 py-3 text-xs font-bold uppercase text-left">Herbisida Group</th>
            <th class="px-3 py-3 text-xs font-bold uppercase text-left">Aktivitas</th>
            <th class="px-3 py-3 text-xs font-bold uppercase text-left">Kode Item</th>
            <th class="px-3 py-3 text-xs font-bold uppercase text-left">Nama Item</th>
            <th class="px-3 py-3 text-xs font-bold uppercase text-right">Dosis/Ha</th>
            <th class="px-3 py-3 text-xs font-bold uppercase text-center">Satuan</th>
            <th class="px-3 py-3 text-xs font-bold uppercase text-center w-24">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          @forelse ($herbisidaDosages as $index => $data)
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="px-3 py-2.5 text-center text-gray-500 font-bold">{{ $herbisidaDosages->firstItem() + $index }}</td>
              <td class="px-3 py-2.5">
                <span class="text-xs font-bold text-gray-900">{{ $data->herbisidagroupid }}</span>
                <span class="text-xs text-gray-500 ml-1">{{ $data->herbisidagroupname }}</span>
              </td>
              <td class="px-3 py-2.5">
                <span class="text-xs font-mono font-bold text-gray-800">{{ $data->activitycode }}</span>
                <span class="text-xs text-gray-500 ml-1">{{ $data->activityname }}</span>
              </td>
              <td class="px-3 py-2.5">
                <a href="{{ route('masterdata.herbisida.index', ['search' => $data->itemcode]) }}"
                   class="text-xs font-mono font-bold text-blue-600 hover:underline">
                  {{ $data->itemcode }}
                </a>
              </td>
              <td class="px-3 py-2.5 text-xs text-gray-700">{{ $data->itemname }}</td>
              <td class="px-3 py-2.5 text-right font-bold text-gray-900">{{ $data->dosageperha }}</td>
              <td class="px-3 py-2.5 text-center text-xs text-gray-500">{{ $data->measure }}</td>
              <td class="px-3 py-2.5">
                <div class="flex items-center justify-center gap-1">
                  @can('masterdata.herbisidadosage.edit')
                  <button
                    @click="editRow({
                      herbisidagroupid: '{{ $data->herbisidagroupid }}',
                      itemcode: '{{ $data->itemcode }}',
                      dosageperha: {{ $data->dosageperha ?? 0 }}
                    })"
                    class="p-1.5 rounded-md text-blue-600 hover:bg-blue-50 transition-colors"
                    title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                  @endcan
                  @can('masterdata.herbisidadosage.delete')
                  <form action="{{ url("masterdata/herbisida-dosage/{$data->herbisidagroupid}/{$data->itemcode}") }}"
                        method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-1.5 rounded-md text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </form>
                  @endcan
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-sm font-semibold">Tidak ada data dosis herbisida</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="p-4 border-t border-gray-200">
      @if ($herbisidaDosages->hasPages())
        {{ $herbisidaDosages->appends(['perPage' => $perPage, 'search' => request('search')])->links() }}
      @else
        <p class="text-xs text-gray-500">
          Menampilkan <span class="font-bold">{{ $herbisidaDosages->count() }}</span> dari <span class="font-bold">{{ $herbisidaDosages->total() }}</span> data
        </p>
      @endif
    </div>

    {{-- Modal --}}
    <div x-show="open" x-cloak class="relative z-50" role="dialog" aria-modal="true">
      <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-60"></div>
      <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <div x-show="open"
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="opacity-0 scale-95"
               x-transition:enter-end="opacity-100 scale-100"
               x-transition:leave="transition ease-in duration-150"
               x-transition:leave-start="opacity-100 scale-100"
               x-transition:leave-end="opacity-0 scale-95"
               class="bg-white rounded-lg shadow-2xl w-full max-w-lg">

            <form method="POST" id="formcreateedit"
              :action="mode === 'edit'
                ? '{{ url('masterdata/herbisida-dosage') }}/' + form.herbisidagroupid_original + '/' + form.itemcodeoriginal
                : '{{ url('masterdata/herbisida-dosage') }}'">
              @csrf
              <template x-if="mode === 'edit'">
                <input type="hidden" name="_method" value="PATCH">
              </template>

              {{-- Header --}}
              <div class="px-5 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 uppercase"
                    x-text="mode === 'edit' ? 'Edit Dosis Herbisida' : 'Tambah Dosis Herbisida'"></h3>
                <button @click="open = false" type="button" class="text-gray-400 hover:text-gray-600 p-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>

              {{-- Body --}}
              <div class="p-5 space-y-4">
                @error('herbisidagroupid')
                  <div class="p-2 bg-red-50 border border-red-200 rounded text-xs text-red-700">{{ $message }}</div>
                @enderror

                {{-- Company (read-only) --}}
                <div>
                  <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Company</label>
                  <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm font-bold text-gray-700">
                    {{ Session::get('companycode') }}
                  </div>
                </div>

                {{-- Herbisida Group --}}
                <div>
                  <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Herbisida Group *</label>
                  <select name="herbisidagroupid" x-model="form.herbisidagroupid"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-400 focus:border-gray-400" required>
                    <option value="" disabled>Pilih Herbisida Group</option>
                    <template x-for="g in groups" :key="g.herbisidagroupid">
                      <option :value="g.herbisidagroupid" x-text="`${g.herbisidagroupid} — ${g.herbisidagroupname} (${g.activitycode})`"></option>
                    </template>
                  </select>
                </div>

                {{-- Item Code --}}
                <div>
                  <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kode Item *</label>
                  <select name="itemcode" x-model="form.itemcode"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-400 focus:border-gray-400" required>
                    <option value="" disabled>Pilih Kode Item</option>
                    <template x-for="i in items" :key="i.itemcode">
                      <option :value="i.itemcode" x-text="`${i.itemcode} — ${i.itemname}`"></option>
                    </template>
                  </select>
                </div>

                {{-- Dosage --}}
                <div>
                  <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Dosis per Ha *</label>
                  <input type="number" step="0.001" name="dosageperha" x-model="form.dosageperha"
                         class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-400 focus:border-gray-400"
                         min="0" max="9999.99" required>
                </div>
              </div>

              {{-- Footer --}}
              <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-end gap-2">
                <button @click.prevent="open = false" type="button"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                  Batal
                </button>
                <button type="submit" id="submitmodal"
                        class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-bold hover:bg-gray-900 transition-colors"
                        x-text="mode === 'edit' ? 'Update' : 'Simpan'">
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    @if (session('success'))
      <div x-data x-init="alert('{{ session('success') }}')"></div>
    @endif
  </div>
</x-layout>

<script>
  $('#submitmodal').on('click', function() {
    $(this).attr('disabled', true);
    $(this).text('Menyimpan...');
    $('#formcreateedit').submit();
  });
</script>