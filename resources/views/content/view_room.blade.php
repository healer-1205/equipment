<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
  <div class="container">
      <div class="card">
          <div class="card-header">
              <h2>{{$title}}</h2>
              <div class="d-flex flex-row-reverse"><button
                      class="btn btn-sm btn-pill btn-outline-primary font-weight-bolder" id="createNewRoom"><i
                          class="fas fa-plus"></i>add data </button></div>
          </div>
          <div class="card-body">
              <div class="col-md-12">
                  <div class="table-responsive">
                      <table class="table" id="tableBuilding">
                          <thead class="font-weight-bold text-center">
                              <tr>
                                  {{-- <th>No.</th> --}}
                                  <th>Building</th>
                                  <th>Room</th>
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
<div class="modal fade" id="modal-room" data-backdrop="static" tabindex="-1" role="dialog"
  aria-labelledby="staticBackdrop" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
          <div class="modal-header bg-primary">
              <h5 class="modal-title text-white" id="exampleModalLabel">Add Room</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <i aria-hidden="true" class="ki ki-close"></i>
              </button>
          </div>
          <div class="modal-body">
              <form id="formRoom" name="formRoom">
                  <div class="form-group">
                      <select name="building_id" class="form-control" id="building">
                        @foreach ($buildings as $building)
                          <option value="{{$building->id}}">{{$building->name}}</option>
                        @endforeach
                      </select><br>
                      <input type="text" name="name" class="form-control" id="name" placeholder="Name"><br>
                      <input type="hidden" name="room_id" id="room_id" value="">
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
          ajax: "{{ route('room.index') }}",
          columns: [{
                  data: 'building.name',
                  name: 'building'
              },
              {
                  data: 'name',
                  name: 'room'
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
      // initialize btn add
      $('#createNewRoom').click(function () {
          $('#saveBtn').val("create room");
          $('#room_id').val('');
          $('#formRoom').trigger("reset");
          $('#modal-room').modal('show');
      });
      // initialize btn edit
      $('body').on('click', '.editRoom', function () {
          var room_id = $(this).data('id');
          $.get("{{route('room.index')}}" + '/' + room_id + '/edit', function (data) {
              $('#saveBtn').val("edit-room");
              $('#modal-room').modal('show');
              $('#room_id').val(data.id);
              $('#name').val(data.name);
              $('#building').val(data.building_id);
          })
      });
      // initialize btn save
      $('#saveBtn').click(function (e) {
          e.preventDefault();
          $(this).html('Save');

          $.ajax({
              data: $('#formRoom').serialize(),
              url: "{{ route('room.store') }}",
              type: "POST",
              dataType: 'json',
              success: function (data) {

                  $('#formRoom').trigger("reset");
                  $('#modal-room').modal('hide');
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
      $('body').on('click', '.deleteRoom', function () {
          var room_id = $(this).data("id");

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
                      url: "{{route('room.store')}}" + '/' + room_id,
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
