<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>
  
  <div class="w-full">
    {{-- Filter Bar Modern --}}
    <form method="GET" class="mx-auto px-4 pt-4">
      <div class="max-w-5xl mx-auto bg-gray-50 border border-gray-300 rounded-lg p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
          
          {{-- Search --}}
          <div class="md:col-span-4 relative">
            <input
              type="text"
              name="search"
              id="search"
              value="{{ $search ?? '' }}"
              placeholder=" "
              class="peer w-full p-2 pt-5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500"
            />
            <label for="search" class="absolute left-2 top-1 text-xs text-gray-600 transition-all peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-400 peer-focus:top-1 peer-focus:text-xs peer-focus:text-blue-600">
              Search RKH / Mandor
            </label>
          </div>

          {{-- Start Date --}}
          <div class="md:col-span-2 relative">
            <input
              type="date"
              id="start_date"
              name="start_date"
              value="{{ $startDate ?? '' }}"
              placeholder=" "
              class="peer w-full p-2 pt-5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 {{ $startDate ? 'text-black' : 'text-gray-400' }}"
            />
            <label for="start_date" class="absolute left-2 top-1 text-xs text-gray-600 peer-focus:text-blue-600">
              Start Date
            </label>
          </div>

          {{-- End Date --}}
          <div class="md:col-span-2 relative">
            <input
              type="date"
              id="end_date"
              name="end_date"
              value="{{ $endDate ?? '' }}"
              placeholder=" "
              class="peer w-full p-2 pt-5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 {{ $endDate ? 'text-black' : 'text-gray-400' }}"
            />
            <label for="end_date" class="absolute left-2 top-1 text-xs text-gray-600 peer-focus:text-blue-600">
              End Date
            </label>
          </div>

          {{-- Per Page --}}
          <div class="md:col-span-1 relative">
            <input
              type="text"
              name="perPage"
              id="perPage"
              value="{{ $perPage ?? 15 }}"
              placeholder=" "
              class="peer w-full p-2 pt-5 border border-gray-300 rounded-md text-sm text-center focus:ring-2 focus:ring-blue-500"
            />
            <label for="perPage" class="absolute left-2 top-1 text-xs text-gray-600 peer-focus:text-blue-600">
              Per page
            </label>
          </div>

          {{-- Buttons --}}
          <div class="md:col-span-3 flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
              Apply
            </button>
            @if(request()->hasAny(['search','start_date','end_date']) || request('perPage') != 15)
              <a href="{{ url()->current() }}" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium text-center">
                Reset
              </a>
            @endif
            <button type="submit"
              formaction="{{ route('transaction.gudang.report') }}" formmethod="GET"
              class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">
              Report
            </button>
          </div>
          
        </div>
      </div>
    </form>

    {{-- Table --}}
    <div class="mx-auto px-4 py-4">
      <div class="overflow-x-auto rounded-md border border-gray-300">
        <table class="min-w-full bg-white text-sm">
          <thead>
            <tr class="bg-gray-100">
              <th class="py-2 px-4 border-b border-gray-300 text-gray-700">No. RKH</th>
              <th class="py-2 px-4 border-b border-gray-300 text-gray-700">Tanggal</th>
              <th class="py-2 px-4 border-b border-gray-300 text-gray-700">Keterangan</th>
              <th class="py-2 px-4 border-b border-gray-300 text-gray-700">No. Pemakaian</th>
              <th class="py-2 px-4 border-b border-gray-300 text-gray-700">Luas</th>
              <th class="py-2 px-4 border-b border-gray-300 text-gray-700">Mandor</th>
              <th class="py-2 px-4 border-b border-gray-300 text-gray-700">Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($usehdr as $u) 
              <tr class="hover:bg-gray-50">
                <td class="py-2 px-4 border border-gray-300">
                  <a href="{{ route('transaction.gudang.detail', ['rkhno' => $u->rkhno]) }}" class="text-blue-600 hover:underline">
                    {{ $u->rkhno }} @if($u->nouse)
                                      <span class="inline-block px-2 py-1 text-xs font-semibold text-white bg-green-600 rounded-full">
                                        Generated
                                      </span>
                                    @endif
                  </a>
                </td>
                <td class="py-2 px-4 border border-gray-300 text-center">{{ date('d M Y', strtotime($u->rkhdate)) }}</td>
                <td class="py-2 px-4 border border-gray-300">Pre Emergence</td>
                <td class="py-2 px-4 border border-gray-300">{{ $u->nouse }}</td>
                <td class="py-2 px-4 border border-gray-300 text-right">{{ $u->totalluas }}</td>
                <td class="py-2 px-4 border border-gray-300">{{ $u->name }}</td>
                <td class="py-2 px-4 border border-gray-300 text-center">
                  <span class="inline-block px-2 py-1 text-xs font-semibold text-white rounded-full
                    {{ strtolower($u->flagstatus) == 'active' ? 'bg-blue-700' : '' }}
                    {{ strtolower($u->flagstatus) == 'dispatched' ? 'bg-yellow-700' : '' }}
                    {{ strtolower($u->flagstatus) == 'received_by_mandor' ? 'bg-green-700' : '' }}
                    {{ strtolower($u->flagstatus) == 'returned_by_mandor' ? 'bg-orange-700' : '' }}
                    {{ strtolower($u->flagstatus) == 'return_received' ? 'bg-green-700' : '' }}
                    {{ strtolower($u->flagstatus) == 'completed' ? 'bg-gray-600' : 'bg-gray-500' }}">
                    {{ $u->flagstatus }}
                  </span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    {{-- Pagination --}}
    <div class="px-4 py-2">
      {{ $usehdr->links() }}
    </div>
  </div>

  {{-- Script Minimal --}}
  <script>
    // Validasi perPage - hanya angka
    document.addEventListener("DOMContentLoaded", () => {
      const perPageInput = document.getElementById("perPage");
      if (perPageInput) {
        perPageInput.addEventListener("input", (e) => {
          e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
      }
    });
  </script>
</x-layout>