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
              } else {
                  alert('At least one item is required');
              }
          }
      }"
      class="mx-auto py-1 bg-white rounded-md shadow-md">

      <div class="flex items-center justify-between px-4 py-2">
          {{-- Create Button --}}
          @can('masterdata.herbisidagroup.create')
              <button @click="resetForm()"
                      class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 flex items-center gap-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg> New Group
              </button>
          @endcan

          {{-- Search Form --}}
          <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
              <label for="search" class="text-xs font-medium text-gray-700">Search:</label>
              <input type="text" name="search" id="search" value="{{ request('search') }}"
                  class="text-xs mt-1 block w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  onkeydown="if(event.key==='Enter') this.form.submit()" />
          </form>

          {{-- Items per page --}}
          <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
              <input type="hidden" name="search" value="{{ request('search') }}">
              <label for="perPage" class="text-xs font-medium text-gray-700">Items per page:</label>
              <select name="perPage" id="perPage" onchange="this.form.submit()"
                  class="text-xs mt-1 block w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                  <option value="10" {{ (int)request('perPage', 10) === 10 ? 'selected' : '' }}>10</option>
                  <option value="20" {{ (int)request('perPage', 10) === 20 ? 'selected' : '' }}>20</option>
                  <option value="50" {{ (int)request('perPage', 10) === 50 ? 'selected' : '' }}>50</option>
              </select>
          </form>

          {{-- Modal --}}
          <div x-show="open" x-cloak class="relative z-10" role="dialog">
              <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-500/75"></div>
              <div class="fixed inset-0 z-10 overflow-y-auto">
                  <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                      <div x-show="open" x-transition 
                           class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-2xl">
                          <form method="POST"
                                :action="mode === 'edit'
                                  ? '{{ url('masterdata/herbisida-group') }}/' + form.herbisidagroupid
                                  : '{{ url('masterdata/herbisida-group') }}'"
                                class="bg-white px-4 pt-4 pb-4 sm:p-6 space-y-4">
                              @csrf
                              <template x-if="mode === 'edit'">
                                  <input type="hidden" name="_method" value="PATCH">
                              </template>

                              {{-- Hidden field for rounddosage - always submitted --}}
                              <input type="hidden" name="rounddosage" :value="form.rounddosage ? '1' : '0'">

                              <h3 class="text-base font-semibold text-gray-900" 
                                  x-text="mode === 'edit' ? 'Edit Herbisida Group' : 'Create Herbisida Group'"></h3>

                              {{-- Group Info --}}
                              <div class="space-y-3">
                                {{-- Herbisida Group ID --}}
                                <div x-show="mode === 'create'">
                                    <label class="block text-xs font-medium text-gray-700">Group ID *</label>
                                    <input type="text" name="herbisidagroupid" x-model="form.herbisidagroupid"
                                          class="mt-1 block w-1/2 text-sm border border-gray-300 rounded-md px-3 py-1.5 focus:ring-blue-500 focus:border-blue-500" 
                                          required>
                                    <p class="text-xs text-gray-500 mt-1">Auto-generated, but you can edit it</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Group Name *</label>
                                    <input type="text" name="herbisidagroupname" x-model="form.herbisidagroupname"
                                          class="mt-1 block w-full text-sm border border-gray-300 rounded-md px-3 py-1.5 focus:ring-blue-500 focus:border-blue-500" required>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Activity Code *</label>
                                    <select name="activitycode" x-model="form.activitycode"
                                            class="mt-1 block w-full text-sm border border-gray-300 rounded-md px-3 py-1.5 focus:ring-blue-500 focus:border-blue-500" 
                                            required>
                                        <option value="">-- Select Activity --</option>
                                        @foreach($activities as $activity)
                                        <option value="{{ $activity->activitycode }}">
                                            {{ $activity->activitycode }} - {{ $activity->activityname }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Description</label>
                                    <textarea name="description" x-model="form.description" rows="2"
                                              class="mt-1 block w-full text-sm border border-gray-300 rounded-md px-3 py-1.5 focus:ring-blue-500 focus:border-blue-500"></textarea>
                                </div>

                                {{-- Round Dosage Checkbox - NO name attribute, driven by hidden input above --}}
                                <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 rounded-md p-3">
                                    <input type="checkbox" id="rounddosage_cb"
                                           :checked="form.rounddosage == 1"
                                           @change="form.rounddosage = $event.target.checked ? 1 : 0"
                                           class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div>
                                        <label for="rounddosage_cb" class="text-xs font-semibold text-gray-800 cursor-pointer">Pembulatan Dosage</label>
                                        <p class="text-[11px] text-gray-500 mt-0.5">Jika dicentang, qty material akan dibulatkan ke kelipatan 0.05</p>
                                    </div>
                                </div>
                              </div>

                              {{-- Items Section --}}
                              <div class="border-t pt-3">
                                  <div class="flex items-center justify-between mb-2">
                                      <h4 class="text-xs font-semibold text-gray-800">Herbisida Items *</h4>
                                      <button type="button" @click="addItem()"
                                              class="bg-green-600 text-white px-2 py-1 rounded-md hover:bg-green-700 text-xs">
                                          + Add
                                      </button>
                                  </div>

                                  <div class="space-y-2 max-h-48 overflow-y-auto">
                                      <template x-for="(item, index) in form.items" :key="index">
                                          <div class="flex items-center gap-2 bg-gray-50 p-2 rounded-md">
                                              <div class="flex-1">
                                                  <select :name="'items[' + index + '][itemcode]'" 
                                                          x-model="item.itemcode"
                                                          class="w-full border border-gray-300 rounded-md px-2 py-1 text-xs" required>
                                                      <option value="">-- Select Item --</option>
                                                      @foreach($herbisidaItems as $herbisida)
                                                      <option value="{{ $herbisida->itemcode }}">
                                                          {{ $herbisida->itemcode }} - {{ $herbisida->itemname }}
                                                      </option>
                                                      @endforeach
                                                  </select>
                                              </div>
                                              <div class="w-24">
                                                  <input type="number" 
                                                         :name="'items[' + index + '][dosageperha]'"
                                                         x-model="item.dosageperha"
                                                         placeholder="Dosage"
                                                         step="0.01"
                                                         class="w-full border border-gray-300 rounded-md px-2 py-1 text-xs" required>
                                              </div>
                                              <button type="button" @click="removeItem(index)"
                                                      class="text-red-600 hover:text-red-800">
                                                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                  </svg>
                                              </button>
                                          </div>
                                      </template>
                                  </div>
                              </div>

                              {{-- Buttons --}}
                              <div class="bg-gray-50 -mx-4 -mb-4 px-4 py-3 sm:flex sm:flex-row-reverse">
                                  <button type="submit"
                                          class="inline-flex w-full justify-center rounded-md bg-blue-600 px-4 py-1.5 text-white text-sm font-medium shadow-sm hover:bg-blue-700 sm:ml-3 sm:w-auto"
                                          x-text="mode === 'edit' ? 'Update' : 'Create'">
                                  </button>
                                  <button @click.prevent="open = false" type="button"
                                          class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-1.5 text-gray-700 text-sm font-medium shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
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
              <table class="min-w-full bg-white text-sm">
                  <thead>
                      <tr class="bg-gray-100 text-gray-700">
                          <th class="py-2 px-4 border-b">No</th>
                          <th class="py-2 px-4 border-b">Group Name</th>
                          <th class="py-2 px-4 border-b">Activity Code</th>
                          <th class="py-2 px-4 border-b">Description</th>
                          <th class="py-2 px-4 border-b">Item Code</th>
                          <th class="py-2 px-4 border-b">Item Name</th>
                          <th class="py-2 px-4 border-b">Dosage/HA</th>
                          <th class="py-2 px-4 border-b">Pembulatan</th>
                          <th class="py-2 px-4 border-b">Aksi</th>
                      </tr>
                  </thead>
                  <tbody>
                      @php
                          $grouped = $grouping->groupBy('herbisidagroupid');
                      @endphp
                      
                      @foreach ($grouped as $groupId => $items)
                          @foreach ($items as $index => $data)
                              <tr class="hover:bg-gray-50">
                                  @if($index === 0)
                                      <td class="py-2 px-4 border-b text-center bg-gray-50" rowspan="{{ $items->count() }}">
                                          {{ $groupId }}
                                      </td>
                                      <td class="py-2 px-4 border-b font-semibold bg-gray-50" rowspan="{{ $items->count() }}">
                                          {{ $data->herbisidagroupname }}
                                      </td>
                                      <td class="py-2 px-4 border-b bg-gray-50" rowspan="{{ $items->count() }}">
                                          {{ $data->activitycode }}
                                      </td>
                                      <td class="py-2 px-4 border-b text-xs bg-gray-50" rowspan="{{ $items->count() }}">
                                          <div class="max-w-xs whitespace-pre-line">{{ $data->description }}</div>
                                      </td>
                                  @endif
                                  
                                  <td class="py-2 px-4 border-b">{{ $data->itemcode }}</td>
                                  <td class="py-2 px-4 border-b">{{ $data->itemname ?? '-' }}</td>
                                  <td class="py-2 px-4 border-b text-right">{{ $data->dosageperha }}</td>
                                  
                                  @if($index === 0)
                                      <td class="py-1 px-2 border-b text-center bg-gray-50 text-[11px]" rowspan="{{ $items->count() }}">
                                          @if($data->rounddosage == 1)
                                              <span class="text-blue-700 font-semibold">Pembulatan</span>
                                          @else
                                              <span class="text-gray-400">Tidak ada pembulatan</span>
                                          @endif
                                      </td>
                                      <td class="py-2 px-4 border-b" rowspan="{{ $items->count() }}">
                                          <div class="flex items-center justify-center space-x-2">
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
                                              class="text-blue-600 hover:text-blue-800">
                                                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                  </svg>
                                              </button>
                                              @endcan
                                              
                                              @can('masterdata.herbisidagroup.delete')
                                              <form action="{{ url("masterdata/herbisida-group/{$groupId}") }}" 
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin hapus group ini?');">
                                                  @csrf
                                                  @method('DELETE')
                                                  <button type="submit" class="text-red-600 hover:text-red-800">
                                                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                      @endforeach
                  </tbody>
              </table>
          </div>

          {{-- Pagination --}}
          <div class="mt-4">
              {{ $grouping->appends(['search' => request('search'), 'perPage' => request('perPage', 10)])->links() }}
          </div>
      </div>

      {{-- Toast --}}
      @if(session('success'))
          <div x-data x-init="alert('{{ session('success') }}')"></div>
      @endif
  </div>
</x-layout>