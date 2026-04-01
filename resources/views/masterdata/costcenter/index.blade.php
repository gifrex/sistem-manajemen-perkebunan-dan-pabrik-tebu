<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div 
    x-data="{
      open: false,
      form: {
        herbisidagroupid: '',
        herbisidagroupname: '',
        activitycode: '',
        costcenter: '',
        description: ''
      }
    }"
    class="mx-auto py-2 bg-white rounded-md shadow-md">

    {{-- HEADER --}}
    <div class="flex items-center justify-between px-4 py-2">

      {{-- SEARCH --}}
      <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
          class="text-xs border rounded-md px-2 py-1"
          placeholder="Search...">
      </form>

    </div>

    {{-- MODAL --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-10 bg-gray-500/75 flex items-center justify-center">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">

        <form method="POST"
          :action="'{{ url('masterdata/costcenter') }}/' + form.herbisidagroupid">

          @csrf
          <input type="hidden" name="_method" value="PATCH">

          <h3 class="text-lg font-bold mb-4">Setup Cost Center</h3>

          <div class="space-y-3">

            <div>
              <label>Group ID</label>
              <input type="text" x-model="form.herbisidagroupid"
                class="w-full border rounded px-2 py-1 bg-gray-100" readonly>
            </div>

            <div>
              <label>Group Name</label>
              <input type="text" x-model="form.herbisidagroupname"
                class="w-full border rounded px-2 py-1 bg-gray-100" readonly>
            </div>

            <div>
              <label>Activity</label>
              <input type="text" x-model="form.activitycode"
                class="w-full border rounded px-2 py-1 bg-gray-100" readonly>
            </div>

            <div>
              <label>Cost Center</label>
              <input type="text" name="costcenter" x-model="form.costcenter"
                class="w-full border rounded px-2 py-1 uppercase"
                required>
            </div>

            <div>
              <label>Description</label>
              <input type="text" name="description" x-model="form.description"
                class="w-full border rounded px-2 py-1" required>
            </div>

          </div>

          <div class="flex justify-end gap-2 mt-4">
            <button type="button" @click="open=false"
              class="px-3 py-1 border rounded">Cancel</button>

            <button type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">Save</button>
          </div>

        </form>

      </div>
    </div>

    {{-- TABLE --}}
    <div class="px-4 py-2">
      <div class="overflow-x-auto border rounded">
        <table class="min-w-full text-sm text-center">

          <thead class="bg-gray-100">
            <tr>
              <th>Group ID</th>
              <th>Name</th>
              <th>Activity</th>
              <th>Cost Center</th>
              <th>Description</th>
              <th>Aksi</th>
            </tr>
          </thead>

          <tbody>
            @foreach ($costcenter as $i => $d)
            <tr class="{{ !$d->costcenter ? 'bg-red-50' : 'hover:bg-gray-50' }}">

              <td>{{ $d->herbisidagroupid }}</td>
              <td>{{ $d->herbisidagroupname }}</td>
              <td>{{ $d->activitycode }}</td>

              <td>
                @if($d->costcenter)
                  {{ $d->costcenter }}
                @else
                  <span class="text-red-500 font-semibold">Belum diisi</span>
                @endif
              </td>

              <td>{{ $d->description ?? '-' }}</td>

              <td class="flex justify-center gap-2">

                {{-- EDIT / ISI --}}
                <button
                @click="
                    form.herbisidagroupid = '{{ $d->herbisidagroupid }}';
                    form.herbisidagroupname = @js($d->herbisidagroupname);
                    form.activitycode = @js($d->activitycode);
                    form.costcenter = @js($d->costcenter);
                    form.description = @js($d->description);
                    open = true;
                "
                class="px-3 py-1 rounded-md text-sm font-medium
                    {{ $d->costcenter ? 'bg-blue-100 text-blue-700' : 'bg-green-600 text-white' }}">
                
                {{ $d->costcenter ? 'Edit' : 'Set' }}
                </button>

                {{-- DELETE --}}
                @if($d->costcenter)
                <form method="POST"
                  action="{{ url('masterdata/costcenter/'.$d->herbisidagroupid) }}"
                  onsubmit="return confirm('Hapus data?')">
                  @csrf
                  @method('DELETE')
                  <button class="text-red-600">Delete</button>
                </form>
                @endif

              </td>

            </tr>
            @endforeach
          </tbody>

        </table>
      </div>
    </div>

    {{-- SUCCESS --}}
    @if(session('success'))
      <script>alert("{{ session('success') }}")</script>
    @endif

  </div>
</x-layout>