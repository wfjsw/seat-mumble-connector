@extends('web::layouts.grids.12')

@section('title', trans('web::seat.configuration'))
@section('page_header', trans('web::seat.configuration'))

@section('full')
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Configuration</h3>
        </div>
        <div class="panel-body">
            <form role="form" action="{{ route('mumble-connector.oauth.configuration.post') }}" method="post" class="form-horizontal">
                {{ csrf_field() }}

                <div class="box-body">

                    <legend>Mumble ICE Interface</legend>

                    <div class="form-group">
                        <label for="mumble-configuration-endpoint-ip" class="col-md-4">ICE Endpoint IP</label>
                        <div class="col-md-7">
                            <div class="input-group input-group-sm">
                                @if (setting('winterco.mumble-connector.credentials.ice_endpoint_ip', true) == null)
                                <input type="text" class="form-control" id="mumble-configuration-endpoint-ip"
                                       name="mumble-configuration-endpoint-ip" />
                                @else
                                <input type="text" class="form-control " id="mumble-configuration-endpoint-ip"
                                       name="mumble-configuration-endpoint-ip" value="{{ setting('winterco.mumble-connector.credentials.ice_endpoint_ip', true) }}" readonly />
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mumble-configuration-endpoint-port" class="col-md-4">ICE Endpoint Port</label>
                        <div class="col-md-7">
                            <div class="input-group input-group-sm">
                                @if (setting('winterco.mumble-connector.credentials.ice_endpoint_port', true) == null)
                                <input type="text" class="form-control" id="mumble-configuration-endpoint-port"
                                       name="mumble-configuration-endpoint-port" />
                                @else
                                <input type="text" class="form-control " id="mumble-configuration-endpoint-port"
                                       name="mumble-configuration-endpoint-port" value="{{ setting('winterco.mumble-connector.credentials.ice_endpoint_port', true) }}" readonly />
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mumble-configuration-key" class="col-md-4">Mumble ICE Key</label>
                        <div class="col-md-7">
                            <div class="input-group input-group-sm">
                                @if (setting('winterco.mumble-connector.credentials.ice_key', true) == null)
                                <input type="text" class="form-control" id="mumble-configuration-key"
                                       name="mumble-configuration-key" />
                                @else
                                <input type="text" class="form-control" id="mumble-configuration-key"
                                       name="mumble-configuration-key" value="{{ setting('winterco.mumble-connector.credentials.ice_key', true) }}" readonly />
                                @endif
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-danger btn-flat" id="key-eraser">
                                        <i class="fa fa-eraser"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mumble-configuration-server" class="col-md-4">Server Address</label>
                        <div class="col-md-7">
                            <div class="input-group input-group-sm">
                                @if (setting('winterco.mumble-connector.credentials.server_addr', true) == null)
                                <input type="text" class="form-control" id="mumble-configuration-server"
                                       name="mumble-configuration-server" />
                                @else
                                <input type="text" class="form-control " id="mumble-configuration-server"
                                       name="mumble-configuration-server" value="{{ setting('winterco.mumble-connector.credentials.server_addr', true) }}" readonly />
                                @endif
                            </div>
                        </div>
                    </div>

                    <legend>Options</legend>

                    <div class="form-group">
                        <label for="mumble-configuration-ticker" class="col-md-4">Display Ticker</label>
                        <div class="col-md-7">
                            <div class="input-group input-group-sm">
                                <input type="checkbox" id="mumble-configuration-ticker"
                                       name="mumble-configuration-ticker" @if(setting('winterco.mumble-connector.ticker', true)) checked="checked" @endif />
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mumble-configuration-nick-format" class="col-md-4">Nickname Format</label>
                        <div class="col-md-7">
                            <div class="input-group input-group-sm">
                                <input type="input" id="mumble-configuration-nick-format"
                                       name="mumble-configuration-nick-format" value="{{ setting('winterco.mumble-connector.nickfmt', true) ?: '[%s] %s' }}" />
                            </div>
                        </div>
                    </div>

                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right">Update</button>
                </div>

            </form>
        </div>
    </div>
@stop

@push('javascript')
    <script type="application/javascript">
        $('#key-eraser').on('click', function(){
            var mumble_secret = $('#mumble-configuration-secret');
            mumble_secret.val('');
            mumble_secret.removeAttr("readonly");
        });
    </script>
@endpush
