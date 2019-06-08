<div class="modal fade" id="user-credentials" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close pull-right" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    {{ trans('mumble-connector::seat.accounts_and_credentials') }}
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-condensed table-hover" id="credentials">
                            <tbody>
                                <tr>
                                    <td><b>{{ trans('mumble-connector::seat.server_ip') }}</b></td>
                                    <td><input readonly id="server-ip"></td>
                                </tr>
                                <tr>
                                    <td><b>{{ trans('mumble-connector::seat.server_port') }}</b></td>
                                    <td><input readonly id="server-port"></td>
                                </tr>
                                <tr>
                                    <td><b>{{ trans('mumble-connector::seat.username') }}</b></td>
                                    <td><input readonly id="username"></td>
                                </tr>
                                <tr>
                                    <td><b>{{ trans('mumble-connector::seat.password') }}</b></td>
                                    <td><input readonly id="password"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-xs btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
