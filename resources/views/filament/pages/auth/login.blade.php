<x-filament-panels::page.simple>
@push('styles')
<style>
    /* Base styles */
    .fi-simple-layout{
        background-color: #b8510c !important;
        margin: 0;
        padding: 0;
    }
    .fi-simple-main {
        height: 100vh;
        margin: 0;
        border-radius: 0;
        background-color: #b8510c !important;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
    }
    .fi-simple-main .fi-simple-header{
        
    }

    .fi-simple-page {
        width: 100%;
    }

    .fi-simple-main-ctn {
        display: flex;
        flex-direction: row;
        /* flex-wrap: wrap; */
        width: 100%;
        padding: 0;
        margin: 0;
    }

    .bg-emas {
        background-image: url('/bg-login.jpeg');
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        width: 100%;
        height: 100vh;
        top: 0;
    }

    /* Mobile (≤600px) */
    @media (max-width: 600px) {
        .fi-simple-main {
            flex-direction: column;
            height: auto;
            /* padding-bottom: 1rem; */
        }

        .bg-emas {
            height: 30vh;
        }

        .fi-simple-main-ctn {
            flex-direction: column;
        }
    }

    /* Tablet (601px - 1024px) */
    @media (min-width: 601px) and (max-width: 1024px) {

        
        .fi-simple-main {
            flex-direction: column;
            height: auto;
            width: 100%;
        }

        .bg-emas {
            height: 50vh;
            top: 0;
        }

        .fi-simple-main-ctn {
            flex-direction: column;
        }
    }

    /* Desktop (≥1025px) */
    @media (min-width: 1025px) {
        .fi-simple-main-ctn {
            flex-direction: row;
            justify-content: center;
        }

        .bg-emas {
            height: 100vh;
        }
    }
</style>
@endpush
<x-filament-panels::form id="form" wire:submit="authenticate">
    <div class="parent">
        {{-- add tag di sini dengan class="bg-emas" --}}
        <div class="child"></div>
    </div>

    <!-- Form Fields for Username and Password -->
    {{ $this->form }}

    <!-- Form Actions (Submit Buttons, etc.) -->
    <x-filament-panels::form.actions :actions="$this->getCachedFormActions()"
        :full-width="$this->hasFullWidthFormActions()" />
</x-filament-panels::form>

@push('scripts')
    <script>
        const parent = document.querySelector('.fi-simple-main-ctn');
        const newElement = document.createElement('div');
        newElement.classList.add('bg-emas');
        parent.insertBefore(newElement, parent.firstChild); 
    </script>
@endpush
</x-filament-panels::page.simple>
