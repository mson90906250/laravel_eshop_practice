@extends('admin.layouts.app')

@section('scripts')

    @parent

    {{-- chart --}}
    <script type="text/javascript" src="{{ asset('js/chart/Chart.js') }}"></script>

    <script>

        $(function () {

            new Chart($("#myLineChart"), {
                "type": "line",
                "data": {
                    "labels": {!! $dayRangeJson !!},
                    "datasets": [{
                        "label": "本月每日營收",
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
                        "label": "本月每日營收",
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

        });

    </script>

@endsection

@section('content')

    <!-- Begin Page Content -->
    <div class="container-fluid">

        @include('admin.includes.alert')

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">今日統計</h1>
        </div>

        <!-- Content Row -->
        <div class="row">

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">

                <div class="card border-left-primary shadow h-100 py-2">

                    <div class="card-body">

                        <div class="row no-gutters align-items-center">

                            <div class="col mr-2">

                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">今日營收</div>

                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayTotalRevenue }}</div>

                            </div>

                            <div class="col-auto">

                                <i class="fas fa-calendar fa-2x text-gray-300"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">本月營收</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $monthlyTotalRevenue }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">今天註冊人數</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayTotalRegistered }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Requests Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">本月註冊人數</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $monthlyTotalRegistered }}</div>
                    </div>
                    <div class="col-auto">
                    <i class="fas fa-comments fa-2x text-gray-300"></i>
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
                    <h6 class="m-0 font-weight-bold text-primary">本月每日營收</h6>
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
                    <h6 class="m-0 font-weight-bold text-primary">本月銷售 Top 10</h6>
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
