@extends('layouts.master')

@section('content')

@if(session()->has('success'))
    <div class="alert alert-success">
        <button type="button" class="close" data-dismiss="alert">×</button>
        {{ session()->get('success') }}
    </div>
@endif
@if(session()->has('error'))
    <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert">×</button>
        {{ session()->get('error') }}
    </div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Manage Industries</h6>
    </div>
    <div class="card-body">
        <form id="search-report" action="{{ url('add_industry') }}" method="post">
            @csrf
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div><i class="fa fa-exclamation-triangle"></i>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            <div class="form-row">
                <div class="form-group col-md-3">
                    <!-- <label for="industry_name">Industry Name</label> -->
                    <input type="text" name="industry_name" class="form-control" id="industry_name" placeholder="Industry Name" autocomplete="off" value="{{ request()->post('industry_name') }}">
                </div>
            </div>
            <div class="float-right">
                <button type="submit" class="btn btn-primary" id="filter-search"><i class="fa fa-save"></i> Save</button>
                <a href="{{ url('manage_industries') }}" class="btn btn-warning"><i class="fa fa-sync"></i> Reset</a>
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
                        <span class="text"><i class="fas fa-calculator"></i> Total Records</span>
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
                        <th>@sortablelink('created_at', 'Created Date')</th>
                        <th>@sortablelink('industry_name', 'Industry Name')</th>
                        <th>Actions</th>
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
                        <td>{{ $details->industry_name }}</td>
                        <td>
                            <a class="btn btn-sm btn-primary" title="Edit" href="{{ route('manageIndustries') }}"><i class="fa fa-edit"></i></a>
                            <a class="btn btn-sm btn-danger" title="Edit" href="{{ route('deleteIndustry', $details->id) }}"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    @endforeach

                    @if ($model->total() <= 0)
                        <tr>
                            <td colspan="4">No records found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('scripts')
<script type="text/JavaScript">
    $(document).ready(function() {
        
    });
</script>
@stop