<x-splade-textarea id="title" name="title" type="text" autosize :label="__('Title')" required autofocus autocomplete="title" />
<x-splade-textarea id="description" name="description" autosize :label="__('Description')" required autocomplete="description" />
<x-splade-select id="types_id" name="types_id" :options="$types" option-label="title" option-value="id" :label="__('Type')" required />
<x-splade-select id="releases_id" name="releases_id" :options="$releases" option-label="version" option-value="id" required :label="__('Sprint')" />
@if (Session::get('ret')[0]['gp'] == '1')
    <x-splade-select id="resp_id" name="resp_id" :options="$devs" option-label="name" option-value="users_id" :label="__('Assign to')" />
@endif
<x-splade-select id="prioridade" name="prioridade" :options="['Crítica', 'Importante', 'Desejada', 'Pode Esperar']" required :label="__('Prioridade')" />
<x-splade-select id="status" name="status" :options="['Open', 'Testing', 'Closed']" required :label="__('Status')" />

@if ($ret['id'] == 0)
    <x-splade-file name="arquivos[]" multiple filepond max-size="2MB"/>
@endif

<div class="flex items-center gap-4">
    <x-splade-submit :label="__('Save')" />
</div>