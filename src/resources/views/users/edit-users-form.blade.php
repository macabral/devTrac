<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Project') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl" dusk="update-profile-information">
                    <section>
                        <header>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __("") }}
                            </p>
                        </header>
                    
                        <x-splade-modal>
                            <x-splade-form method="patch" :action="route('users.update', base64_encode($ret->id))" :default="$ret" class="mt-4 space-y-4" preserve-scroll>
                                @include('users.users-form')
                            </x-splade-form>

                            <div>
                                <x-splade-form method="get" :action="route('users.reset', base64_encode($ret->id))"  class="mt-4 space-y-4" preserve-scroll
                                    confirm="Confirma Resetar a Senha do Usuário?"
                                    confirm-text="Uma nova senha será gerada e encaminhada ao Usuário."
                                    confirm-button="Confirmar"
                                    cancel-button="Cancelar"
                                >
                                    <div class="inline-flex gap-4">
                                        <x-splade-submit :label="__('Password Reset')" />
                                    </div>
                                </x-splade-form>
                            </div>


                        </x-splade-modal>
                        
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>