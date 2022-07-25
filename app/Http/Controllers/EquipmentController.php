<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Request\EquipmentRequest;
use DataTables;
use App\Models\Room;
use App\Models\Building;
use App\Models\Equipment;
use App\Imports\ImportXLS;
use Maatwebsite\Excel\Facades\Excel;

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

        $data = [
            'menu'      => 'menu.v_menu_admin',
            'content' => 'content.view_equipment',
            'title' => 'Equipments Table',
            'buildings' => $buildings,
            'rooms' => $rooms,
        ];
        
        if ($request->ajax()) {
            $q_equipment = Equipment::with('building')->with('room')->select('*')->orderByDesc('created_at');
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
                'product' => $request->product,
                'manufacturer' => $request->manufacturer,
                'model' => $request->model,
                'desc' => $request->desc,
            ]
        );

        return response()->json(['success' => 'Equipment saved successfully!']);
    }

    public function edit($id)
    {
        $Room = Equipment::with('building')->with('room')->find($id);
        return response()->json($Room);
    }

    public function destroy($id)
    {
        Equipment::find($id)->delete();

        return response()->json(['success'=>'Equipment deleted!']);
    }

		public function importEquipment(Request $request){
			$file = $request->file('customer_csv', '');
			if(empty($file)){
					return response()->json(['message' => 'empty']);
			}
			$data = Excel::toArray(new ImportXLS, $request->file('customer_csv'));
			$data = $data[0];
			$equipments = [];
			$j = 0;
			if($data[0][0] != null && $data[0][1] != null && $data[0][2] != null && $data[0][3] != null && $data[0][4] != null && $data[0][5] != null) {
				for($i = 1 ; $i < count($data) ; $i ++) {
					if($data[$i][0] != null && $data[$i][1] != null && $data[$i][2] != null && $data[$i][3] != null && $data[$i][4] != null) {
						$equipments[$j] = $data[$i];
						$j ++;
					}
					else {
						break;
					}	
				}

				if($i == count($data)) {
					for($i = 0 ; $i < count($equipments) ; $i ++) {
						$building = Building::firstOrNew(['name' => $equipments[$i][1]]);
						$building->name = $equipments[$i][1];
						$building->save();

						$room = Room::firstOrNew(['building_id' => $building->id]);
						$room->name = $equipments[$i][2];
						$room->building_id = $building->id;
						$room->save();

						Equipment::updateOrCreate(
							[
								'building_id' => $building->id,
								'room_id' => $room->id,
								'product' => $equipments[$i][0],
							],
							[
									'building_id' => $building->id,
									'room_id' => $room->id,
									'product' => $equipments[$i][0],
									'manufacturer' =>$equipments[$i][3],
									'model' => $equipments[$i][4],
									'desc' => $equipments[$i][5] == null ? "" : $equipments[$i][5],
							]
						);
					}
					return response()->json(['message' => 'success']);
				}
				else {
					return response()->json(['message' => 'value missing']);
				}
			}
			else {
				return response()->json(['message' => 'field missing']);
			}
    }
}
