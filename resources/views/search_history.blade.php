@php
$searchBy[] = 'Filter Search';
$searchBy[] = 'Domain Search';
@endphp
@extends('layouts.master')

@section('content')

@if(session()->has('success'))
    <div class="alert alert-success">
        <button type="button" class="close" data-dismiss="alert">×</button>
        {{ session()->get('success') }}
    </div>
@endif

<ul class="nav nav-tabs">
  <li class="nav-item">
    <a class="nav-link" href="{{ route('domainReport') }}">Domain Report</a>
  </li>
  <li class="nav-item">
    <a class="nav-link active" href="{{ route('searchHistory') }}">Saved Searches</a>
  </li>
  <li class="nav-item">
    <a class="nav-link disabled">Purchased Domains</a>
  </li>
</ul>

<div class="card shadow mb-4">
    <!-- <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Domain Report</h6>
    </div> -->
    <div class="card-body">
        <form id="search-report" method="get" action="{{ url('search_history') }}">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <!-- <label for="start-date">Start Date</label> -->
                    <input type="date" name="start_date" class="form-control datepicker" id="start-date" placeholder="Start Date" autocomplete="off" value="{{ request()->get('start_date') }}">
                </div>
                <div class="form-group col-md-3">
                    <!-- <label for="end-date">End Date</label> -->
                    <input type="date" name="end_date" class="form-control datepicker" id="end-date" placeholder="End Date" autocomplete="off" value="{{ request()->get('end_date') }}">
                </div>
                <div class="form-group col-md-3">
                    <!-- <label for="search_by">Search By</label> -->
                    <select name="search_by" class="form-control" id="search_by">
                        <option value="">-- Search By --</option>
                        @foreach($searchBy as $search_by)
                            @if(request()->get('search_by') == $search_by)
                                <option selected="selected" value="{{ $search_by }}">{{ $search_by }}</option>
                            @else
                                <option value="{{ $search_by }}">{{ $search_by }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <div class="form-check mt-2 ml-3">
                        @php
                            $checkedString = '';
                            if (request()->get('show_favorite')) {
                                $checkedString = 'checked="checked"';
                            }
                        @endphp
                        <input type="checkbox" class="form-check-input" id="show_favorite" {{ $checkedString }} name="show_favorite">
                        <label class="form-check-label" for="show_favorite">Show favorite only</label>
                    </div>
                </div>
            </div>
            <div class="float-right">
                <button type="submit" class="btn btn-primary" id="filter-search"><i class="fa fa-search"></i> Search</button>
                <a href="{{ url('search_history') }}" class="btn btn-warning"><i class="fa fa-sync"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <a href="#" class="btn btn-primary btn-icon-split">
                        <span class="text"><i class="fas fa-calculator"></i> Total Searches</span>
                        <span class="icon">{{ number_format($model->total()) }}</span>
                    </a>
                </div>
                <div class="col-sm-12 col-md-6">
                    <div class="float-right">{{ $model->links() }}</div>
                </div>
            </div>
            <table class="table table-bordered table-striped data-records" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>@sortablelink('created_at', 'Search Date')</th>
                        <th>@sortablelink('title', 'Title')</th>
                        <th>Details</th>
                        <th>Actions</th>
                        <th>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="check-all">
                                <label class="form-check-label" for="check-all">
                                    Delete
                                </label>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $counter = ($model->currentPage() - 1) * $model->perPage();
                    @endphp
                    @foreach($model as $details)
                    <tr>
                        <td>{{ ++$counter }}</td>
                        <td>{{ date('d/m/Y H:i A', strtotime($details->created_at)) }}</td>
                        <td>{{ $details->title }}</td>
                        <td>
                            @php
                                $detailsString = '';
                                $param1 = [];
                                
                                if ($details->type == 'filter_search') {
                                    $param = json_decode($details->param, true);
                                    foreach ($param as $filterDetails) {
                                        $detailsString .= $filterDetails['search_by'] . ': ' . $filterDetails['search'] . ', ';
                                    }
                                }
                                else if ($details->type == 'domain_search') {
                                    $param = json_decode($details->param, true);
                                    foreach ($param as $filterDetails) {
                                        $detailsString .= $filterDetails['search_by'] . ': ' . $filterDetails['search'] . ', ';
                                    }

                                    if ($details->other_param) {
                                        $other_param = json_decode($details->other_param, true);

                                        if ($other_param['generate_by'] && $other_param['keywords']) {
                                            $detailsString .= 'Keywords: ' . $other_param['keywords'] . ', ';
                                        }

                                        $param1 = $other_param;
                                    }
                                }

                                echo rtrim($detailsString, ', ');

                                $param1['search_filters'] = $param;

                                $redirect_url = '?' . http_build_query($param1);
                            @endphp
                        </td>
                        <td>
                            <a class="btn btn-sm btn-primary" title="Reopen" href="{{ route('domainReport') . $redirect_url }}" target="_blank"><i class="fa fa-search"></i></a>
                            <a class="btn btn-sm btn-light border-danger mark-as-favorite" data-record-id="{{ $details->id }}" title="Mark as favorite"><i class="fa fa-heart {{ $details->is_favorite ? 'text-danger' : '' }}"></i></a>
                        </td>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input single-search" type="checkbox" value="{{ $details->id }}" id="selectSearch{{ $details->id }}">
                                <label class="form-check-label" for="selectSearch{{ $details->id }}">
                                    Select
                                </label>
                            </div>
                        </td>
                    </tr>
                    @endforeach

                    @if ($model->total() <= 0)
                        <tr>
                            <td colspan="6">No records found.</td>
                        </tr>
                    @endif
                </tbody>

                @if ($model->total())
                <thead>
                    <tr>
                        <td colspan="5">&nbsp;</td>
                        <td>
                            <a class="btn btn-sm btn-danger" title="Delete" id="delete-selected-searches"><i class="fa fa-trash"></i> Delete</a>
                        </td>
                    </tr>
                </thead>
                @endif
            </table>
        </div>
    </div>
</div>
@stop

@section('scripts')
<script type="text/JavaScript">
    $(document).ready(function() {
        $('#check-all').click(function() {
            $('.single-search').attr('checked', $(this).is(':checked'));
        });

        $('.mark-as-favorite').click(function() {
            var currentElement = $(this);
            var id = currentElement.attr('data-record-id');

            formData = {};
            formData.id = id;

            $.ajax({
                type: "POST",
                url: "{{ url('toggle_favorite') }}",
                data: formData,
                dataType: "json",
                //if received a response from the server
                success: function(data, textStatus, jqXHR) {
                    currentElement.toggleClass('text-danger');
                },
                //If there was no resonse from the server
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR, textStatus, errorThrown);
                },
                //capture the request before it was sent to server
                beforeSend: function(jqXHR, settings) {
                    $(".sys-overlay").fadeIn(300);
                },
                //this is called after the response or error functions are finished
                //so that we can take some action
                complete: function(jqXHR, textStatus) {
                    $(".sys-overlay").fadeOut(300);
                }
            });
        });

        $('#delete-selected-searches').click(function() {
            var selectedSearches = [];
            $('.single-search:checked').each(function() {
                selectedSearches.push($(this).val());
            });

            formData = {};
            formData.ids = selectedSearches;

            $.ajax({
                type: "POST",
                url: "{{ url('delete_search_history') }}",
                data: formData,
                dataType: "json",
                //if received a response from the server
                success: function(data, textStatus, jqXHR) {
                    window.location.reload();
                },
                //If there was no resonse from the server
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR, textStatus, errorThrown);
                },
                //capture the request before it was sent to server
                beforeSend: function(jqXHR, settings) {
                    $(".sys-overlay").fadeIn(300);
                },
                //this is called after the response or error functions are finished
                //so that we can take some action
                complete: function(jqXHR, textStatus) {
                    $(".sys-overlay").fadeOut(300);
                }
            });
        });
    });
</script>
@stop