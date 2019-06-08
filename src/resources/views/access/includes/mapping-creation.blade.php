<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{{ trans('mumble-connector::seat.quick_create') }}</h3>
    </div>
    <div class="panel-body">
        <form role="form" action="{{ route('mumble-connector.add') }}" method="post">
            {{ csrf_field() }}

            <div class="box-body">

                <div class="form-group">
                    <label for="mumble-type">{{ trans_choice('web::seat.type', 1) }}</label>
                    <select name="mumble-type" id="mumble-type" class="form-control">
                        <option value="group">{{ trans('mumble-connector::seat.user_filter') }}</option>
                        <option value="role">{{ trans('mumble-connector::seat.role_filter') }}</option>
                        <option value="corporation">{{ trans('mumble-connector::seat.corporation_filter') }}</option>
                        <option value="title">{{ trans('mumble-connector::seat.title_filter') }}</option>
                        <option value="alliance">{{ trans('mumble-connector::seat.alliance_filter') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="mumble-group-id">{{ trans('web::seat.username') }}</label>
                    <select name="mumble-group-id" id="mumble-group-id" class="form-control">
                    </select>
                </div>

                <div class="form-group">
                    <label for="mumble-role-id">{{ trans_choice('web::seat.role', 1) }}</label>
                    <select name="mumble-role-id" id="mumble-role-id" class="form-control" disabled="disabled">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="mumble-corporation-id">{{ trans_choice('web::seat.corporation', 1) }}</label>
                    <select name="mumble-corporation-id" id="mumble-corporation-id" class="form-control" disabled="disabled">
                    </select>
                </div>

                <div class="form-group">
                    <label for="mumble-title-id">{{ trans_choice('web::seat.title', 1) }}</label>
                    <select name="mumble-title-id" id="mumble-title-id" class="form-control" disabled="disabled"></select>
                </div>

                <div class="form-group">
                    <label for="mumble-alliance-id">{{ trans('web::seat.alliance') }}</label>
                    <select name="mumble-alliance-id" id="mumble-alliance-id" class="form-control" disabled="disabled">
                    </select>
                </div>

                <div class="form-group">
                    <label for="mumble-mumble-role">{{ trans('mumble-connector::seat.mumble_role') }}</label>
                    <input name="mumble-mumble-role" id="mumble-mumble-role" class="form-control">
                </div>

                <div class="form-group">
                    <label for="mumble-enabled">{{ trans('web::seat.enabled') }}</label>
                    <input type="checkbox" name="mumble-enabled" id="mumble-enabled" checked="checked" value="1" />
                </div>

            </div>

            <div class="box-footer">
                <button type="submit" class="btn btn-primary pull-right">{{ trans('web::seat.add') }}</button>
            </div>

        </form>
    </div>
</div>
