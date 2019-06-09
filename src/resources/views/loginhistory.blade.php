@extends('web::layouts.grids.12')

@section('title', trans('mumble-connector::seat.login_history'))
@section('page_header', trans('mumble-connector::seat.login_history'))

@section('full')

    <div id="user-alert" class="callout callout-danger hidden">
        <h4></h4>
        <p></p>
    </div>

    <div class="row margin-bottom">
        <div class="col-md-12">
            <div class="pull-right">
                    <button type="button" class="btn btn-info" id="retrieve-account">
                        {{ trans('mumble-connector::seat.retrieve_account') }}
                    </button>
                    <button type="button" class="btn btn-danger" id="reset-password">
                        <i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;
                        {{ trans('mumble-connector::seat.reset_password') }}
                    </button>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{ trans_choice('mumble-connector::seat.login_history', 2) }}</h3>
        </div>
        <div class="panel-body">
            <table class="table table-condensed table-hover table-responsive" id="history" data-page-length="25">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Session ID</th>
                        <th>IP Address</th>
                        <th>Login Time</th>
                        <th>Logout Time</th>
                        <th>Version</th>
                        <th>Release</th>
                        <th>OS</th>
                        <th>OS Version</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    @include('mumble-connector::includes.credentials')

@endsection

@push('head')
<link rel="stylesheet" type="text/css" href="{{ asset('web/css/wt-mumble-hook.css') }}" />
@endpush

@push('javascript')
    <script type="text/javascript">
        var logincredentials_modal = $('#user-credentials');
        $('#history').dataTable({
            processing: true,
            serverSide: true,
            searchDelay: 1000,
            pageLength: 50,
            ajax: {
                url: "{{ url()->current() }}"
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

        $('button.btn-info').on('click', function() {
            $.ajax({
                url: '{{ route('mumble-connector.server.getcredentials') }}',
                method: 'POST',
                success: function(data){
                    logincredentials_modal.find('#server-ip').val(data.server_addr.split(':')[0] || '');
                    logincredentials_modal.find('#server-port').val(data.server_addr.split(':')[1] || '64738');
                    logincredentials_modal.find('#username').val(data.username);
                    logincredentials_modal.find('#password').val(data.password);
                    logincredentials_modal.modal('show');
                }
            });
        });
        
        $('button.btn-danger').on('click', function(){
            if (!window.confirm('{{ trans('mumble-connector::seat.password_reset_confirm') }}')) return;
            $.ajax({
                url: '{{ route('mumble-connector.reset') }}',
                method: 'POST',
                success: function(data){
                    window.alert('{{ trans('mumble-connector::seat.password_reset_complete')  }}');
                }
            });
        });
    </script>
@endpush
