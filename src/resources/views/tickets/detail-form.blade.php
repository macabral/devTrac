<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ticket') }} #{{ $ret['id'] }}
        </h2>
        <a href="javascript:history.back()" class="pc-4 py-2 bg-indigo-400 hover:bg-indigo-600 text-black rounded-md">
            {{ __('Back') }}
        </a>
    </x-slot>

    <div class="py-12">
        <div class="max-w-1xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section>
                        <header>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __("") }}
                            </p>
                        </header>
                        <div>
                            <p class="mt-1 text-xl pb-2 pt-2 text-blue-800">
                                {!! nl2br($ret->title) !!}
                            </p>
                            <p class="mt-1 text-base pb-2 pt-2 text-blue-800">
                                {!! nl2br($ret->description) !!}
                            </p>
                        </div>
                        <div class="pt-6">
                            <table class="min-w-full text-center border-collapse border border-slate-400">
                                <thead class="border-b bg-neutral-800 font-medium  dark:border-neutral-500 dark:bg-neutral-900">
                                    <tr>
                                        <th class="border border-slate-300">{{ __('Type') }}</th>
                                        <th class="border border-slate-300">{{ __('Release') }}</th>
                                        <th class="border border-slate-300">{{ __('Relator') }}</th>
                                        <th class="border border-slate-300">{{ __('Assign to') }}</th>
                                        <th class="border border-slate-300">{{ __('Created At') }}</th>
                                        <th class="border border-slate-300">{{ __('Status') }}</th>
                                        <th class="border border-slate-300">{{ __('Files') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="border border-slate-300">{{ $ret->type }}</td>
                                        <td class="border border-slate-300">{{ $ret->release }}</td>
                                        <td class="border border-slate-300">{{ $ret->relator }}</td>
                                        <td class="border border-slate-300">{{ $ret->resp }}</td>
                                        <td class="border border-slate-300">{{ date('d/m/Y H:i', strtotime($ret->created_at)) }}</td>  
                                        <td class="border border-slate-300">{{ $ret->status }}</td>
                                        <td class="border border-slate-300">
                                            <Link slideover href="{{ route('files.show', base64_encode($ret->id)) }}" title="{{ __('Files') }}">
                                                <center>
                                                    ({{ $ret->docs }})
                                                </center>
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                           
                            <div class="pt-4 pb-4">
                                @foreach ($logs as $item)

                                    <div class="pt-4 text-sm text-gray-500"> 
                                        {{ $item->id }}: {{ date('d/m/Y H:i', strtotime($item->created_at)) }}, {{ $item->name }}<hr>
                                        <div class="text-sm text-gray-900" style="line-height:1.375; white-space:pre-wrap; padding: 1em;">
                                            {!! (nl2br($item->description)) !!}
                                        </div>
                                    </div>
                                    
                                @endforeach
                            </div>

                            <hr>

                            @if ($ret->status == 'Open' || $ret->status == 'Testing')
                                <div>
                                    
                                    <x-splade-form method="post" :action="route('logtickets.create',  $ret->id)"  class="mt-4 space-y-4" preserve-scroll>
                                        @include('tickets.log-form')
                                    </x-splade-form>

                                </div>
                            @endif                            

                    </section>
            </div>
        </div>
    </div>
</x-app-layout>