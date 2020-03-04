@php

    use App\Models\PaymentMethod;

@endphp

@extends('admin.layouts.app')

@section('scripts')

    @parent

    <script>

        $(function () {

            //---排序用
            var orderAttribute = "{{ request()->input('order_by') }}";

            var isAsc = "{{ request()->input('is_asc') }}";

            $('a.sort-group').each(function () {

                var selfUrl = new URL($(this).attr('href'));

                if (orderAttribute === selfUrl.searchParams.get('order_by').replace('-', '')) {

                    if (isAsc == 1) {

                        $(this).addClass('sort-asc');

                        $(this).attr('href', "{!! route('admin.paymentMethod.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 0])) !!}");

                    } else {

                        $(this).addClass('sort-desc');

                        $(this).attr('href', "{!! route('admin.paymentMethod.index', array_merge(request()->except('_token'), ['order_by' => request()->input('order_by'), 'is_asc' => 1])) !!}");

                    }

                }

            });

            //選擇全部checkbox
            $('#select-all').on('click', function () {

                if (this.checked) {

                    $('tbody input[type="checkbox"]').not(':checked').trigger('click');

                } else {

                    $('tbody input[type="checkbox"]:checked').trigger('click');

                }

            });

        });

    </script>

@endsection

@section('content')

    @include('admin.includes.alert')

    <!-- DataTales Example -->
    <div class="card shadow mb-4">

        <div class="card-header py-3">

            <h1 class="m-0 font-weight-bold text-primary">支付方法清單</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '支付方法清單' => ''
                ]
            ])

        </div>

        <div class="card-body">

            {{-- search --}}
            {!! Form::open(['url' => route('admin.paymentMethod.index'), 'method' => 'GET']) !!}

                <div class="row mb-3">

                    <div class="col-3 p-4">

                        {!! Form::bsText('名稱', 'name', request()->input('name') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        {!! Form::bsSelect('狀態', 'status', request()->input('status') ?? '', PaymentMethod::getStatusLabelList(), '全部', 'vertical') !!}

                    </div>

                    <div class="col-3 p-4">

                        <br>

                        {!! Form::button('搜尋', ['type' => 'Submit', 'class' => 'btn btn-primary mt-2']) !!}

                    </div>


                </div>

            {!! Form::close() !!}

            {!! Form::open(['url' => route('admin.paymentMethod.updateStatus'), 'method' => 'POST']) !!}

                @method('PUT')

                {{-- button --}}
                {!! Form::button('開啓', ['type' => 'submit', 'name' => 'status', 'value' => PaymentMethod::STATUS_ON, 'class' => 'btn btn-success']) !!}

                {!! Form::button('關閉', ['type' => 'submit', 'name' => 'status', 'value' => PaymentMethod::STATUS_OFF, 'class' => 'btn btn-danger']) !!}

                {{-- table --}}
                <div class="table-responsive">

                    <table class="table table-bordered mt-2" id="dataTable" width="100%" cellspacing="0">

                        <thead>

                            <tr>

                                <th>

                                    <input id="select-all" type="checkbox">

                                </th>

                                <th>

                                    Logo

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.paymentMethod.index', array_merge(request()->except('_token'), ['order_by' => 'name'])) }}">

                                        名稱

                                    </a>

                                </th>

                                <th>

                                    <a class="sort-group" href="{{ route('admin.paymentMethod.index', array_merge(request()->except('_token'), ['order_by' => 'status'])) }}">

                                        狀態

                                    </a>

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($paymentMethodList as $paymentMethod)

                                <tr>

                                    <td>

                                        <input name="id[]" type="checkbox" value='{{ $paymentMethod->id }}'>

                                    </td>

                                    <td>

                                        <div style="display: flex; justify-content: center">

                                            <img style="max-width: 50px" src="{{ asset($paymentMethod->logo_url) }}" alt="">

                                        </div>

                                    </td>

                                    <td>{{ $paymentMethod->name }}</td>

                                    <td>{{ PaymentMethod::getStatusLabelList()[$paymentMethod->status] }}</td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            {!! Form::close() !!}

            {{ $paymentMethodList->links() }}

        </div>

    </div>

@endsection
