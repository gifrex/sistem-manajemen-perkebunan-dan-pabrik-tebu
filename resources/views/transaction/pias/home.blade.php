<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div class="w-full">
    {{-- Filter Bar --}}

    <form method="GET" class="mx-auto px-4 pt-4">
      <div class="max-w-5xl mx-auto bg-gray-50 border border-gray-300 rounded-lg p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
          
          {{-- Search (takes more space) --}}
          <div class="md:col-span-4 relative">
            <input
              type="text"
              name="search"
              id="search"
              value="{{ $search ?? '' }}"
              placeholder=" "
              class="peer w-full p-2 pt-5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <label 
              for="search" 
              class="absolute left-2 top-1 text-xs text-gray-600 transition-all peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-400 peer-focus:top-1 peer-focus:text-xs peer-focus:text-blue-600"
            >
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
              class="peer w-full p-2 pt-5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $startDate ? 'text-black' : 'text-gray-400' }}"
            />
            <label 
              for="start_date" 
              class="absolute left-2 top-1 text-xs text-gray-600 peer-focus:text-blue-600"
            >
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
              class="peer w-full p-2 pt-5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $endDate ? 'text-black' : 'text-gray-400' }}"
            />
            <label 
              for="end_date" 
              class="absolute left-2 top-1 text-xs text-gray-600 peer-focus:text-blue-600"
            >
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
            <label 
              for="perPage" 
              class="absolute left-2 top-1 text-xs text-gray-600 peer-focus:text-blue-600"
            >
              Per page
            </label>
          </div>
    
          {{-- Buttons --}}
          <div class="md:col-span-3 flex gap-2">
            <button
              type="submit"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 text-sm font-medium"
            >
              Apply
            </button>
            <a href="{{ route('transaction.pias.report', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
               class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium text-center whitespace-nowrap">
              Report
            </a>
            
            @if(request()->hasAny(['search','start_date','end_date']) || request('perPage') != 15)
              <a 
                href="{{ route('transaction.pias.index') }}" 
                class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium text-center"
              >
                Reset
              </a>
            @endif
          </div>
          
        </div>
      </div>
    </form>


    {{-- Tabel --}}
    <div class="mx-auto px-4 py-4">
      <div class="overflow-x-auto rounded-md border border-gray-300">
        <table class="min-w-full bg-white text-sm">
          <thead>
            <tr>
              <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">No.</th>
              <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">RKH</th>
              <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Tanggal</th>
              <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Total Luas</th>
              <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Man Power</th>
              <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Mandor</th>
              <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Approved By</th>
            </tr>
          </thead>
          <tbody>
            @forelse($data as $item)
              <tr> 
                <td class="py-2 px-4 border border-gray-300 text-lg text-center">{{ $loop->iteration }}</td>
                <td class="py-2 px-4 border border-gray-300">
                  <a href="{{ route('transaction.pias.detail', ['rkhno' => $item->rkhno]) }}" target="_blank" class="text-blue-600 hover:underline">
                    {{ $item->rkhno }}  
                    @if($item->is_generated)
                    <span class="inline-block px-2 py-1 text-xs font-semibold text-white rounded-full bg-green-600">
                      Generated
                    </span> @endif
                  </a>
                </td>
                <td class="py-2 px-4 border border-gray-300 text-lg text-center">{{ date('d M Y', strtotime($item->rkhdate)) }}</td>
                <td class="py-2 px-4 border border-gray-300 text-lg text-right">{{ $item->totalluas ?? 'N/A' }}</td>
                <td class="py-2 px-4 border border-gray-300 text-lg text-right">{{ $item->manpower ?? 'N/A' }}</td>
                <td class="py-2 px-4 border border-gray-300 text-lg text-center">{{ $item->mandor_name ?? 'N/A' }}</td>
                <td class="py-2 px-4 border border-gray-300 text-lg text-center"> @if( $item->{'approval'.$item->jumlahapproval.'flag'} == 1 ) 
                  {{ strstr($item->{'approval'.$item->jumlahapproval.'userid'},'_',true) }}  @endif </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="py-4 text-center text-gray-500">
                  Tidak ada RKH yang sudah selesai
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Pagination --}}
    <div class="px-4 py-2">
      {{-- links() sudah di-appends() dari controller, jadi query filter tetap nempel --}}
      {{ $data->links() }}
    </div>
  </div>

  {{-- Script kecil untuk UX --}}
  <script>
    // Hanya angka untuk perPage
    document.addEventListener("DOMContentLoaded", () => {
      const inputElement = document.getElementById("perPage");
      if (inputElement) {
        inputElement.addEventListener("input", (event) => {
          event.target.value = event.target.value.replace(/[^0-9]/g, '');
        });
      }
    });

    // Klik di luar dropdown => tutup
    document.addEventListener("click", function(event) {
      const dropdown = document.getElementById("menu-dropdown");
      const button = document.getElementById("menu-button");
      if (!dropdown || !button) return;
      if (!dropdown.contains(event.target) && !button.contains(event.target)) {
        dropdown.classList.add("hidden");
      }
    });
  </script>
</x-layout>
