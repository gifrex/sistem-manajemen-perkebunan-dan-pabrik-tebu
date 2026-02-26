<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div
    x-data="{
      open: @json($errors->any()),
      mode: '{{ old('category') ? 'edit' : 'create' }}',
      form: {
        companycode: '',
        companycodeoriginal: '',
        category: '',
        categoryoriginal: '',
        activitygroup: '',
        jumlahapproval: '1',
        idjabatanapproval1: '',
        idjabatanapproval2: '',
        idjabatanapproval3: '',
        idjabatanapproval4: '',
        idjabatanapproval5: ''
      },
      resetForm() {
        this.mode = 'create';
        this.form = {
          companycode: '',
          companycodeoriginal: '',
          category: '',
          categoryoriginal: '',
          activitygroup: '',
          jumlahapproval: '1',
          idjabatanapproval1: '',
          idjabatanapproval2: '',
          idjabatanapproval3: '',
          idjabatanapproval4: '',
          idjabatanapproval5: ''
        };
        this.open = true;
      },
      showJabatan(n) {
        return parseInt(this.form.jumlahapproval) >= n;
      }
    }"
    x-init="
      if ({{ $errors->any() ? 'true' : 'false' }}) {
        form.category           = '{{ old('category') }}';
        form.activitygroup      = '{{ old('activitygroup') }}';
        form.jumlahapproval     = '{{ old('jumlahapproval', '1') }}';
        form.idjabatanapproval1 = '{{ old('idjabatanapproval1') }}';
        form.idjabatanapproval2 = '{{ old('idjabatanapproval2') }}';
        form.idjabatanapproval3 = '{{ old('idjabatanapproval3') }}';
        form.idjabatanapproval4 = '{{ old('idjabatanapproval4') }}';
        form.idjabatanapproval5 = '{{ old('idjabatanapproval5') }}';
      }
    "
    class="mx-auto py-1 bg-white rounded-md shadow-md">

    {{-- Toolbar --}}
    <div class="flex items-center justify-between px-4 py-2">
      @can('masterdata.approval.create')
        <button @click="resetForm()"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2">
          <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5" />
          </svg>
          New Data
        </button>
      @endcan

      <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        <label for="search" class="text-xs font-medium text-gray-700">Search:</label>
        <input type="text" name="search" id="search" value="{{ request('search') }}"
               placeholder="Category atau Activity Group"
               class="text-xs mt-1 block w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
               onkeydown="if(event.key==='Enter') this.form.submit()">
      </form>

      <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        <label for="perPage" class="text-xs font-medium text-gray-700">Items per page:</label>
        <select name="perPage" id="perPage" onchange="this.form.submit()"
                class="text-xs mt-1 block w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
          <option value="10" {{ (int)request('perPage', $perPage) === 10 ? 'selected' : '' }}>10</option>
          <option value="20" {{ (int)request('perPage', $perPage) === 20 ? 'selected' : '' }}>20</option>
          <option value="50" {{ (int)request('perPage', $perPage) === 50 ? 'selected' : '' }}>50</option>
        </select>
      </form>
    </div>

    {{-- Modal Form --}}
    <div x-show="open" x-cloak class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-500/75" aria-hidden="true"></div>
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
                    ? '{{ url('masterdata/approval') }}/' + form.companycodeoriginal + '/' + encodeURIComponent(form.categoryoriginal)
                    : '{{ url('masterdata/approval') }}'"
                  class="bg-white px-4 pt-2 pb-4 sm:p-6 sm:pt-1 sm:pb-4 space-y-4">
              @csrf
              <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PATCH"></template>

              <div class="text-center sm:text-left">
                <h3 class="text-lg font-medium text-gray-900 mt-2" id="modal-title"
                    x-text="mode === 'edit' ? 'Edit Approval' : 'Create Approval'"></h3>

                <div class="mt-4 space-y-4">

                  {{-- Category --}}
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Category <span class="text-red-500">*</span></label>
                    <input type="text" name="category" x-model="form.category"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           maxlength="150" required>
                    @error('category')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                  </div>

                  {{-- Activity Group --}}
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Activity Group</label>
                    <input type="text" name="activitygroup" x-model="form.activitygroup"
                           placeholder="Contoh: I, II, III (opsional)"
                           class="mt-1 block w-1/3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           maxlength="5">
                    @error('activitygroup')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                  </div>

                  {{-- Jumlah Approval --}}
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Jumlah Approval <span class="text-red-500">*</span></label>
                    <select name="jumlahapproval" x-model="form.jumlahapproval"
                            class="mt-1 block w-1/3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            required>
                      @foreach(range(1, 5) as $n)
                        <option value="{{ $n }}">{{ $n }}</option>
                      @endforeach
                    </select>
                    @error('jumlahapproval')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                  </div>

                  {{-- Jabatan Approval 1 - 5 --}}
                  <div class="space-y-3">
                    @foreach(range(1, 5) as $n)
                      <div @if($n > 1) x-show="showJabatan({{ $n }})" @endif>
                        <label class="block text-sm font-medium text-gray-700">
                          Jabatan Approval {{ $n }}
                          @if($n === 1)<span class="text-red-500">*</span>@endif
                          @if($n > 1)<span x-show="showJabatan({{ $n }})" class="text-red-500">*</span>@endif
                        </label>
                        <select name="idjabatanapproval{{ $n }}"
                                x-model="form.idjabatanapproval{{ $n }}"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                @if($n === 1) required @else :required="showJabatan({{ $n }})" @endif>
                          <option value="">-- Pilih Jabatan --</option>
                          @foreach($jabatan as $item)
                            <option value="{{ $item->idjabatan }}">{{ $item->namajabatan }}</option>
                          @endforeach
                        </select>
                        @error("idjabatanapproval{$n}")<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                      </div>
                    @endforeach
                  </div>

                </div>
              </div>

              <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button type="submit"
                        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-4 py-2 text-white text-sm font-medium shadow-sm hover:bg-blue-700 sm:ml-3 sm:w-auto"
                        x-text="mode === 'edit' ? 'Update' : 'Create'">
                </button>
                <button @click.prevent="open = false" type="button"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-gray-700 text-sm font-medium shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                  Cancel
                </button>
              </div>
            </form>
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
              <th class="py-2 px-4 border-b">Activity Group</th>
              <th class="py-2 px-4 border-b">Category</th>
              <th class="py-2 px-4 border-b">Jumlah</th>
              <th class="py-2 px-4 border-b">Approval 1</th>
              <th class="py-2 px-4 border-b">Approval 2</th>
              <th class="py-2 px-4 border-b">Approval 3</th>
              <th class="py-2 px-4 border-b">Approval 4</th>
              <th class="py-2 px-4 border-b">Approval 5</th>
              <th class="py-2 px-4 border-b">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($approval as $index => $data)
              <tr class="hover:bg-gray-50">
                <td class="py-2 px-4 border-b">{{ $approval->firstItem() + $index }}</td>
                <td class="py-2 px-4 border-b">{{ $data->activitygroup ?? '-' }}</td>
                <td class="py-2 px-4 border-b text-left">{{ $data->category }}</td>
                <td class="py-2 px-4 border-b">{{ $data->jumlahapproval }}</td>
                <td class="py-2 px-4 border-b text-left">{{ optional($data->jabatanApproval1)->namajabatan ?? '-' }}</td>
                <td class="py-2 px-4 border-b text-left">{{ optional($data->jabatanApproval2)->namajabatan ?? '-' }}</td>
                <td class="py-2 px-4 border-b text-left">{{ optional($data->jabatanApproval3)->namajabatan ?? '-' }}</td>
                <td class="py-2 px-4 border-b text-left">{{ optional($data->jabatanApproval4)->namajabatan ?? '-' }}</td>
                <td class="py-2 px-4 border-b text-left">{{ optional($data->jabatanApproval5)->namajabatan ?? '-' }}</td>
                <td class="py-2 px-4 border-b">
                  <div class="flex items-center justify-center space-x-2">
                    @can('masterdata.approval.edit')
                      <button @click="
                        mode = 'edit';
                        form.companycodeoriginal  = '{{ $data->companycode }}';
                        form.categoryoriginal     = '{{ addslashes($data->category) }}';
                        form.category             = '{{ addslashes($data->category) }}';
                        form.activitygroup        = '{{ $data->activitygroup }}';
                        form.jumlahapproval       = '{{ $data->jumlahapproval }}';
                        form.idjabatanapproval1   = '{{ $data->idjabatanapproval1 }}';
                        form.idjabatanapproval2   = '{{ $data->idjabatanapproval2 }}';
                        form.idjabatanapproval3   = '{{ $data->idjabatanapproval3 }}';
                        form.idjabatanapproval4   = '{{ $data->idjabatanapproval4 }}';
                        form.idjabatanapproval5   = '{{ $data->idjabatanapproval5 }}';
                        open = true"
                        class="group flex items-center">
                        <svg class="w-6 h-6 text-blue-500 group-hover:hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z" />
                        </svg>
                        <svg class="w-6 h-6 text-blue-500 hidden group-hover:block" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                          <path fill-rule="evenodd"
                                d="M11.32 6.176H5c-1.105 0-2 .949-2 2.118v10.588C3 20.052 3.895 21 5 21h11c1.105 0 2-.948 2-2.118v-7.75l-3.914 4.144A2.46 2.46 0 0 1 12.81 16l-2.681.568c-1.75.37-3.292-1.263-2.942-3.115l.536-2.839c.097-.512.335-.983.684-1.352l2.914-3.086Z"
                                clip-rule="evenodd" />
                          <path fill-rule="evenodd"
                                d="M19.846 4.318a2.148 2.148 0 0 0-.437-.692 2.014 2.014 0 0 0-.654-.463 1.92 1.92 0 0 0-1.544 0 2.014 2.014 0 0 0-.654.463l-.546.578 2.852 3.02.546-.579a2.14 2.14 0 0 0 .437-.692 2.244 2.244 0 0 0 0-1.635ZM17.45 8.721 14.597 5.7 9.82 10.76a.54.54 0 0 0-.137.27l-.536 2.84c-.07.37.239.696.588.622l2.682-.567a.492.492 0 0 0 .255-.145l4.778-5.06Z"
                                clip-rule="evenodd" />
                        </svg>
                      </button>
                    @endcan

                    @can('masterdata.approval.delete')
                      <form method="POST"
                            action="{{ url('masterdata/approval/'.$data->companycode.'/'.urlencode($data->category)) }}"
                            onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="group flex items-center">
                          <svg class="w-6 h-6 text-red-500 group-hover:hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <use xlink:href="#icon-trash-outline"/>
                          </svg>
                          <svg class="w-6 h-6 text-red-500 hidden group-hover:block" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                            <use xlink:href="#icon-trash-solid"/>
                          </svg>
                        </button>
                      </form>
                    @endcan
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="10" class="py-4 text-center text-gray-500">Tidak ada data</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Pagination --}}
    <div class="mx-4 my-1">
      {{ $approval->appends(['perPage' => $approval->perPage(), 'search' => $search])->links() }}
    </div>

    {{-- Toast --}}
    @if(session('success'))
      <div x-data x-init="alert('{{ session('success') }}')"></div>
    @endif
  </div>
</x-layout>