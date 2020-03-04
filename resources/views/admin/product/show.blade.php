@php

    use App\Models\Product;
    use App\Helper\DetailView;

@endphp

@extends('admin.layouts.app')

@section('styles')

    @parent

    <link href="{{ asset('css/admin/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">

@endsection

@section('scripts')

    @parent

    {{-- Quill --}}
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

    <script>

        $(function () {

            //---Quill
            var quill = new Quill('#product-description', {
                modules: {
                    toolbar: []
                },
            });

            var oldDescription = '{!! str_replace('\n', '\\\n', $product->description) !!}';

            oldDescription = JSON.parse(oldDescription);

            quill.setContents(oldDescription);

            quill.enable(false);

        });

    </script>

@endsection

@section('content')

    @include('admin.includes.alert')

    <!-- DataTales Example -->
    <div class="card shadow mb-4">

        <div class="card-header py-3">

            <h1 class="m-0 font-weight-bold text-primary">{{ $product->name }}</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '商品清單' => route('admin.product.index'),
                    $product->name => ''
                ]
            ])

        </div>

        <div class="card-body">

            {{-- button --}}
            <a href="{{ route('admin.product.edit', ['product' => $product->id]) }}"
                class="btn btn-info mb-3">修改</a>

            {{-- detail view --}}
            {!!
                DetailView::get($product, [
                    'table' => [
                        'class' => 'table table-bordered',
                    ],
                    'columns' => [
                        'name',
                        [
                            'attribute' => 'brand_id',
                            'value'     => function ($attribute, $value, $model) {

                                return $model->brand->name;

                            }
                        ],
                        [
                            'attribute' => 'categories',
                            'label'     => '類型',
                            'value'     => function ($attribute, $value, $model) {

                                $value = '';

                                foreach ($model->categories as $category) {

                                    $value = sprintf('%s%s, <br>', $value, $category->name);

                                }

                                return rtrim($value, ', <br>');

                            }
                        ],
                        'original_price',
                        [
                            'attribute' => 'status',
                            'value'     => function ($attribute, $value, $model) {

                                return Product::getStatusLabels()[$value];

                            }
                        ],
                        [
                            'attribute' => 'description',
                            'value'     =>  function ($attribute, $value, $model) {

                                return sprintf('<div id="product-description"></div>');

                            }
                        ],
                        [
                            'attribute' => 'images',
                            'label'     => '圖片',
                            'options'   => [
                                'style'     => [
                                    'max-height' => '400px',
                                    'overflow-y' => 'scroll'
                                ],
                            ],
                            'value'     => function ($attribute, $value, $model) {

                                $html = '<div class="row">%s</div>';

                                $content = '';

                                foreach ($model->images as $image) {

                                    $imageDiv = sprintf('<div class="col-2 mb-2"><img style="width: 100%%" src="%s"></div>', asset($image->url));

                                    $content = sprintf('%s%s', $content, $imageDiv);

                                }

                                return sprintf($html, $content);

                            }
                        ],
                    ]
                ])
            !!}

            {{-- stock --}}
            <div class="table-responsive">

                <h5 class="mt-5">商品規格</h5>

                <table class="table table-bordered mt-2" id="dataTable" width="100%" cellspacing="0">

                    <thead>

                        <tr>

                           <th>圖片</th>

                           <th>規格</th>

                           <th>庫存</th>

                           <th>價格</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach ($product->stocks as $stock)

                            <tr>

                               <td style="width: 100px">

                                    <img style="width: 75px" src="{{ asset($stock->image ? $stock->image->url : config('custom.no_image_url')) }}" alt="">

                               </td>

                               <td>{{ $stock->attribute }}</td>

                               <td>{{ $stock->quantity }}</td>

                               <td>{{ $stock->price }}</td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection
