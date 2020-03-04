
@extends('admin.layouts.app')

@section('styles')

    @parent

    {{-- date picker --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.0-alpha14/css/tempusdominus-bootstrap-4.min.css" />

    <link href="{{ asset('css/admin/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">

@endsection

@section('scripts')

    @parent

    {{-- chart --}}
    <script type="text/javascript" src="{{ asset('js/chart/Chart.js') }}"></script>
    {{--  --}}

    {{-- date picker --}}
    <script src="{{ asset('js/moment/moment.js') }}"></script>

    <script src="{{ asset('js/moment/locale/zh-tw.js') }}"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.0-alpha14/js/tempusdominus-bootstrap-4.min.js"></script>
    {{--  --}}

    <script>

        $(function () {

            //--chart
            new Chart($("#myLineChart"), {
                "type": "line",
                "data": {
                    "labels": {!! $monthRangeJson !!},
                    "datasets": [{
                        "label": "當年總營收",
                        "data": {!! $lineChartDataJson !!},
                        "fill": false,
                        "borderColor": "rgb(75, 192, 192)",
                        "lineTension": 0.1
                    }]
                },
                "options": {}
            });

            new Chart($("#myPieChart"), {
                "type": "pie",
                "data": {
                    "labels": {!! $productNameListJson !!},
                    "datasets": [{
                        "label": "當年銷售Top 10",
                        "data": {!! $productQuantityListJson !!},
                        "backgroundColor" : [
                            'rgb(235, 64, 52)',
                            'rgb(235, 140, 52)',
                            'rgb(235, 195, 52)',
                            'rgb(223, 235, 52)',
                            'rgb(189, 235, 52)',
                            'rgb(134, 235, 52)',
                            'rgb(52, 235, 76)',
                            'rgb(52, 235, 195)',
                            'rgb(52, 205, 235)',
                            'rgb(52, 134, 235)']
                    }]
                },
                "options": {}
            });
            //--

            //--date picker
            $('#datetimepicker1').datetimepicker({
                format: 'YYYY',
            });
            //--

        });

    </script>

@endsection

@section('content')

    <!-- Begin Page Content -->
    <div class="container-fluid">

        @include('admin.includes.alert')

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">每年統計</h1>
        </div>

        {!! Form::open(['url' => route('admin.report.annualReport'), 'method' => 'GET']) !!}

            <div class="row">

                <div class="col-3 p-4">

                    <div class="row">

                        <div class="col-9">

                            <label>時間</label>

                        </div>

                        <div class="col-12">

                            <div class="input-group date" id="datetimepicker1" data-target-input="nearest">

                                <input type="text" class="form-control datetimepicker-input" data-target="#datetimepicker1" name="year" value="{{ request()->input('year') ?? date('Y') }}">

                                <div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">

                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-3 p-4">

                    <br>

                    {!! Form::button('搜尋', ['type' => 'Submit', 'class' => 'btn btn-primary mt-2']) !!}

                </div>

            </div>

        {!! Form::close() !!}

        <!-- Content Row -->
        <div class="row">

            <div class="col-xl-6 col-md-6 mb-4">

                <div class="card border-left-primary shadow h-100 py-2">

                    <div class="card-body">

                        <div class="row no-gutters align-items-center">

                            <div class="col mr-2">

                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">當年營收</div>

                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $selectedYearTotalRevenue }}</div>

                            </div>

                            <div class="col-auto">

                                <i class="fas fa-calendar fa-2x text-gray-300"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="col-xl-6 col-md-6 mb-4">

                <div class="card border-left-info shadow h-100 py-2">

                    <div class="card-body">

                        <div class="row no-gutters align-items-center">

                            <div class="col mr-2">

                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">當年註冊人數</div>

                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $selectedYearTotalRegistered }}</div>

                            </div>

                            <div class="col-auto">

                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- Content Row -->

        <div class="row">

            <!-- Line Chart -->
            <div class="col-xl-8 col-lg-7">

                <div class="card shadow mb-4">

                    <!-- Card Header - Dropdown -->
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">

                        <h6 class="m-0 font-weight-bold text-primary">當年每月營收</h6>

                    </div>

                    <!-- Card Body -->
                    <div class="card-body">

                        <div class="chart-line">

                            <canvas id="myLineChart"></canvas>

                        </div>

                    </div>

                </div>

            </div>

            <!-- Pie Chart -->
            <div class="col-xl-4 col-lg-5">

                <div class="card shadow mb-4">

                    <!-- Card Header - Dropdown -->
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">

                        <h6 class="m-0 font-weight-bold text-primary">當年銷售 Top 10</h6>

                    </div>

                    <!-- Card Body -->
                    <div class="card-body">

                        <div class="chart-pie pt-4 pb-2">

                            <canvas id="myPieChart"></canvas>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
    <!-- /.container-fluid -->

@endsection
