<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use App\Models\Room;
use App\Models\Building;

class RoomController extends Controller
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

        $data = [
            'menu'      => 'menu.v_menu_admin',
            'content' => 'content.view_room',
            'title' => 'Rooms Table',
            'buildings' => $buildings
        ];
        
        if ($request->ajax()) {
            $q_room = Room::with(['building' => function ($query) {
                $query->orderBy('name');
            }])->select('*');
            return Datatables::of($q_room)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){

                        $btn = '<div data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Edit" class="btn btn-sm btn-icon btn-outline-success btn-circle mr-2 edit editRoom"><i class=" fi-rr-edit"></i></div>';
                        $btn = $btn.' <div data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Delete" class="btn btn-sm btn-icon btn-outline-danger btn-circle mr-2 deleteRoom"><i class="fi-rr-trash"></i></div>';

                         return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }

        return view('layouts.v_template', $data);
    }

    public function store(Request $request)
    {
        Room::updateOrCreate(
            ['id' => $request->room_id],
            [
                'building_id' => $request->building_id,
                'name' => $request->name,
            ]
        );

        return response()->json(['success' => 'Building saved successfully!']);
    }

    public function edit($id)
    {
        $Room = Room::with('building')->find($id);
        return response()->json($Room);
    }

    public function destroy($id)
    {
        Room::find($id)->delete();

        return response()->json(['success'=>'Room deleted!']);
    }
}
