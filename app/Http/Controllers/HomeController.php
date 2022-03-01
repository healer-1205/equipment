<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use App\Models\Building;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $data = [
            'menu'      => 'menu.v_menu_admin',
            'content' => 'content.view_dashboard',
            'title' => 'Buildings Table'
        ];

        if ($request->ajax()) {
            $q_building = Building::select('*')->orderByDesc('created_at');
            return Datatables::of($q_building)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
     
                        $btn = '<div data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Edit" class="btn btn-sm btn-icon btn-outline-success btn-circle mr-2 edit editBuilding"><i class=" fi-rr-edit"></i></div>';
                        $btn = $btn.' <div data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Delete" class="btn btn-sm btn-icon btn-outline-danger btn-circle mr-2 deleteBuilding"><i class="fi-rr-trash"></i></div>';
 
                         return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }

        return view('layouts.v_template',$data);
    }

    public function store(Request $request)
    {
        Building::updateOrCreate(['id' => $request->building_id],
                [
                 'name' => $request->name,
                ]);        

        return response()->json(['success'=>'Building saved successfully!']);
    }

    public function edit($id)
    {
        $Building = Building::find($id);
        return response()->json($Building);
    }

    public function destroy($id)
    {
        Building::find($id)->delete();

        return response()->json(['success'=>'Building deleted!']);
    }
}
