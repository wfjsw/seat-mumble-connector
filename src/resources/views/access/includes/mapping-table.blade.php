<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{{ trans_choice('mumble-connector::seat.authorization', 2) }}</h3>
    </div>
    <div class="panel-body">

        <ul class="nav nav-pills" id="mumble-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#mumble-username" role="tab" data-toggle="tab">{{ trans('mumble-connector::seat.user_filter') }}</a>
            </li>
            <li role="presentation">
                <a href="#mumble-role" role="tab" data-toggle="tab">{{ trans('mumble-connector::seat.role_filter') }}</a>
            </li>
            <li role="presentation">
                <a href="#mumble-corporation" role="tab" data-toggle="tab">{{ trans('mumble-connector::seat.corporation_filter') }}</a>
            </li>
            <li role="presentation">
                <a href="#mumble-title" role="tab" data-toggle="tab">{{ trans('mumble-connector::seat.title_filter') }}</a>
            </li>
            <li role="presentation">
                <a href="#mumble-alliance" role="tab" data-toggle="tab">{{ trans('mumble-connector::seat.alliance_filter') }}</a>
            </li>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade in active" id="mumble-username">
                @include('mumble-connector::access.includes.subs.user-mapping-tab')
            </div>
            <div role="tabpanel" class="tab-pane fade" id="mumble-role">
                @include('mumble-connector::access.includes.subs.role-mapping-tab')
            </div>
            <div role="tabpanel" class="tab-pane fade" id="mumble-corporation">
                @include('mumble-connector::access.includes.subs.corporation-mapping-tab')
            </div>
            <div role="tabpanel" class="tab-pane fade" id="mumble-title">
                @include('mumble-connector::access.includes.subs.title-mapping-tab')
            </div>
            <div role="tabpanel" class="tab-pane fade" id="mumble-alliance">
                @include('mumble-connector::access.includes.subs.alliance-mapping-tab')
            </div>
        </div>
    </div>
</div>
