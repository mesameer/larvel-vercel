<?php
$searchBy = ['Group Name', 'Country Name', 'State Name', 'County Name', 'City Name'];
$generateBy = ['State Name', 'City Name', 'Group Name'];
?>
@extends('layouts.master')

@section('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" rel="stylesheet" />
@stop
@section('content')

@if(session()->has('success'))
    <div class="alert alert-success">
        <button type="button" class="close" data-dismiss="alert">×</button>
        {{ session()->get('success') }}
    </div>
@endif

<ul class="nav nav-tabs">
  <li class="nav-item">
    <a class="nav-link active" href="{{ route('domainReport') }}">Domain Report</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="{{ route('searchHistory') }}">Saved Searches</a>
  </li>
  <li class="nav-item">
    <a class="nav-link disabled">Purchased Domains</a>
  </li>
</ul>
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <!-- <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Domain Report</h6>
    </div> -->
    <div class="card-body">
        <form id="search-report" method="get" action="{{ url('domain_report') }}">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <select class="form-control" id="search_by">
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
                <div class="form-group col-md-3 d-none">
                    <input type="text" class="form-control" id="search" placeholder="Search..." autocomplete="off" value="{{ request()->get('search') }}">
                    <input type="hidden" id="search_country" name="search_country" value="{{ request()->get('search_country') }}">
                    <input type="hidden" id="search_state" name="search_state" value="{{ request()->get('search_state') }}">
                    <input type="hidden" id="search_county" name="search_county" value="{{ request()->get('search_county') }}">
                </div>
            </div>
            
            @if (request()->get('search_filters'))
            <div class="form-row">
                <div class="col">
                @php
                $search_filters = request()->get('search_filters');

                try {
                    $us_result = json_decode($search_filters, true);
                }
                catch (Throwable $e){
                    $us_result = false;
                }

                if ($us_result !== false) {
                    $search_filters = $us_result;
                }
                
                $counter = 1;
                @endphp
                @foreach ($search_filters as $details)
                <span class="search-tag">
                    <span class="form-tag-search">
                        <span class="text-gray-500">{{ $details['search_by'] }}: </span>
                        <span class="tag-val">{{ $details['search'] }}</span>
                        <span data-role="remove"></span>
                    </span>
                    <input type="hidden" name="search_filters[{{ $counter }}][search_by]" value="{{ $details['search_by'] }}" />
                    <input type="hidden" name="search_filters[{{ $counter }}][search]" value="{{ $details['search'] }}" />

                    @if (!empty($details['search_country']))
                    <input type="hidden" name="search_filters[{{ $counter }}][search_country]" value="{{ $details['search_country'] }}" />
                    @endif
                    @if (!empty($details['search_state']))
                    <input type="hidden" name="search_filters[{{ $counter }}][search_state]" value="{{ $details['search_state'] }}" />
                    @endif
                    @if (!empty($details['search_county']))
                    <input type="hidden" name="search_filters[{{ $counter++ }}][search_county]" value="{{ $details['search_county'] }}" />
                    @endif
                </span>
                @endforeach
                </div>
            </div>
            @endif
            <div class="float-right">
                <button type="submit" class="btn btn-primary" id="filter-search"><i class="fa fa-search"></i> Search</button>
                <a href="{{ url('domain_report') }}" class="btn btn-warning"><i class="fa fa-sync"></i> Reset</a>
            </div>
        </form>
    </div>
</div>
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Get Available Domains</h6>
            </div>
            <div class="card-body">
                <form id="get-domains" method="get" action="{{ url('domain_report') }}">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <select class="form-control" id="generate_by" name="generate_by">
                                <option value="">-- Generate By --</option>
                                @foreach($generateBy as $generate_by)
                                @if(request()->get('generate_by') == $generate_by)
                                <option selected="selected" value="{{ $generate_by }}">{{ $generate_by }}</option>
                                @else
                                <option value="{{ $generate_by }}">{{ $generate_by }}</option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control" data-role="tagsinput" id="keywords" name="keywords" placeholder="Keywords..." value="{{ request()->get('keywords') }}" />
                        </div>
                        <div class="form-group col-md-3">
                            <div class="form-check mt-2">
                                <input class="form-check-input" @if(request()->get('maintainOrder')) checked="checked" @endif type="checkbox" id="maintainOrder" name="maintainOrder">
                                <label class="form-check-label" for="maintainOrder">Maintain keywords order</label>
                            </div>
                        </div>
                    </div>
                    <div class="float-right">
                        @if (isset($search_filters))
                        <input type="hidden" name="search_filters" value="{{ json_encode($search_filters) }}">
                        @endif

                        <input type="hidden" name="record_type" value="{{ request()->get('record_type') }}">
                        <input type="hidden" name="type" value="{{ request()->get('type') }}">

                        <button type="submit" class="btn btn-primary" id="domain-search"><i class="fa fa-search"></i> Search Domains</button>
                    </div>
                </form>
            </div>
        </div>

        @if(request()->get('generate_by') == 'Group Name')
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-12 col-md-3">
                        <select class="form-control" id="search_by">
                            <option value="">-- Search Industry --</option>
                            @foreach($industries as $industry)
                                <option value="{{ $industry['id'] }}">{{ $industry['industry_name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-12 col-md-4">
                        <div class="float-left">
                            <div class="input-group">
                                <input class="form-control" id="virtual_city_name" placeholder="Virtual City Name" />
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" id="get-domains-by-group">Get Domains</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row domain-container-2 mt-3 d-none">
                    <div class="col-sm-12">
                        <div class="domain-container"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="table-responsive">
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <a href="#" class="btn btn-primary btn-icon-split">
                        <span class="text"><i class="fas fa-calculator"></i> Total Records</span>
                        <span class="icon">{{ number_format($locations->total()) }}</span>
                    </a>
                </div>
                <div class="col-sm-12 col-md-6">
                    <div class="float-right">{{ $locations->appends($_GET)->links() }}</div>
                </div>
            </div>
            <table class="table table-bordered table-striped data-records" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <!-- <th>Status</th> -->
                        <th>@sortablelink('country', 'Country') <span>{{ $uniqueCount['country'] }}</span></th>
                        <th>@sortablelink('state_name', 'State') <span>{{ number_format($uniqueCount['state_name']) }}</span></th>
                        <!-- <th>State Lat/Lng</th> -->
                        <th>@sortablelink('county_name', 'County') <span>{{ number_format($uniqueCount['county_name']) }}</span></th>
                        <!-- <th>County Lat/Lng</th> -->
                        <th>@sortablelink('city_name', 'City') <span>{{ number_format($uniqueCount['city_name']) }}</span></th>
                        <!-- <th>City Lat/Lng</th> -->
                        <th>@sortablelink('zip_code', 'ZIP') <span>{{ number_format($uniqueCount['zip_code']) }}</span></th>
                        <th>ZIP Lat/Lng</th>
                        <!-- <th>Timezone</th> -->
                        <th>Area Code</th>
                        <th>@sortablelink('zip_population', 'Polulation') <span>{{ number_format($uniqueCount['zip_population']) }}</span></th>
                        <!-- <th>Type</th> -->
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                        <!-- <th>Status</th> -->
                        <th>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" <?php echo (request()->get('record_type') == 'country' && request()->get('type') == 'unique') ? 'checked="checked"' : ''; ?> class="custom-control-input toggle-collapse" data-record="country" id="toggle-country">
                                <label class="custom-control-label" for="toggle-country">Unique</label>
                            </div>
                        </th>
                        <th>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" <?php echo (request()->get('record_type') == 'state' && request()->get('type') == 'unique') ? 'checked="checked"' : ''; ?> class="custom-control-input toggle-collapse" data-record="state" id="toggle-state">
                                <label class="custom-control-label" for="toggle-state">Unique</label>
                            </div>
                        </th>
                        <!-- <th>State Lat/Lng</th> -->
                        <th>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" <?php echo (request()->get('record_type') == 'county' && request()->get('type') == 'unique') ? 'checked="checked"' : ''; ?> class="custom-control-input toggle-collapse" data-record="county" id="toggle-county">
                                <label class="custom-control-label" for="toggle-county">Unique</label>
                            </div>
                        </th>
                        <!-- <th>County Lat/Lng</th> -->
                        <th>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" <?php echo (request()->get('record_type') == 'city' && request()->get('type') == 'unique') ? 'checked="checked"' : ''; ?> class="custom-control-input toggle-collapse" data-record="city" id="toggle-city">
                                <label class="custom-control-label" for="toggle-city">Unique</label>
                            </div>
                        </th>
                        <!-- <th>City Lat/Lng</th> -->
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <!-- <th>Timezone</th> -->
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <!-- <th>Type</th> -->
                    </tr>
                </thead>
                <tbody>
                    @php
                    $counter = ($locations->currentPage() - 1) * $locations->perPage();
                    @endphp
                    @foreach($locations as $locationDetails)
                    <tr>
                        <td>{{ ++$counter }}</td>
                        <!-- <td>{{ $locationDetails->status }}</td> -->
                        <td>{{ $locationDetails->country }}</td>
                        <td><span class="<?php echo (request()->get('generate_by') == 'State Name' ? 'location-name' : ''); ?>">{{ $locationDetails->state_name }}</span> ({{ $locationDetails->state_short_name ? $locationDetails->state_short_name : $locationDetails->state_name }})
                            @if(request()->get('generate_by') == 'State Name')
                            <a href="javascript:void(0);" class="btn btn-sm btn-success btn-circle float-right get-domains" data-collapse="false" title="View available domains">
                                <i class="fas fa-plus"></i>
                            </a>
                            @endif
                        </td>
                        <!-- <td>{{ $locationDetails->state_lat }}, {{ $locationDetails->state_lng }}</td> -->
                        <td>{{ $locationDetails->county_name }}</td>
                        <!-- <td>{{ $locationDetails->county_lat }}, {{ $locationDetails->county_lng }}</td> -->
                        <td><span class="<?php echo (request()->get('generate_by') == 'City Name' ? 'location-name' : ''); ?>">{{ $locationDetails->city_name }}</span>
                            @if(request()->get('generate_by') == 'City Name')
                            <a href="javascript:void(0);" class="btn btn-sm btn-success btn-circle float-right get-domains" data-collapse="false" title="View available domains">
                                <i class="fas fa-plus"></i>
                            </a>
                            @endif
                        </td>
                        <!-- <td>{{ $locationDetails->city_lat }}, {{ $locationDetails->city_lng }}</td> -->
                        <td>{{ $locationDetails->zip_code }}
                        @php
                        $counter = 0;
                        $group_name = '';
                        if (isset($search_filters)) {
                            foreach($search_filters as $details) {
                                if ($details['search_by'] == 'Group Name') {
                                    $group_name = $details['search'];
                                    $counter++;
                                }
                            }
                        }
                        @endphp
                        @if(request()->get('type') == 'unique' && request()->get('record_type') == 'city' && $counter == 1)
                            <a href="javascript:void(0);" 
                            data-country="{{ $locationDetails->country }}" 
                            data-state="{{ $locationDetails->state_name }}" 
                            data-county="{{ $locationDetails->county_name }}" 
                            data-city="{{ $locationDetails->city_name }}" 
                            data-group-name="{{ $group_name }}" 
                            class="btn btn-sm btn-info btn-circle float-right edit-zip-code"><i class="fa fa-edit"></i></a>
                        @endif
                        </td>
                        <td>{{ $locationDetails->zip_lat }}, {{ $locationDetails->zip_lng }}</td>
                        <!-- <td>{{ $locationDetails->timezone }}</td> -->
                        <td>{{ $locationDetails->area_code }}</td>
                        <td>{{ number_format($locationDetails->zip_population) }}</td>
                        <!-- <td>{{ $locationDetails->type }}</td> -->
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(request()->get('type') != 'unique')
        <div class="float-right mt-3">
            <button type="button" class="btn btn-primary" id="create-groups" data-toggle="modal" data-target="#createGroupModal"><i class="fa fa-users"></i> Create Group</button>
            <!-- <button type="button" class="btn btn-primary"><i class="fa fa-shopping-cart"></i> Purchase</button> -->
        </div>
        
        <!-- Create Group Modal-->
        <div class="modal fade" id="createGroupModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Create Group</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="groupName" class="col-sm-3 col-form-label">Group Name: </label>
                            <div class="col-sm-9">
                                <input type="text" id="groupName" class="form-control" placeholder="Enter group name" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                        <a class="btn btn-primary" href="javascript:void(0);" id="create-group">Create Group</a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
<!-- Add Zip Code Modal-->
<div class="modal fade" id="addZipModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Zip Codes</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <div class="col-sm-4 offset-sm-8">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" for="search-zip"><i class="fa fa-search"></i></span>
                            </div>
                            <input type="text" class="form-control float-right" id="search-zip" placeholder="Search..." aria-describedby="basic-addon1">
                        </div>
                    </div>
                </div>
                <div class="form-group tags-input">
                    <textarea id="defaultZipCodes" rows="5" cols="5" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-primary" href="javascript:void(0);" id="add-zip"><i class="fa fa-plus"></i> Add</a>
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.js"></script>
<script type="text/JavaScript">
    var formDataLocation;
    $(document).ready(function() {
        $(document).on('change', '#search_by', function() {
            searchBy = $(this).val();
            if (searchBy == '') {
                $('#search').parent().addClass('d-none');
            }
            else {
                $('#search').parent().removeClass('d-none');
                $('#search').val('');
            }
        });

        if ($('#search_by').val() != '') {
            $('#search').parent().removeClass('d-none');
        }

        $('#get-domains-by-group').click(function() {
            var virtualCityName = $('#virtual_city_name').val();

            if (virtualCityName == '') {
                alert('Virtual city name is required.');
                return false;
            }

            formData = {};
            formData.maintainOrder = $('#maintainOrder').is(':checked') ? 1 : 0;
            formData.keywords = $('#keywords').val();
            formData.location = virtualCityName;

            $.ajax({
                type: "POST",
                url: "{{ url('available_domains') }}",
                data: formData,
                dataType: "json",
                //if received a response from the server
                success: function(data, textStatus, jqXHR) {
                    var htmlString = 'No domain available.';

                    var data = Object.entries(data);
                    
                    if (data.length > 0) {
                        htmlString = '';
                        var counter = 0;

                        data.forEach(function(tldDetails) {
                            tldName = tldDetails[0];
                            tldData = tldDetails[1];

                            htmlString += '<div class="row-header"><div class="col-md-12">'+ tldName +'</div></div>';

                            if (tldData.length > 0) {
                                htmlString += '<div class="row">';

                                tldData.forEach(function(domain) {
                                    htmlString += `<div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="defaultCheck`+ ++counter +`">
                                                        <label class="form-check-label" for="defaultCheck`+ counter +`">
                                                            `+ domain +`
                                                        </label>
                                                    </div>
                                                    </div>`;
                                });
                                
                                htmlString += '</div>';
                            }
                            
                            // htmlString += '</div>';
                        });
                    }

                    $('.domain-container-2').removeClass('d-none');
                    $('.domain-container').html(htmlString);
                    
                    $(".sys-overlay").fadeOut(300);
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

        $(document).on('click', '#filter-search', function() {
            if (typeof $('input[name="search_filters"]').val() != 'undefined') {
                var filters = Object.values(JSON.parse($('input[name="search_filters"]').val()));
            }
            else {
                var filters = [];
            }
            
            var search_by = $('#search_by').val();
            var search = $('#search').val();

            if (search_by && search) {
                var newFilter = {};

                newFilter.search_by = search_by;
                newFilter.search = search;

                if ($('#search_country').val()) {
                    newFilter.search_country = $('#search_country').val();
                }
                if ($('#search_state').val()) {
                    newFilter.search_state = $('#search_state').val();
                }
                if ($('#search_county').val()) {
                    newFilter.search_county = $('#search_county').val();
                }

                filters.push(newFilter);
            }

            formData = {};
            formData.type = 'filter_search';
            formData.title = 'Filter Search';
            formData.param = filters;

            $.ajax({
                type: "POST",
                url: "{{ url('save_search_history') }}",
                data: formData,
                dataType: "json",
                //if received a response from the server
                success: function(data, textStatus, jqXHR) {
                    $('#search-report').submit();
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

            return false;
        });

        $(document).on('click', '#domain-search', function() {
            var filters = Object.values(JSON.parse($('input[name="search_filters"]').val()));
            
            var generate_by = $('#generate_by').val();
            var keywords = $('#keywords').val();
            var maintainOrder = $('#maintainOrder').is(':checked') ? 1 : 0;

            formData = {};
            formData.type = 'domain_search';
            formData.title = 'Domain Search';
            formData.param = filters;
            formData.other_param = {'generate_by': generate_by, 'keywords': keywords, 'maintainOrder': maintainOrder};

            $.ajax({
                type: "POST",
                url: "{{ url('save_search_history') }}",
                data: formData,
                dataType: "json",
                //if received a response from the server
                success: function(data, textStatus, jqXHR) {
                    $('#get-domains').submit();
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

            return false;
        });

        var routeAuto = "{{ url('autocomplete_city') }}";

        $('#search').typeahead({
            source: function(query, process) {
                return $.get(routeAuto, {
                    query: query, 
                    search_by: $('#search_by').val()
                }, function(data) {
                    return process(data);
                });
            },
            displayText: function(item) {
                return item.info;
            }
        });

        $('#search').change(function() {
            var current = $(this).typeahead("getActive");
            
            if ($('#search_by').val() == 'Group Name') {
                $(this).val(current.info);
            }
            else if ($('#search_by').val() == 'Country Name') {
                $(this).val(current.country);
            }
            else if ($('#search_by').val() == 'State Name') {
                $(this).val(current.state_name);
                $('#search_country').val(current.country);
            }
            else if ($('#search_by').val() == 'County Name') {
                $(this).val(current.county_name);
                $('#search_country').val(current.country);
                $('#search_state').val(current.state_name);
            }
            else if ($('#search_by').val() == 'City Name') {
                $(this).val(current.city_name);
                $('#search_country').val(current.country);
                $('#search_state').val(current.state_name);
                $('#search_county').val(current.county_name);
            }
        });

        $(document).on('change', '.toggle-collapse', function() {
            var checked = $(this).is(':checked');

            $('.toggle-collapse').attr('checked', false);
            $(this).attr('checked', checked);
            
            var recordType = $(this).attr('data-record');
            var type = $(this).is(':checked') ? 'unique' : 'all';

            url = removeURLParameter(window.location.href, 'record_type');
            url = removeURLParameter(url, 'type');

            if (url.includes('?')) {
                window.location.href = url + '&record_type=' + recordType + '&type=' + type;
            }
            else {
                window.location.href = url + '?record_type=' + recordType + '&type=' + type;
            }
        });

        $(document).on('click', '.get-domains', function() {
            var currentElement = $(this);

            if (currentElement.attr('data-collapse') == 'true') {
                currentElement.find('.fas').removeClass('fa-minus').addClass('fa-plus');
                currentElement.attr('data-collapse', 'false');
                
                currentElement.closest('tr').next('tr.domain').remove();
                return;
            }
            else {
                currentElement.find('.fas').removeClass('fa-plus').addClass('fa-minus');
                currentElement.attr('data-collapse', 'true');
            }
            
            var location = currentElement.closest('tr').find('.location-name').text();

            formData = {};
            formData.maintainOrder = $('#maintainOrder').is(':checked') ? 1 : 0;
            formData.keywords = $('#keywords').val();
            formData.location = location;

            $.ajax({
                type: "POST",
                url: "{{ url('available_domains') }}",
                data: formData,
                dataType: "json",
                //if received a response from the server
                success: function(data, textStatus, jqXHR) {
                    var htmlString = 'No domain available.';

                    var data = Object.entries(data);
                    
                    if (data.length > 0) {
                        htmlString = '';
                        var counter = 0;

                        data.forEach(function(tldDetails) {
                            tldName = tldDetails[0];
                            tldData = tldDetails[1];

                            htmlString += '<div class="row row-header"><div class="col-md-12">'+ tldName +'</div></div>';

                            if (tldData.length > 0) {
                                htmlString += '<div class="row">';

                                tldData.forEach(function(domain) {
                                    htmlString += `<div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="defaultCheck`+ ++counter +`">
                                                        <label class="form-check-label" for="defaultCheck`+ counter +`">
                                                            `+ domain +`
                                                        </label>
                                                    </div>
                                                    </div>`;
                                });
                                
                                htmlString += '</div>';
                            }
                            
                            // htmlString += '</div>';
                        });
                    }

                    currentElement.closest('tr').after('<tr class="domain"><td colspan="9">'+ htmlString +'</td></tr>');
                    
                    $(".sys-overlay").fadeOut(300);
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

        $('#search-report').submit(function () {
            var current = $('#search').typeahead("getActive");
            
            if (current) {
                $(this).append($("<input>").attr({
                                    name: "search_filters[0][search_by]",
                                    type: "hidden",
                                    value: $('#search_by').val()
                                }));

                $(this).append($("<input>").attr({
                                    name: "search_filters[0][search]",
                                    type: "hidden",
                                    value: $('#search').val()
                                }));

                if (current.hasOwnProperty('country')) {
                    $(this).append($("<input>").attr({
                                    name: "search_filters[0][search_country]",
                                    type: "hidden",
                                    value: current.country
                                }));
                }

                if (current.hasOwnProperty('state_name')) {
                    $(this).append($("<input>").attr({
                                    name: "search_filters[0][search_state]",
                                    type: "hidden",
                                    value: current.state_name
                                }));
                }

                if (current.hasOwnProperty('county_name')) {
                    $(this).append($("<input>").attr({
                                    name: "search_filters[0][search_county]",
                                    type: "hidden",
                                    value: current.county_name
                                }));
                }
            }
        });

        $('#create-group').click(function() {
            var groupName = $('#groupName').val();

            if (groupName == '') {
                return false;
            }

            $('#get-domains').append($("<input>").attr({
                                    name: "group_name",
                                    type: "hidden",
                                    value: groupName
                                }));
            
            $('#get-domains').submit();
        });

        var route = "{{ url('get_zip_codes') }}";

        $('#defaultZipCodes').tagsinput({
            itemValue: 'id',
            itemText: 'zip_code',
            tagClass: function(item) {
                return 'badge badge-primary';
            },
            typeahead: {
                displayKey: 'zip_code',
                valueKey: 'id',
                source: function(query) {
                    return $.post(route, {
                        query: query
                    }, function(data) {
                        return data;
                    });
                },
                afterSelect: function(q) {
                    $('.bootstrap-tagsinput input').val('');
                }
            }
        });
        
        $(document).on('keyup', '#search-zip', function() {
            var zipCode = $(this).val();

            $('.tags-input .bootstrap-tagsinput .tag').each(function() {
                if ($(this).text().includes(zipCode)) {
                    $(this).removeClass('badge-primary').addClass('badge-success');
                }
                else {
                    $(this).addClass('badge-primary').removeClass('badge-success');
                }
            });
        });

        $('.edit-zip-code').click(function() {
            formDataLocation = {};
            formDataLocation.country = $(this).attr('data-country');
            formDataLocation.state = $(this).attr('data-state');
            formDataLocation.county = $(this).attr('data-county');
            formDataLocation.city = $(this).attr('data-city');
            formDataLocation.locationGroup = $(this).attr('data-group-name');

            $.ajax({
                type: "POST",
                url: "{{ url('get_location_group_zip_codes') }}",
                data: formDataLocation,
                dataType: "json",
                //if received a response from the server
                success: function(data, textStatus, jqXHR) {
                    if (data) {
                        data.forEach(function(value) {
                            $('#defaultZipCodes').tagsinput('add', value);
                        });
                    }

                    $('#addZipModal').modal('show');

                    $(".sys-overlay").fadeOut(300);
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

        $('#add-zip').click(function() {
            console.log(formDataLocation);

            formData = formDataLocation;
            formData.locations = $('#defaultZipCodes').tagsinput('items');

            $.ajax({
                type: "POST",
                url: "{{ url('save_zip_codes') }}",
                data: formData,
                dataType: "json",
                //if received a response from the server
                success: function(data, textStatus, jqXHR) {
                    if (data.success == 'true') {
                        alert('Zip codes updated successfully.');
                        $('#addZipModal').modal('hide');
                    }
                    $(".sys-overlay").fadeOut(300);
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

    function removeURLParameter(url, parameter) {
        //prefer to use l.search if you have a location/link object
        var urlparts = url.split('?');   
        if (urlparts.length >= 2) {

            var prefix = encodeURIComponent(parameter) + '=';
            var pars = urlparts[1].split(/[&;]/g);

            //reverse iteration as may be destructive
            for (var i = pars.length; i-- > 0;) {    
                //idiom for string.startsWith
                if (pars[i].lastIndexOf(prefix, 0) !== -1) {  
                    pars.splice(i, 1);
                }
            }

            return urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : '');
        }
        return url;
    }
</script>
@stop