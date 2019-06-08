@extends('web::layouts.grids.3-9')

@section('title', trans('web::seat.access'))
@section('page_header', trans('web::seat.access'))

@section('left')

    @include('mumble-connector::access.includes.mapping-creation')
    
@stop

@section('right')

    @include('mumble-connector::access.includes.mapping-table')

@stop

@push('javascript')
    <script type="application/javascript">
        function getCorporationTitle() {
            $('#mumble-title-id').empty();

            $.ajax('{{ route('mumble-connector.json.titles') }}', {
                data: {
                    corporation_id: $('#mumble-corporation-id').val()
                },
                dataType: 'json',
                method: 'GET',
                success: function(data){
                    for (var i = 0; i < data.length; i++) {
                        $('#mumble-title-id').append($('<option></option>').attr('value', data[i].title_id).text(data[i].name));
                    }
                }
            });
        }

        $('#mumble-type').change(function(){
            $.each(['mumble-group-id', 'mumble-role-id', 'mumble-corporation-id', 'mumble-title-id', 'mumble-alliance-id'], function(key, value){
                if (value === ('mumble-' + $('#mumble-type').val() + '-id')) {
                    $(('#' + value)).prop('disabled', false);
                } else {
                    $(('#' + value)).prop('disabled', true);
                }
            });

            if ($('#mumble-type').val() === 'title') {
                $('#mumble-corporation-id, #mumble-title-id').prop('disabled', false);
            }
        }).select2();

        $('#mumble-corporation-id').change(function(){
            getCorporationTitle();
        });

        $('#mumble-role-id').select2();

        $('#mumble-group-id').select2({
            ajax: {
                url: "{{ route('fastlookup.groups') }}",
                dataType: 'json',
                cache: true,
            },
            minimumInputLength: 3
        })

        $('#mumble-corporation-id').select2({
            ajax: {
                url: "{{ route('fastlookup.corporations') }}",
                dataType: 'json',
                cache: true,
            },
            minimumInputLength: 3
        })

        $('#mumble-alliance-id').select2({
            ajax: {
                url: "{{ route('fastlookup.alliances') }}",
                dataType: 'json',
                cache: true,
            },
            minimumInputLength: 3
        })

        $('#mumble-tabs').find('a').click(function(e){
            e.preventDefault();
            $(this).tab('show');
        });

        $(document).ready(function(){
            getCorporationTitle();
        });
    </script>
@endpush
