{{-- resources/views/transaction/rencanakerjaharian/lkh-edit-v2.blade.php --}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div x-data="lkhEditWizard()" class="max-w-7xl mx-auto px-4 pb-6">
    
    {{-- STICKY Progress Bar --}}
    <div class="sticky top-[6rem] z-30 bg-white border-b border-gray-200 shadow-sm mb-6">
      <div class="max-w-7xl mx-auto px-6 py-4">
        <div class="flex items-center justify-between relative">
          
          {{-- Progress Line Background --}}
          <div class="absolute top-5 left-0 right-0 h-0.5 bg-gray-200 -z-10"></div>
          
          {{-- Active Progress Line --}}
          <div class="absolute top-5 left-0 h-0.5 bg-blue-600 transition-all duration-500 -z-10"
               :style="`width: ${((currentStep - 1) / 3) * 100}%`"></div>

          {{-- Step Circles --}}
          <template x-for="(step, index) in steps" :key="step.id">
            <div class="flex flex-col items-center flex-1 relative">
              <div 
                class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 cursor-pointer hover:scale-110 z-10 text-sm font-bold"
                :class="{
                  'bg-blue-600 text-white shadow-md': currentStep === step.id,
                  'bg-green-600 text-white shadow-sm': currentStep > step.id,
                  'bg-white border-2 border-gray-300 text-gray-400': currentStep < step.id
                }"
                @click="currentStep > step.id ? goToStep(step.id) : null">
                <template x-if="currentStep > step.id">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                  </svg>
                </template>
                <template x-if="currentStep <= step.id">
                  <span x-text="step.id"></span>
                </template>
              </div>
              <div class="mt-2 text-center">
                <p class="text-xs font-semibold transition-colors"
                   :class="currentStep >= step.id ? 'text-gray-800' : 'text-gray-400'"
                   x-text="step.title"></p>
              </div>
            </div>
          </template>
        </div>

        {{-- Current Step Info --}}
        <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-100">
          <div class="text-sm text-gray-600 flex items-center gap-3">
            <span class="flex items-center gap-1">
              <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              <strong class="font-mono">{{ $lkhData->lkhno }}</strong>
            </span>
            <span class="text-gray-400">•</span>
            <span>{{ \Carbon\Carbon::parse($lkhData->lkhdate)->format('d M Y') }}</span>
            <span class="text-gray-400">•</span>
            <span class="px-2 py-0.5 rounded text-xs font-medium {{ $lkhData->jenistenagakerja == 1 ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
              {{ $lkhData->jenistenagakerja == 1 ? 'Harian' : 'Borongan' }}
            </span>
            {{-- Dirty Indicator --}}
            <span x-show="isDirty && jenistenagakerja == 1" 
                  class="px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700 animate-pulse">
              ⚠️ Needs Recalculation
            </span>
          </div>
          <div class="flex items-center gap-3">
            <div class="text-xs text-gray-500">
              Step <span class="font-bold" x-text="currentStep"></span> of 4
            </div>
            <button 
              type="button"
              @click="nextStep()" 
              x-show="currentStep < 4"
              :disabled="!canProceed()"
              :class="canProceed() ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
              class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all flex items-center gap-1">
              <span x-text="currentStep === 3 ? 'Review' : 'Next'"></span>
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    {{-- Main Wizard Container --}}
    <div class="bg-white rounded-lg shadow border border-gray-200">
      <div class="p-6">

        {{-- STEP 1: Plot Details --}}
        <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.lkh-edit-steps.step1-plots')
        </div>

        {{-- STEP 2: Worker Details --}}
        <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.lkh-edit-steps.step2-workers')
        </div>

        {{-- STEP 3: Material Details --}}
        <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.lkh-edit-steps.step3-materials')
        </div>

        {{-- STEP 4: Review & Submit --}}
        <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.lkh-edit-steps.step4-review')
        </div>
      </div>

      {{-- Navigation Footer --}}
      <div class="bg-gray-50 border-t border-gray-200 px-6 py-4">
        <div class="flex justify-between items-center">
          <button type="button" @click="prevStep()" x-show="currentStep > 1"
            class="px-5 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Previous
          </button>
          <div x-show="currentStep === 1">
            <button type="button" onclick="window.history.back()" 
              class="px-5 py-2 bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 rounded-lg text-sm font-medium transition-all flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              Cancel
            </button>
          </div>
          <button type="button" @click="nextStep()" x-show="currentStep < 4"
            :disabled="!canProceed()"
            :class="canProceed() ? 'bg-blue-600 hover:bg-blue-700 shadow-md' : 'bg-gray-300 cursor-not-allowed'"
            class="px-5 py-2 text-white rounded-lg text-sm font-medium transition-all flex items-center gap-2">
            <span x-text="getNextButtonText()"></span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </button>
          <button type="button" id="submit-btn" @click="submitForm()" x-show="currentStep === 4"
            :disabled="isSubmitting"
            :class="isSubmitting ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-700 hover:shadow-lg'"
            class="px-8 py-2.5 bg-green-600 text-white rounded-lg text-sm font-semibold transition-all flex items-center gap-2 shadow-md">
            <svg x-show="!isSubmitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <svg x-show="isSubmitting" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span x-show="!isSubmitting">Save Changes</span>
            <span x-show="isSubmitting">Saving...</span>
          </button>
        </div>
      </div>
    </div>

    {{-- Success Modal --}}
    @include('transaction.rencanakerjaharian.lkh-edit-steps.success-modal')

    {{-- Validation Modal --}}
    @include('transaction.rencanakerjaharian.lkh-edit-steps.validation-modal')

    {{-- Recalculate Warning Modal --}}
    @include('transaction.rencanakerjaharian.lkh-edit-steps.recalculate-modal')
  </div>

  @push('scripts')
  @include('transaction.rencanakerjaharian.lkh-edit-steps.wizard-script')
  @endpush
</x-layout>