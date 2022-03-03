<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use App\Models\Room;
use App\Models\Building;
use App\Models\User;
use App\Models\Equipment;

class EquipmentController extends Controller
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

    public function index(Request $request)
    {
        $buildings = Building::get();
        $rooms = Room::get();
        $users = User::get();

        $data = [
            'menu'      => 'menu.v_menu_admin',
            'content' => 'content.view_equipment',
            'title' => 'Equipments Table',
            'buildings' => $buildings,
            'rooms' => $rooms,
            'users' => $users
        ];
        
        if ($request->ajax()) {
            $q_equipment = Equipment::with('building')->with('room')->with('user')->select('*')->orderByDesc('created_at');
            return Datatables::of($q_equipment)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){

                        $btn = '<div data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Edit" class="btn btn-sm btn-icon btn-outline-success btn-circle mr-2 edit editEquipment"><i class=" fi-rr-edit"></i></div>';
                        $btn = $btn.' <div data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Delete" class="btn btn-sm btn-icon btn-outline-danger btn-circle mr-2 deleteEquipment"><i class="fi-rr-trash"></i></div>';

                         return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }

        return view('layouts.v_template', $data);
    }

    public function store(Request $request)
    {
        Equipment::updateOrCreate(
            ['id' => $request->equipment_id],
            [
                'building_id' => $request->building_id,
                'room_id' => $request->room_id,
                'user_id' => $request->user_id,
                'product' => $request->product,
                'manufacturer' => $request->manufacturer,
                'model' => $request->model,
            ]
        );

        return response()->json(['success' => 'Equipment saved successfully!']);
    }

    public function edit($id)
    {
        $Room = Equipment::with('building')->with('room')->with('user')->find($id);
        return response()->json($Room);
    }

    public function destroy($id)
    {
        Equipment::find($id)->delete();

        return response()->json(['success'=>'Equipment deleted!']);
    }
}
