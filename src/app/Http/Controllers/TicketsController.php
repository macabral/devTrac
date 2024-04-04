<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Collection;
use ProtoneMedia\Splade\SpladeTable;
use ProtoneMedia\Splade\Facades\Toast;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Library\LogService;
use Ramsey\Uuid\Uuid;
use ZipArchive;
use App\Models\Tickets;
use App\Models\Releases;
use App\Models\UsersProjects;
use App\Models\Type;
use App\Models\Logtickets;
use App\Models\User;
use App\Library\TracMail;

class TicketsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        if (! isset(Session::get('ret')[0]['id'])) {

            return redirect()->back();

        }

        $projects_id = Session::get('ret')[0]['id'];

        $globalSearch = AllowedFilter::callback('global', function ($query,$value) {
            $query->where(function ($query) use ($value) {
                Collection::wrap($value)->each(function ($value) use ($query) {
                    $query
                        ->orwhere('tickets.title', 'LIKE', "%$value%")
                        ->orwhere('tickets.description', 'LIKE', "%$value%")
                        ->orwhere('releases.version', 'LIKE', "%$value%")
                        ->orwhere('types.title', 'LIKE', "%$value%")
                        ->orwhere('tickets.prioridade', 'LIKE', "%$value%")
                        ->orwhere('a.name', 'LIKE', "%$value%");
                });
            });
        });

        $releases = Releases::select('version','id')->where('projects_id','=',$projects_id)->get();

        $releases = $releases->pluck('version','id')->toArray();

    
        $ret = QueryBuilder::for(Tickets::class)
            ->select("projects.title as project","tickets.*", "a.name as resp","b.id as user_id","b.name as relator","types.title as type","releases.version as release")
            ->leftJoin('users as a','a.id','=','resp_id')
            ->leftJoin('users as b','b.id','=','relator_id')
            ->leftJoin('types','types.id','=','types_id')
            ->leftJoin('releases','releases.id','=','tickets.releases_id')
            ->leftJoin('projects','projects.id','=','releases.projects_id')
            ->where('tickets.projects_id','=',$projects_id)
            ->orderby('prioridade')
            ->orderby('created_at', 'desc')
            ->allowedSorts(['title','type','relator'])
            ->allowedFilters(['id','title', 'status', 'resp', 'prioridade','releases_id', $globalSearch])
            ->paginate(50)
            ->withQueryString();

        return view('tickets.result-search', [
            'ret' => SpladeTable::for($ret)
                ->perPageOptions([])
                ->withGlobalSearch()
                ->selectFilter('releases_id',$releases)
                ->column('id', label: __('ID'), searchable: true)
                ->column('project', label: __('Project'), sortable: true, searchable: false, canBeHidden:false)
                ->column('release', label: __('Sprint'))
                ->column('title', label: __('Title'), canBeHidden:false)
                ->column('type', label: __('Type'))
                ->column('relator', label: __('Relator'))
                ->column('resp', label: __('Assign to'))
                ->column('prioridade', label: __('Priority'))
                ->column('status', label: __('Status'), searchable: false)
                ->column('action', label: '', canBeHidden:false)
        ]);
    }

    /**
     * Lista tíquetes por Sprint.
     */
    public function sprint($id)
    {

        $sprintId = base64_decode($id);

        $globalSearch = AllowedFilter::callback('global', function ($query,$value) {
            $query->where(function ($query) use ($value) {
                Collection::wrap($value)->each(function ($value) use ($query) {
                    $query
                        ->orwhere('tickets.title', 'LIKE', "%$value%")
                        ->orwhere('tickets.prioridade', 'LIKE', "%$value%")
                        ->orwhere('tickets.description', 'LIKE', "%$value%")
                        ->orwhere('releases.version', 'LIKE', "%$value%")
                        ->orwhere('types.title', 'LIKE', "%$value%")
                        ->orwhere('a.name', 'LIKE', "%$value%");
                });
            });
        });

        $ret = QueryBuilder::for(Tickets::class)
            ->select("projects.title as project","tickets.*", "a.name as resp","b.id as user_id","b.name as relator","types.title as type","releases.version as release")
            ->leftJoin('users as a','a.id','=','resp_id')
            ->leftJoin('users as b','b.id','=','relator_id')
            ->leftJoin('types','types.id','=','types_id')
            ->leftJoin('releases','releases.id','=','tickets.releases_id')
            ->leftJoin('projects','projects.id','=','releases.projects_id')
            ->where('tickets.releases_id', $sprintId)
            ->orderby('prioridade')
            ->orderby('created_at', 'desc')
            ->allowedSorts(['title','type','relator'])
            ->allowedFilters(['id','title', 'status', 'resp', $globalSearch])
            ->paginate(50)
            ->withQueryString();

        return view('tickets.result-search', [
            'ret' => SpladeTable::for($ret)
                ->perPageOptions([])
                ->withGlobalSearch()
                ->column('id', label: __('ID'), searchable: true)
                ->column('project', label: __('Project'), sortable: true, searchable: true, canBeHidden:false)
                ->column('release', label: __('Sprint'))
                ->column('title', label: __('Title'), canBeHidden:false)
                ->column('type', label: __('Type'))
                ->column('relator', label: __('Relator'))
                ->column('resp', label: __('Assign to'))
                ->column('prioridade', label: __('Priority'))
                ->column('status', label: __('Status'), searchable: true)
                ->column('action', label: '', canBeHidden:false)
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function mytickets()
    {

        if (! isset(Session::get('ret')[0]['id'])) {

            return redirect()->back();

        }

        $projects_id = Session::get('ret')[0]['id'];
        
        $releases = Releases::select('version','id')->where('projects_id','=',$projects_id)->where('status','=','Open')->get();

        $releases = $releases->pluck('version','id')->toArray();

        $globalSearch = AllowedFilter::callback('global', function ($query,$value) {
            $query->where(function ($query) use ($value) {
                Collection::wrap($value)->each(function ($value) use ($query) {
                    $query
                        ->orwhere('tickets.title', 'LIKE', "%$value%")
                        ->orwhere('tickets.prioridade', 'LIKE', "%$value%")
                        ->orwhere('tickets.description', 'LIKE', "%$value%")
                        ->orwhere('releases.version', 'LIKE', "%$value%")
                        ->orwhere('types.title', 'LIKE', "%$value%")
                        ->orwhere('a.name', 'LIKE', "%$value%")
                        ->orwhere('b.name', 'LIKE', "%$value%");
                });
            });
        });

        $ret = QueryBuilder::for(Tickets::class)
            ->select("tickets.*", "a.name as resp","b.id as user_id","b.name as relator","types.title as type","releases.version as release","projects.title as project")
            ->Join('users as a','a.id','=','resp_id')
            ->Join('users as b','b.id','=','relator_id')
            ->Join('types','types.id','=','types_id')
            ->Join('releases','releases.id','=','tickets.releases_id')
            ->Join('projects','projects.id','=','tickets.projects_id')
            ->Where(function($query) {
                $query->where('tickets.status', '=', 'Open')
                    ->orwhere('tickets.status', '=', 'Testing');
                })
            ->Where(function($query) {
                    if (Session::get('ret')[0]['relator'] == '1') {
                        $query->orwhere('tickets.relator_id', '=', auth('sanctum')->user()->id);
                    }
                    if (Session::get('ret')[0]['dev'] == '1') {
                        $query->orwhere('tickets.resp_id', '=', auth('sanctum')->user()->id);
                    }
                })
            ->Where('releases.status', '=', 'Open')
            ->Where('tickets.projects_id','=', $projects_id)
            ->orderby('releases_id')
            ->orderby('status')
            ->orderby('prioridade')
            ->orderBy('created_at')
            ->allowedFilters(['id','title', 'status', 'releases_id', $globalSearch])
            ->paginate(7)
            ->withQueryString();

        return view('tickets.result-search', [
            'ret' => SpladeTable::for($ret)
                ->perPageOptions([])
                ->withGlobalSearch()
                ->selectFilter('releases_id',$releases)
                ->column('id', label: __('ID'), searchable: true)
                ->column('project', label: __('Project'), sortable: true, searchable: false, canBeHidden:false)
                ->column('release', label: __('Sprint'))
                ->column('title', label: __('Title'))
                ->column('type', label: __('Type'))
                ->column('relator', label: __('Relator'))
                ->column('resp', label: __('Assign to'))
                ->column('prioridade', label: __('Priority'))
                ->column('status', label: __('Status'))
                ->column('action', label: '', canBeHidden:false, exportAs: false)
        ]);
    }


    /**
     * Display a listing of the resource.
     */
    public function testing()
    {
   
        if (! isset(Session::get('ret')[0]['id'])) {

            return redirect()->back();

        }

        $projects_id = Session::get('ret')[0]['id'];

        $releases = Releases::select('version','id')->where('projects_id','=',$projects_id)->where('status','=','Open')->get();

        $releases = $releases->pluck('version','id')->toArray();

        $globalSearch = AllowedFilter::callback('global', function ($query,$value) {
            $query->where(function ($query) use ($value) {
                Collection::wrap($value)->each(function ($value) use ($query) {
                    $query
                        ->orwhere('tickets.title', 'LIKE', "%$value%")
                        ->orwhere('tickets.description', 'LIKE', "%$value%")
                        ->orwhere('releases.version', 'LIKE', "%$value%")
                        ->orwhere('tickets.prioridade', 'LIKE', "%$value%")
                        ->orwhere('types.title', 'LIKE', "%$value%");
                });
            });
        });

        $ret = QueryBuilder::for(Tickets::class)
            ->select("tickets.*", "a.name as resp","b.id as user_id","b.name as relator","types.title as type","releases.version as release","projects.title as project")
            ->where('tickets.status', 'Testing')
            ->leftJoin('users as a','a.id','=','resp_id')
            ->leftJoin('users as b','b.id','=','relator_id')
            ->leftJoin('types','types.id','=','types_id')
            ->leftJoin('releases','releases.id','=','tickets.releases_id')
            ->leftJoin('projects','projects.id','=','tickets.projects_id')
            ->Where('tickets.projects_id','=',$projects_id)
            ->orderby('prioridade')
            ->orderBy('status')
            ->orderBy('created_at', 'desc')
            ->allowedSorts(['title','type','relator'])
            ->allowedFilters(['id','title', 'status', 'releases_id', $globalSearch])
            ->paginate(7)
            ->withQueryString();

        return view('tickets.result-search', [
            'ret' => SpladeTable::for($ret)
                ->perPageOptions([])
                ->withGlobalSearch()
                ->selectFilter('releases_id',$releases)
                ->column('id', label: __('ID'), searchable: true)
                ->column('project', label: __('Project'), sortable: true, searchable: true, canBeHidden:false)
                ->column('release', label: __('Sprint'))
                ->column('title', label: __('Title'), canBeHidden:false)
                ->column('type', label: __('Type'))
                ->column('relator', label: __('Relator'))
                ->column('resp', label: __('Assign to'))
                ->column('prioridade', label: __('Priority'))
                ->column('status', label: __('Status'), searchable: true)
                ->column('action', label: '', canBeHidden:false)
        ]);
    }

    /**
     * Display the specified resource.
    */
    public function show(string $id)
    {
        $id = base64_decode($id);

        if (! isset(Session::get('ret')[0]['id'])) {

            return redirect()->back();

        }

        $projects_id = Session::get('ret')[0]['id'];

        $userId = auth('sanctum')->user()->id;

        $projects = UsersProjects::select('projects.id','title')
            ->leftJoin('projects','projects.id','=','projects_id')
            ->where('users_id','=',$userId)
            ->where('relator','=','1')
            ->where('projects_id','=',$projects_id)
            ->get();

        if (isset($projects) && $id == 0) {
            $project = $projects[0]->id;
        } else {
            $project = 0;
        }

        // releases
        if (Session::get('ret')[0]['gp'] == '1') {
            $releases = Releases::select('id','version')->where('status','Open')->orwhere('status','Waiting')->where('projects_id', $project)->orderBy('version')->get();
        } else {
            $releases = Releases::select('id','version')->where('status','Waiting')->where('projects_id', $project)->orderBy('version')->get();
        }
        
        // devs
        $devs = UsersProjects::select('users_id','name')->where('projects_id', $project)->where('dev', '1')->leftJoin('users','users.id','=','users_id')->where('users.active','=',1)->orderby('name')->get();

        // Type 
        $types = Type::select('id','title')->where('status','Enabled')->get();

        if ($id == 0) {

            $ret = array(
                'id' => 0,
                'title' => '',
                'description' => '',
                'status' => 'Open',
                'projects_id' => $project,
                'perfil' => Session::get('ret')[0]['gp']
            );

            return view('tickets.new-form', [
                'ret' => $ret,
                'releases' => $releases,
                'devs' => $devs,
                'types' => $types,
                'projects' => $projects
            ]);

        } else {

            $ret = Tickets::findOrFail($id);
            $ret['perfil'] = Session::get('ret')[0]['gp'];

            return view('tickets.edit-form', [
                'ret' => $ret,
                'releases' => $releases,
                'devs' => $devs,
                'types' => $types,
                'projects' => $projects
            ]);

        }

    }
    
    /**
     * Display the specified resource.
    */
    public function edit(string $id)
    {
        $id = base64_decode($id);

        if (Session::get('ret')[0]['gp'] != '1' && Session::get('ret')[0]['relator'] != '1' && Session::get('ret')[0]['dev'] != '1' && Session::get('ret')[0]['tester'] != '1') {

            return redirect()->back();

        }

        $projects_id = Session::get('ret')[0]['id'];

        $ret = Tickets::
            select("tickets.*","projects.title as project","a.name as resp","b.name as relator","types.title as type","releases.version as release")
            ->leftJoin('projects','projects.id','=','tickets.projects_id')
            ->leftJoin('users as a','a.id','=','resp_id')
            ->leftJoin('users as b','b.id','=','relator_id')
            ->leftJoin('types','types.id','=','types_id')
            ->leftJoin('releases','releases.id','=','tickets.releases_id')
            ->where('tickets.id', $id)
            ->where('tickets.projects_id','=',$projects_id)
            ->get();

        if (count($ret) == 0) {
            
            Toast::title(__('Ticket not found.'))->danger()->autoDismiss(5);
            return redirect()->back();

        }

        $queryLogs = Logtickets::
            select("logtickets.*", "users.name")
            ->where('tickets_id', $id)
            ->orderBy('Created_at')
            ->leftJoin('users','users.id','=','users_id')->get();

        return view('tickets.detail-form', [
            'ret' => $ret[0],
            'logs' => $queryLogs
        ]);
    }


    /**
     * Creating a new resource.
     */
    public function create(Request $request, TracMail $TracMailInstance)
    {
        
        $this->validate($request, [
            'projects_id' => 'required',
            'title' => 'required|max:255',
            'status' => 'required',
            'releases_id' => 'required',
            'types_id' => 'required',
            'prioridade' => 'required'
        ]);

        $input = $request->all();

        $input['relator_id'] = auth('sanctum')->user()->id;

        $arqs = $request->file('arquivos');

        $zip_file = '';

        if (!is_null($arqs)) {
            $created = date('Y');
            $destinationPath = public_path('uploads/' . $input['projects_id'] . '/' . $created );
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            
            $zip_file = Uuid::uuid4() . '.zip';
            while (file_exists($destinationPath . '/' . $zip_file)) {
                $zip_file = Uuid::uuid4() . '.zip';
            }

            $destino = $destinationPath . '/' . $zip_file;

            $zip = new ZipArchive();

            $zipStatus = $zip->open($destino, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if ($zipStatus == true) {

                foreach($arqs as $file) {
                    
                    $zip->addFile($file, basename($file->getClientOriginalName()));

                }

                $input['docs'] = $zip->count();

                $zip->close();

            }
        }

        $input['file'] = $zip_file;

        Try {

            Tickets::create($input);

        } catch (\Exception $e) {

            Toast::title(__('Error! ' .  $e))->danger()->autoDismiss(15);
            return response()->json(['messagem' => $e], 422);
            
        }
        
        try {

            $data = Tickets::latest()->first();

            $destinatario = User::select('email')->where('id','=',$data['resp_id'])->get();

            $id = $data['id'];
            $title = $data['title'];

            $mailData = [
                'to' => $destinatario[0]['email'],
                'cc' => null,
                'subject' => 'devTRAC: Novo Tíquete',
                'title' => "Novo Tíquete",
                'body' => "Você está recebendo esse email porque um Tíquete foi atribuído para você: [$id] - $title",
                'priority' => 0,
                'attachments' => null
            ];
                
            $TracMailInstance->save($mailData);


        } catch (\Exception $e) {

            Toast::title(__('It was not possible to send email notification.'))->danger()->autoDismiss(5);

        }

        Toast::title(__('Ticket saved!'))->autoDismiss(5);

        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id, LogService $logServiceInstance)
    {

        $this->validate($request, [
            'title' => 'required|max:255',
            'status' => 'required',
            'releases_id' => 'required',
            'types_id' => 'required'
        ]);

        $id = base64_decode($id);
        
        $input = $request->all();

        $ret = Tickets::findOrFail($id);

        // registra log das alterações
        $logServiceInstance->saveLog($id, $ret, $input);

        try {
            
            $ret->fill($input);

        } catch (\Exception $e) {

            return response()->json(['messagem' => $e], 422);
            
        }

        $ret->save();

        Toast::title(__('Ticket saved!'))->autoDismiss(5);

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {

        $id = base64_decode($id);

        $ret = Tickets::findOrFail($id);

        return view('tickets.confirm-delete', [
            'ret' => $ret,
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $ret = Tickets::findOrFail($id);

        try {
            
            if (! empty($ret['file'])) {
                $created = substr($ret['created_at'],0,4);
                $destinationPath = public_path('uploads/' . $ret['projects_id'] . '/' . $created) . '/' . $ret['file'];

                if (file_exists($destinationPath)) {
                    unlink($destinationPath);
                }
            }

            $ret->delete();

            Toast::title(__('Ticket deleted!'))->autoDismiss(5);

        } catch (\Exception $e) {

            Toast::title(__('Ticket cannot be deleted!'))->danger()->autoDismiss(5);
            
        }

        return redirect()->back();

    }
}
