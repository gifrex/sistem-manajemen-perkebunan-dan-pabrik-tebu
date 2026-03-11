{{--resources\views\masterdata\herbisidagroup\index.blade.php--}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div 
    x-data="{
      open: false,
      mode: 'create',
      form: { 
        herbisidagroupid: '{{ $nextId }}',
        herbisidagroupname: '', 
        activitycode: '', 
        description: '',
        rounddosage: 0,
        items: [{ itemcode: '', dosageperha: '' }]
      },
      resetForm() {
        this.mode = 'create';
        this.form = { 
          herbisidagroupid: '{{ $nextId }}',
          herbisidagroupname: '', 
          activitycode: '', 
          description: '',
          rounddosage: 0,
          items: [{ itemcode: '', dosageperha: '' }]
        };
        this.open = true;
      },
      addItem() {
        this.form.items.push({ itemcode: '', dosageperha: '' });
      },
      removeItem(index) {
        if (this.form.items.length > 1) {
          this.form.items.splice(index, 1);
        }
      }
    }"
    class="bg-white rounded-xl border-2 border-gray-300 shadow-sm">

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b-2 border-gray-200 bg-gray-50">
      @can('masterdata.herbisidagroup.create')
        <button @click="resetForm()"
                class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-xs font-bold uppercase transition-colors flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          New Group
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
              <option value="{{ $pp }}" {{ (int)request('perPage', 10) === $pp ? 'selected' : '' }}>{{ $pp }}</option>
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
            <th class="px-3 py-3 text-xs font-bold uppercase w-12">ID</th>
            <th class="px-3 py-3 text-xs font-bold uppercase text-left">Group Name</th>
            <th class="px-3 py-3 text-xs font-bold uppercase text-left">Activity</th>
            <th class="px-3 py-3 text-xs font-bold uppercase text-left">Description</th>
            <th class="px-3 py-3 text-xs font-bold uppercase text-left">Kode Item</th>
            <th class="px-3 py-3 text-xs font-bold uppercase text-left">Nama Item</th>
            <th class="px-3 py-3 text-xs font-bold uppercase text-right">Dosis/Ha</th>
            <th class="px-3 py-3 text-xs font-bold uppercase text-center">Pembulatan</th>
            <th class="px-3 py-3 text-xs font-bold uppercase text-center w-24">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          @php $grouped = $grouping->groupBy('herbisidagroupid'); @endphp

          @forelse ($grouped as $groupId => $items)
            @php
              $hasDosage = $items->first()->itemcode !== null;
            @endphp
            @if($hasDosage)
              {{-- Group WITH dosage for this company --}}
              @foreach ($items as $index => $data)
                <tr class="hover:bg-gray-50 transition-colors">
                  @if($index === 0)
                    <td class="px-3 py-2.5 text-center font-bold text-gray-700 bg-gray-50 border-r border-gray-200" rowspan="{{ $items->count() }}">
                      {{ $groupId }}
                    </td>
                    <td class="px-3 py-2.5 bg-gray-50 border-r border-gray-200" rowspan="{{ $items->count() }}">
                      <span class="text-sm font-bold text-gray-900">{{ $data->herbisidagroupname }}</span>
                    </td>
                    <td class="px-3 py-2.5 bg-gray-50 border-r border-gray-200" rowspan="{{ $items->count() }}">
                      <span class="text-xs font-mono font-bold text-gray-800">{{ $data->activitycode }}</span>
                    </td>
                    <td class="px-3 py-2.5 bg-gray-50 border-r border-gray-200" rowspan="{{ $items->count() }}">
                      <div class="text-xs text-gray-600 max-w-[180px] whitespace-pre-line">{{ $data->description ?? '-' }}</div>
                    </td>
                  @endif

                  <td class="px-3 py-2.5">
                    <span class="text-xs font-mono font-bold text-gray-800">{{ $data->itemcode }}</span>
                  </td>
                  <td class="px-3 py-2.5 text-xs text-gray-700">{{ $data->itemname ?? '-' }}</td>
                  <td class="px-3 py-2.5 text-right font-bold text-gray-900">{{ $data->dosageperha }}</td>

                  @if($index === 0)
                    <td class="px-3 py-2.5 text-center bg-gray-50 border-l border-gray-200" rowspan="{{ $items->count() }}">
                      @if($data->rounddosage == 1)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-300">
                          Pembulatan
                        </span>
                      @else
                        <span class="text-[10px] text-gray-400">—</span>
                      @endif
                    </td>
                    <td class="px-3 py-2.5 bg-gray-50 border-l border-gray-200" rowspan="{{ $items->count() }}">
                      <div class="flex items-center justify-center gap-1">
                        @can('masterdata.herbisidagroup.edit')
                        <button @click="
                          mode = 'edit';
                          form.herbisidagroupid = {{ json_encode($groupId) }};
                          form.herbisidagroupname = {{ json_encode($data->herbisidagroupname) }};
                          form.activitycode = {{ json_encode($data->activitycode) }};
                          form.description = {{ json_encode($data->description ?? '') }};
                          form.rounddosage = {{ $data->rounddosage == 1 ? '1' : '0' }};
                          form.items = {{ $items->map(fn($i) => ['itemcode' => $i->itemcode, 'dosageperha' => $i->dosageperha])->toJson() }};
                          open = true;
                        "
                        class="p-1.5 rounded-md text-blue-600 hover:bg-blue-50 transition-colors" title="Edit">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                          </svg>
                        </button>
                        @endcan
                        @can('masterdata.herbisidagroup.delete')
                        <form action="{{ url("masterdata/herbisida-group/{$groupId}") }}" method="POST"
                              onsubmit="return confirm('Yakin hapus dosage group ini untuk company Anda?');">
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
                  @endif
                </tr>
              @endforeach
            @else
              {{-- Group WITHOUT dosage for this company --}}
              @php $data = $items->first(); @endphp
              <tr class="bg-yellow-50 hover:bg-yellow-100 transition-colors">
                <td class="px-3 py-2.5 text-center font-bold text-gray-400">{{ $groupId }}</td>
                <td class="px-3 py-2.5">
                  <span class="text-sm font-bold text-gray-500">{{ $data->herbisidagroupname }}</span>
                </td>
                <td class="px-3 py-2.5">
                  <span class="text-xs font-mono text-gray-500">{{ $data->activitycode }}</span>
                </td>
                <td class="px-3 py-2.5">
                  <div class="text-xs text-gray-400 max-w-[180px] whitespace-pre-line">{{ $data->description ?? '-' }}</div>
                </td>
                <td colspan="3" class="px-3 py-2.5 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="text-xs font-semibold text-yellow-700">Belum ada dosage untuk {{ Session::get('companycode') }}</span>
                  </div>
                </td>
                <td class="px-3 py-2.5 text-center">
                  @if($data->rounddosage == 1)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-300">
                      Pembulatan
                    </span>
                  @else
                    <span class="text-[10px] text-gray-400">—</span>
                  @endif
                </td>
                <td class="px-3 py-2.5">
                  <div class="flex items-center justify-center">
                    @can('masterdata.herbisidagroup.edit')
                    <button @click="
                      mode = 'edit';
                      form.herbisidagroupid = {{ json_encode($groupId) }};
                      form.herbisidagroupname = {{ json_encode($data->herbisidagroupname) }};
                      form.activitycode = {{ json_encode($data->activitycode) }};
                      form.description = {{ json_encode($data->description ?? '') }};
                      form.rounddosage = {{ $data->rounddosage == 1 ? '1' : '0' }};
                      form.items = [{ itemcode: '', dosageperha: '' }];
                      open = true;
                    "
                    class="px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white rounded-md text-xs font-bold transition-colors flex items-center gap-1"
                    title="Tambah dosage">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                      </svg>
                      Input Dosage
                    </button>
                    @endcan
                  </div>
                </td>
              </tr>
            @endif
          @empty
            <tr>
              <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-sm font-semibold">Tidak ada data herbisida group</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="p-4 border-t border-gray-200">
      {{ $grouping->appends(['search' => request('search'), 'perPage' => request('perPage', 10)])->links() }}
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
               class="bg-white rounded-lg shadow-2xl w-full max-w-2xl">

            <form method="POST"
                  :action="mode === 'edit'
                    ? '{{ url('masterdata/herbisida-group') }}/' + form.herbisidagroupid
                    : '{{ url('masterdata/herbisida-group') }}'">
              @csrf
              <template x-if="mode === 'edit'">
                <input type="hidden" name="_method" value="PATCH">
              </template>
              <input type="hidden" name="rounddosage" :value="form.rounddosage ? '1' : '0'">

              {{-- Header --}}
              <div class="px-5 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 uppercase"
                    x-text="mode === 'edit' ? 'Edit Herbisida Group' : 'Tambah Herbisida Group'"></h3>
                <button @click="open = false" type="button" class="text-gray-400 hover:text-gray-600 p-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>

              {{-- Body --}}
              <div class="p-5 space-y-4">
                {{-- Group ID (create only) --}}
                <div x-show="mode === 'create'">
                  <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Group ID</label>
                  <input type="hidden" name="herbisidagroupid" x-model="form.herbisidagroupid">
                  <div class="w-1/3 px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm font-bold text-gray-700" x-text="form.herbisidagroupid"></div>
                  <p class="text-[10px] text-gray-500 mt-1">Auto-generated dari sistem</p>
                </div>

                {{-- Group Name --}}
                <div>
                  <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Group Name *</label>
                  <input type="text" name="herbisidagroupname" x-model="form.herbisidagroupname"
                         class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-400 focus:border-gray-400" required>
                </div>

                {{-- Activity Code --}}
                <div>
                  <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Activity Code *</label>
                  <select name="activitycode" x-model="form.activitycode"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-400 focus:border-gray-400" required>
                    <option value="">-- Pilih Aktivitas --</option>
                    @foreach($activities as $activity)
                      <option value="{{ $activity->activitycode }}">
                        {{ $activity->activitycode }} — {{ $activity->activityname }}
                      </option>
                    @endforeach
                  </select>
                </div>

                {{-- Description --}}
                <div>
                  <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Description</label>
                  <textarea name="description" x-model="form.description" rows="2"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-400 focus:border-gray-400"></textarea>
                </div>

                {{-- Pembulatan --}}
                <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 rounded-lg p-3">
                  <input type="checkbox" id="rounddosage_cb"
                         :checked="form.rounddosage == 1"
                         @change="form.rounddosage = $event.target.checked ? 1 : 0"
                         class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                  <div>
                    <label for="rounddosage_cb" class="text-xs font-bold text-gray-800 cursor-pointer">Pembulatan Dosage</label>
                    <p class="text-[10px] text-gray-500 mt-0.5">Jika dicentang, qty material akan dibulatkan ke kelipatan 0.05</p>
                  </div>
                </div>

                {{-- Items --}}
                <div class="border-t border-gray-200 pt-4">
                  <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-bold text-gray-800 uppercase">Herbisida Items *</h4>
                    <button type="button" @click="addItem()"
                            class="bg-green-700 hover:bg-green-800 text-white px-3 py-1 rounded-md text-xs font-bold transition-colors">
                      + Tambah Item
                    </button>
                  </div>

                  <div class="space-y-2 max-h-48 overflow-y-auto">
                    <template x-for="(item, index) in form.items" :key="index">
                      <div class="flex items-center gap-2 bg-gray-50 p-2.5 rounded-lg border border-gray-200">
                        <div class="flex-1">
                          <select :name="'items[' + index + '][itemcode]'" x-model="item.itemcode"
                                  class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-xs focus:ring-2 focus:ring-gray-400" required>
                            <option value="">-- Pilih Item --</option>
                            @foreach($herbisidaItems as $herbisida)
                              <option value="{{ $herbisida->itemcode }}">
                                {{ $herbisida->itemcode }} — {{ $herbisida->itemname }}
                              </option>
                            @endforeach
                          </select>
                        </div>
                        <div class="w-28">
                          <input type="number" :name="'items[' + index + '][dosageperha]'" x-model="item.dosageperha"
                                 placeholder="Dosis/Ha" step="0.001"
                                 class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-xs focus:ring-2 focus:ring-gray-400" required>
                        </div>
                        <button type="button" @click="removeItem(index)"
                                class="p-1 text-red-500 hover:bg-red-50 rounded transition-colors"
                                :class="form.items.length <= 1 ? 'opacity-30 cursor-not-allowed' : ''"
                                :disabled="form.items.length <= 1">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                          </svg>
                        </button>
                      </div>
                    </template>
                  </div>
                </div>
              </div>

              {{-- Footer --}}
              <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-end gap-2">
                <button @click.prevent="open = false" type="button"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                  Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-bold hover:bg-gray-900 transition-colors"
                        x-text="mode === 'edit' ? 'Update' : 'Simpan'">
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    {{-- Toast --}}
    @if(session('success'))
      <div x-data x-init="alert('{{ session('success') }}')"></div>
    @endif
    @if(session('error'))
      <div x-data x-init="alert('{{ session('error') }}')"></div>
    @endif
  </div>
</x-layout>