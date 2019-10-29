@extends('web::layouts.grids.12')

@section('title', trans('mumble-connector::seat.accounts_and_credentials'))
@section('page_header', trans('mumble-connector::seat.accounts_and_credentials'))

@section('full')

    <div id="user-alert" class="callout callout-danger hidden">
        <h4></h4>
        <p></p>
    </div>

    <div class="row margin-bottom">
        <div class="col-md-12">
            <div class="pull-right">
                    <button type="button" class="btn btn-danger" id="reset-password">
                        <i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;
                        {{ trans('mumble-connector::seat.reset_password') }}
                    </button>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{ trans_choice('mumble-connector::seat.accounts_and_credentials', 2) }}</h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-condensed table-hover" id="credentials">
                        <tbody>
                            <tr>
                                <td><b>{{ trans('mumble-connector::seat.server_ip') }}</b></td>
                                <td><input readonly id="server-ip" style="width: 100%;"></td>
                            </tr>
                            <tr>
                                <td><b>{{ trans('mumble-connector::seat.server_port') }}</b></td>
                                <td><input readonly id="server-port" style="width: 100%;"></td>
                            </tr>
                            <tr>
                                <td><b>{{ trans('mumble-connector::seat.username') }}</b></td>
                                <td><input readonly id="username" style="width: 100%;"></td>
                            </tr>
                            <tr>
                                <td><b>{{ trans('mumble-connector::seat.password') }}</b></td>
                                <td><input readonly id="password" type="password" onfocusin="this.type='text';" onfocusout="this.type='password';" style="width: 100%;"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('head')
<link rel="stylesheet" type="text/css" href="{{ asset('web/css/wt-mumble-hook.css') }}" />
@endpush

@push('javascript')
    <script type="text/javascript">

        $.ajax({
            url: '{{ route('mumble-connector.server.getcredentials') }}',
            method: 'POST',
            success: function(data){
                $('#server-ip').val(data.server_addr.split(':')[0] || '');
                $('#server-port').val(data.server_addr.split(':')[1] || '64738');
                $('#username').val(data.username);
                $('#password').val(data.password);
            }
        });
        
        $('button.btn-danger').on('click', function(){
            if (!window.confirm('{{ trans('mumble-connector::seat.password_reset_confirm') }}')) return;
            $.ajax({
                url: '{{ route('mumble-connector.reset') }}',
                method: 'POST',
                success: function(data){
                    window.alert('{{ trans('mumble-connector::seat.password_reset_complete')  }}');
                    location.reload()
                }
            });
        });
    </script>
@endpush
