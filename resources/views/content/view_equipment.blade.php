<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
  <div class="container">
      <div class="card">
          <div class="card-header">
              <h2>{{$title}}</h2>
              <div class="d-flex flex-row-reverse">
								<input autocomplete="new-password" type="hidden" name="_token" value="{{csrf_token()}}"/>
								<button class="btn btn-sm btn-pill btn-outline-primary font-weight-bolder" id="createNewEquipment">
									<i class="fas fa-plus"></i>Add Equipment
								</button>
								<form id="import-csv-form" method="POST" action="#" accept-charset="UTF-8"  enctype="multipart/form-data">
									<input class="" type="file" id="customer_csv" name="customer_csv" accept=".csv, text/csv, .xlsx"/>
									<button type="submit" class="btn btn-sm btn-pill btn-outline-primary font-weight-bolder mr-5" id="importEquipment">
										Import
									</button>
								</form>
							</div>
          </div>
          <div class="card-body">
              <div class="col-md-12">
                  <div class="table-responsive">
                      <table class="table" id="tableBuilding">
                          <thead class="font-weight-bold text-center">
                              <tr>
                                  {{-- <th>No.</th> --}}
                                  <th>Product</th>
                                  <th>Building</th>
                                  <th>Room</th>
                                  <th>Manufacturer</th>
                                  <th>Model</th>
                                  <th>Detail</th>
                                  <th style="width:90px;">Action</th>
                              </tr>
                          </thead>
                          <tbody class="text-center">
                              {{-- @foreach ($buildings as $r_buildings)
                                  <tr>
                              <td>{{$r_buildings->id}}</td>
                              <td>{{$r_buildings->name}}</td>
                              <td>
                                  <div class="btn btn-success editRoom" data-id="{{$r_buildings->id}}">Edit</div>
                                  <div class="btn btn-danger deleteUser" data-id="{{$r_buildings->id}}">Delete</div>
                              </td>
                              </tr>
                              @endforeach --}}
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>

<!-- Modal-->
<div class="modal fade" id="modal-equipment" data-backdrop="static" tabindex="-1" role="dialog"
  aria-labelledby="staticBackdrop" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
          <div class="modal-header bg-primary">
              <h5 class="modal-title text-white" id="exampleModalLabel">Add Equipment</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <i aria-hidden="true" class="ki ki-close"></i>
              </button>
          </div>
          <div class="modal-body">
              <form id="formEquipment" name="formEquipment">
                  <div class="form-group">
                      <select name="building_id" class="form-control" id="building">
                        @foreach ($buildings as $building)
                          <option value="{{$building->id}}">{{$building->name}}</option>
                        @endforeach
                      </select><br>
                      <select name="room_id" class="form-control" id="room">
                        @foreach ($rooms as $room)
                          <option value="{{$room->id}}">{{$room->name}}</option>
                        @endforeach
                      </select><br>
                      <input type="text" name="product" class="form-control" id="product" placeholder="Product Name"><br>
                      <input type="text" name="manufacturer" class="form-control" id="manufacturer" placeholder="Manufacturer Name"><br>
                      <input type="text" name="model" class="form-control" id="model" placeholder="Model Name"><br>
                      <input type="text" name="desc" class="form-control" id="desc" placeholder="Description"><br>
                      <input type="hidden" name="equipment_id" id="equipment_id" value="">
                  </div>
              </form>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary font-weight-bold" id="saveBtn">Save changes</button>
          </div>
      </div>
  </div>
</div>

@push('scripts')
<script>
  $('document').ready(function () {
      // success alert
      function swal_success() {
          Swal.fire({
              position: 'top-end',
              icon: 'success',
              title: 'Your work has been saved',
              showConfirmButton: false,
              timer: 1000
          })
      }
      // error alert
      function swal_error() {
          Swal.fire({
              position: 'centered',
              icon: 'error',
              title: 'Something goes wrong !',
              showConfirmButton: true,
          })
      }
      // table serverside
      var table = $('#tableBuilding').DataTable({
          processing: false,
          serverSide: true,
          ordering: false,
          dom: 'Bfrtip',
          buttons: [
              'copy', 'excel', 'pdf'
          ],
          ajax: "{{ route('equipment.index') }}",
          columns: [
            {
              data: 'product',
              name: 'product'
            },
            {
              data: 'building.name',
              name: 'building.name'
            },
            {
              data: 'room.name',
              name: 'room.name'
            },
            {
              data: 'manufacturer',
              name: 'manufacturer'
            },
            {
              data: 'model',
              name: 'model'
            },
            {
              data: 'desc',
              name: 'desc'
            },
            {
              data: 'action',
              name: 'action',
              orderable: false,
              searchable: false
            },
          ]
      });
      
      // csrf token
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
			$('#import-csv-form').on("submit", function(e){
					e.preventDefault(); //form will not submitted
					$.ajax({
							url: '{{ url('/import/equipment') }}',
							method:"POST",
							data:new FormData(this),
							contentType:false,          // The content type used when sending data to the server.
							cache:false,                // To unable request pages to be cached
							processData:false,          // To send DOMDocument or non processed data file it is set to false
							success: function(result){
								if(result.message == 'empty') {
									Swal.fire({
										title: 'Warning',
										text: "You can't upload empty file!",
										icon: 'warning',
										showConfirmButton: false,
									});
								}
								else if(result.message == 'field missing') {
									Swal.fire({
										title: 'Warning',
										text: "Some fields are missing!",
										icon: 'warning',
										showConfirmButton: false,
									});
								}
								else if(result.message == 'value missing') {
									Swal.fire({
										title: 'Warning',
										text: "Some values are missing!",
										icon: 'warning',
										showConfirmButton: false,
									});
								}
								else {
									Swal.fire({
										title: 'Success',
										text: "Imported successfully!",
										icon: 'success',
										showConfirmButton: false,
									});
								}
								location.reload();
							},
							error:function(){
							}
					});
      });
      // initialize btn add
      $('#createNewEquipment').click(function () {
          $('#saveBtn').val("create equipment");
          $('#equipment_id').val('');
          $('#formEquipment').trigger("reset");
          $('#modal-equipment').modal('show');
      });
      // initialize btn edit
      $('body').on('click', '.editEquipment', function () {
          var equipment_id = $(this).data('id');
          $.get("{{route('equipment.index')}}" + '/' + equipment_id + '/edit', function (data) {
              $('#saveBtn').val("edit-equipment");
              $('#modal-equipment').modal('show');
              $('#equipment_id').val(data.id);
              $('#product').val(data.product);
              $('#manufacturer').val(data.manufacturer);
              $('#model').val(data.model);
              $('#building').val(data.building_id);
              $('#room').val(data.room_id);
          })
      });
      // initialize btn save
      $('#saveBtn').click(function (e) {
          e.preventDefault();
          $(this).html('Save');

          $.ajax({
              data: $('#formEquipment').serialize(),
              url: "{{ route('equipment.store') }}",
              type: "POST",
              dataType: 'json',
              success: function (data) {

                  $('#formEquipment').trigger("reset");
                  $('#modal-equipment').modal('hide');
                  swal_success();
                  table.draw();

              },
              error: function (data) {
                  swal_error();
                  $('#saveBtn').html('Save Changes');
              }
          });

      });
      // initialize btn delete
      $('body').on('click', '.deleteEquipment', function () {
          var equipment_id = $(this).data("id");

          Swal.fire({
              title: 'Are you sure?',
              text: "You won't be able to revert this!",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Yes, delete it!'
          }).then((result) => {
              if (result.isConfirmed) {
                  $.ajax({
                      type: "DELETE",
                      url: "{{route('equipment.store')}}" + '/' + equipment_id,
                      success: function (data) {
                          swal_success();
                          table.draw();
                      },
                      error: function (data) {
                          swal_error();
                      }
                  });
              }
          })
      });
      // statusing
  });
</script>
@endpush
