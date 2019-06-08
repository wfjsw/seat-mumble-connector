@extends('web::layouts.grids.12')

@section('title', trans('mumble-connector::seat.user_mapping'))
@section('page_header', trans('mumble-connector::seat.user_mapping'))

@section('full')

    <div id="user-alert" class="callout callout-danger hidden">
        <h4></h4>
        <p></p>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{ trans_choice('web::seat.user', 2) }}</h3>
        </div>
        <div class="panel-body">
            <table class="table table-condensed table-hover table-responsive no-margin" id="users-table" data-page-length="25">
                <thead>
                    <tr>
                        <th>SeAT Group ID</th>
                        <th>SeAT User ID</th>
                        <th>SeAT Username</th>
                        <!-- <th>Last Seen IP</th> -->
                        <th></th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>

    @include('mumble-connector::users.includes.roles_modal')
    @include('mumble-connector::users.includes.loginhistory_modal')

@endsection

@push('head')
<link rel="stylesheet" type="text/css" href="{{ asset('web/css/wt-mumble-hook.css') }}" />
@endpush

@push('javascript')
<script type="text/javascript">
    $(function() {
        var groups_modal = $('#user-groups');
        var loginhistory_modal = $('#user-history');
        var table = $('table#users-table').DataTable({
            processing: true,
            serverSide: false,
            ajax: '{{ route('mumble-connector.json.users') }}',
            columns: [
                {data: 'group_id', type: 'num'},
                {data: 'user_id', type: 'num'},
                {data: 'username', type: 'string'},
                // {data: 'ip', type: 'string'},
                @if (auth()->user()->has('mumble-connector.security'))
                {
                    data: null,
                    targets: -1,
                    defaultContent: '<button class="btn btn-xs btn-info groups">Groups</button> <button class="btn btn-xs btn-info login-history">Login History</button> <button class="btn btn-xs btn-danger reset-pwd">Reset Password</button>',
                    orderable: false
                }
                @endif
            ],
            "fnDrawCallback": function(){
                $(document).ready(function(){
                    $('img').unveil(100);
                });
            }
        });

        $('#users-table').find('tbody')
            .on('click', 'button.groups', function() {
                var row = table.row($(this).parents('tr')).data();
                $('#seat_username').text(row.user_name);
                $('#groups').find('tbody tr').remove();
                groups_modal.find('.overlay').show();

                $.ajax({
                    url: '{{ route('mumble-connector.json.user.groups') }}',
                    data: {'id' : row.group_id},
                    success: function(data){
                        if (data) {
                            for (var i = 0; i < data.length; i++) {
                                var role = data[i];

                                $('#roles').find('tbody').append(
                                    '<tr><td>' +
                                    role +
                                    '</td></tr>');
                            }
                        }

                        groups_modal.find('.overlay').hide();
                    }
                });

                groups_modal.modal('show');
            })
            .on('click', 'button.login-history', function(){
                var row = table.row($(this).parents('tr')).data();
                $('#seat_username').text(row.user_name);
                if ( $.fn.DataTable.isDataTable('#history') ) {
                    $('#history').DataTable().destroy();
                }

                $('#history tbody').empty();

                $('#history').dataTable({
                    processing: true,
                    serverSide: true,
                    searchDelay: 1000,
                    pageLength: 50,
                    ajax: {
                        url: "{{ route('mumble-connector.json.users.history') }}",
                        data: {id: row.group_id}
                    },
                    columns: [
                        {data: 'id', name: 'id', type: 'num'},
                        {data: 'session_id', name: 'session_id', type: 'num'},
                        {data: 'ip', name: 'ip', type: 'string'},
                        {data: 'login_time', name: 'login_time'},
                        {data: 'logout_time', name: 'logout_time'},
                        {data: 'version', name: 'version'},
                        {data: 'release', name: 'release'},
                        {data: 'os', name: 'os'},
                        {data: 'osversion', name: 'osversion'}
                    ],
                    order: [
                        [0, 'desc']
                    ]
                });

            })
            .on('click', 'button.reset-pwd', function(){
                var data = table.row($(this).parents('tr')).data();
                if (!window.confirm('Are you sure to reset ' + data.username + '\'s mumble password? This will log all devices off and force reauth.'))
                $.ajax({
                    url: '{{ route('mumble-connector.json.user.reset') }}',
                    method: 'POST',
                    data: {'id' : row.group_id},
                    success: function(data){
                        if (data.ok) window.alert('Password reset completed.');
                        else window.alert('Error occurred.');
                    }
                });
            });
    });
</script>
@endpush
