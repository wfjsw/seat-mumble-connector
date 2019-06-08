<table class="table table-condensed table-hover table-responsive">
    <thead>
    <tr>
        <th>{{ trans_choice('web::seat.corporation', 1) }}</th>
        <th>{{ trans('mumble-connector::seat.mumble_role') }}</th>
        <th>{{ trans('web::seat.created') }}</th>
        <th>{{ trans('web::seat.updated') }}</th>
        <th>{{ trans('web::seat.status') }}</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    @foreach($corporation_filters as $filter)
        <tr>
            <td>{{ $filter->corporation->name }}</td>
            <td>{{ $filter->mumble_role }}</td>
            <td>{{ $filter->created_at }}</td>
            <td>{{ $filter->updated_at }}</td>
            <td>
                @if ($filter->enabled)
                    <span class="fa fa-check text-success"></span>
                @else
                    <span class="fa fa-times text-danger"></span>
                @endif
            </td>
            <td>
                <div class="btn-group">
                    <a href="{{ route('mumble-connector.corporation.remove', ['corporation_id' => $filter->corporation_id, 'mumble_role' => $filter->mumble_role]) }}" type="button" class="btn btn-danger btn-xs col-xs-12">
                        {{ trans('web::seat.remove') }}
                    </a>
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
