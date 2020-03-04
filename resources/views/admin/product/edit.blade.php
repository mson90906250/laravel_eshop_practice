@php

    use App\Models\Product;
    use App\Models\Category;
    use App\Models\Brand;

@endphp

@extends('admin.layouts.app')

@section('styles')

    @parent

    {{-- DataTable --}}
    <link href="{{ asset('css/admin/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">

    {{-- Quill --}}
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    {{-- Croppie --}}
    <link rel="stylesheet" href="{{ asset('css/croppie/croppie.css') }}">


    <style>

        /* HIDE CHECKBOX */
        input.input-image-block[type=checkbox], [type=radio], input.row-stock[type=checkbox] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* IMAGE STYLES */
        [type=checkbox] + img, [type=radio] + img, [type=checkbox] + div {
            cursor: pointer;
        }

        /* CHECKED STYLES */
        [type=checkbox]:checked + img {
            outline: 5px solid #f00;
        }

        /* CHECKED STYLES */
        [type=radio]:checked + img {
            outline: 5px solid #7d7;
        }

        [type=checkbox]:checked + div {
            outline: 5px solid #5af;
        }


    </style>

@endsection

@section('scripts')

    @parent

    {{-- Quill --}}
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

    {{-- Croppie --}}
    <script src="{{ asset('js/croppie/croppie.js') }}"></script>

    <script>

        var clonedImages = [];
        var rootPath = "{{ asset('') }}";
        var noImageUrl = '{{ asset(config("custom.no_image_url")) }}'

        $(function () {

            //---Quill
            var toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
                ['blockquote'],

                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
                [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
                [{ 'direction': 'rtl' }],                         // text direction

                [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

                [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
                [{ 'font': [] }],
                [{ 'align': [] }]
            ];

            var quill = new Quill('#description-editor', {
                modules: {
                    toolbar: toolbarOptions
                },
                placeholder: 'Compose an epic...',
                theme: 'snow'  // or 'bubble'
            });

            var oldDescription = '{!! str_replace('\n', '\\\n', request()->old("description") ?? $product->description) !!}';

            oldDescription = JSON.parse(oldDescription);

            quill.setContents(oldDescription);

            //限制字數
            quill.on('text-change', function (delta, oldDelta, source) {

                var byteSize = (new TextEncoder().encode(quill.getText())).length;

                if (byteSize > 1500) {

                    quill.setContents(oldDelta);

                    alert('簡介長度不能超過1500');

                }

            });

            $('#form-create').on('submit', function (e) {

                var description = $('textarea[name="description"]');

                description.val(JSON.stringify(quill.getContents()));

            });

            //---Croppie
            $uploadCrop = $('#image-for-croppie').croppie({
                viewport: {
                    width: 300,
                    height: 400,
                },
                boundary: {
                    width: 500,
                    height: 600
                }
            });

            $('input[type="file"]').on('change', function () {

                var reader = new FileReader();

                reader.onload = function (e) {

                    $uploadCrop.croppie('bind', {

                        url: e.target.result

                    }).then(function(){

                        console.log('jQuery bind complete');

                    });

                }

                reader.readAsDataURL(this.files[0]);

            });

            $('#btn-croppie').on('click', function () {

                $uploadCrop.croppie('result', {
                    type: 'canvas',
                    size: 'viewport'
                }).then(function (resp) {

                    var html = '<img id="croppied-image" style="width: 300px; display: block; margin:60px auto" src="' + resp + '" />';

                    $("#croppied-block").html(html);

                });

            })

            $('#btn-upload-image').on('click', function () {

                var imageData = $('#croppied-image').attr('src');

                if (imageData == undefined) {

                    alert('請選擇要上傳的圖片');

                } else {

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    $.ajax({
                        url: '{{ route("admin.product.storeImage") }}',
                        type: 'POST',
                        data: {'image': imageData},
                        dataType: 'JSON',
                        success: function (data) {

                            var html = '<div class="col-2 m-2">'
                                            +'<label>'
                                                +'<input type="checkbox" name="image[]" class="input-image-block" value="'+ data.data.imageId +'">'
                                                +'<img style="width:100%" src="' + rootPath + '/' + data.data.url + '">'
                                                +'<input type="text" name="images_to_store[]" style="visibility: hidden; width: 0; height: 0" value="'+ data.data.imageId +'">'
                                            +'</label>'
                                        +'</div>';

                            $('#image-block').append(html);

                            alert('圖片上傳成功');
                        },
                    });

                }

                //重置
                clonedImages = [];

            });

            //---刪除選擇的圖片
            $('#btn-delete-image').on('click', function () {

                 var data = [];

                $('input[type="checkbox"][name="image[]"]:checked').each(function (index, ele) {

                    //去掉stock的圖片
                    if ($('img.row-stock-image-' + ele.value).length) {

                        $('img.row-stock-image-' + ele.value).prop('src', noImageUrl);

                        $('input.row-stock-image-' + ele.value).val('');

                    }

                    data.push(ele.value);

                });

                if (data.length < 1) {

                    alert('請至少選擇一張要刪除的圖片');

                    return false;

                }

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                $.ajax({
                    url: '{{ route("admin.product.deleteImage") }}',
                    type: 'POST',
                    data: {images: data},
                    dataType: 'JSON',
                    success: function (data) {

                        $('input[name="image[]"]:checked').parent().remove();

                        var uncheckedImages = $('input[name="image[]"]').not('checked').parent().parent();

                        $('#image-block').empty();

                        $('#image-block').append(uncheckedImages);

                    }
                });

                //重置
                clonedImages = [];

            });

            //---新增商品規格
            var count = 1 + {{ request()->old('stock') ? collect(request()->old('stock'))->keys()->max() : $product->stocks->count() - 1 }};

            $('#create-stock').on('click', function () {

                var row = '<label>'
                            +'<input type="checkbox" class="row-stock" value=0>'
                            +'<div class="row m-2">'
                                +'<div class="col-3 row-stock-image" style="display: flex; flex-direction: row; justify-content: center">'
                                    +'<img style="width: 75px" src="'+ noImageUrl +'">'
                                    +'<input class="image-for-stock-url" type="text" name="stock['+ count +'][image_for_stock_url]" style="visibility: hidden; height: 0px; width: 0px">' //用於後臺驗證失敗時,記錄圖片url
                                    +'<input class="image-for-stock" type="text" name="stock['+ count +'][image_id]" style="visibility: hidden; height: 0px; width: 0px">'
                                    +'<input class="stock-id" type="number" name="stock['+ count +'][id]" value=0 style="visibility: hidden; height: 0px; width: 0px">'
                                +'</div>'
                                +'<div class="col-3 mt-2">'
                                    +'<label>規格 ex:藍色 XL...</label>'
                                    +'<input type="text" name="stock['+ count +'][attribute]" class="form-control">'
                                +'</div>'
                                +'<div class="col-2 mt-2">'
                                    +'<label>庫存</label>'
                                    +'<input type="number" name="stock['+ count +'][quantity]" class="form-control">'
                                +'</div>'
                                +'<div class="col-2 mt-2">'
                                    +'<label>價格</label>'
                                    +'<input type="number" name="stock['+ count +'][price]" class="form-control">'
                                +'</div>'
                                +'<div class="col-2" style="display: flex; flex-direction: column; justify-content: center">'
                                    +'<button id="row-stock-'+ count +'" type="button" class="btn btn-primary btn-image-modal" data-type="stock-image" data-toggle="modal" data-target="#imageModal">設定圖片</button>'
                                +'</div>'
                            +'</div>'
                            +'<hr>'
                        +'</label>'

                $('#stock-block').append(row);

                count++;

            });

            //---刪除商品規格
            $('#delete-stock').on('click', function () {

                var stockIdList = [];

                //記錄stock的id
                $('input.row-stock:checked').each(function (index, element) {

                    if (element.value > 0) {

                        stockIdList.push(element.value);

                    }

                });

                //刪除db裡的stock
                if (stockIdList.length > 0) {

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    $.ajax({
                        url: '{{ route('admin.product.deleteStock') }}',
                        type: 'POST',
                        data: {
                            product_id: {{ $product->id }},
                            stockIdList: stockIdList
                        },
                        dataType: 'JSON',
                        success: function (data) {

                            if (!data.status) {

                                alert(data.message);

                                return false;

                            }

                            $('input.row-stock:checked').parent().remove();

                        },
                        error: function (data) {

                            alert('商品規格刪除失敗');

                            return false;

                        }
                    });

                } else {

                    $('input.row-stock:checked').parent().remove();

                }

            });

            //---開啓image modal時根據按鈕來執行不同的功能
            $(document).on('click.imageModel', '.btn-image-modal', function () {

                //將圖片塞入image-modal裡
                if (clonedImages.length < 1) {

                    $('#image-modal-container').empty();

                    clonedImages = $('#image-block').children().clone(true);

                    clonedImages.each(function ($index) {

                        // 將checkbox 轉換成 radio
                        var input = $(this).find('input[type="checkbox"]');

                        input.prop('type', 'radio').prop('name', 'image_for_radio');

                        var oldFirstImageId = "{{ request()->old('first_image') }}";

                        if (input.val() == oldFirstImageId) {

                            input.prop('checked', true);

                        }

                        $(this).find('input[type="text"]').remove();

                    });

                    $('#image-modal-container').append(clonedImages);

                }

                var type = $(this).data('type');

                $('#btn-selected-image').data('type', type);

                if (type === 'stock-image') {

                    $('#btn-selected-image').data('stock-id', $(this).attr('id'));

                }

            });

            $('#btn-selected-image').on('click', function () {

                let input =  $('input[type="radio"][name="image_for_radio"]:checked');

                let selectedImageId = input.val();

                let selectedImage = input.siblings('img').attr('src');

                if (selectedImage != undefined && selectedImage != '') {

                    var type = $(this).data('type');

                    if (type === 'stock-image') {

                        //將選擇的圖片設定到指定的stock
                        var stockRow = $(this).data('stock-id');

                        var stockRowParent = $('#' + stockRow).parent();

                        var imgStock = stockRowParent.siblings('.row-stock-image');

                        imgStock.children('img')
                                .attr('src', selectedImage)
                                .addClass('row-stock-image-' + selectedImageId); //刪除圖片時, 可藉此找到目標stock

                        imgStock.children('input.image-for-stock')
                                .val(selectedImageId)
                                .addClass('row-stock-image-' + selectedImageId);

                        imgStock.children('input.image-for-stock-url')
                                .val(selectedImage)
                                .addClass('row-stock-image-' + selectedImageId);

                    } else {

                        //設定該商品的封面圖片
                        $('input[name="first_image"]').val(selectedImageId);

                    }

                    $('#imageModal').modal('hide');

                } else {

                    alert('請選擇一張圖片');

                }

            });

            //當勾選子類別時,父類別也要跟著被勾選
            $('label.sub-category input[type="checkbox"]').on('click', function () {

                var count = $(this).parent().parent().find('label.sub-category input[type="checkbox"]:checked').length;

                if (count > 0) {

                    $(this).parent().siblings('.parent-category').children().not(':checked').trigger('click');

                } else {

                    $(this).parent().siblings('.parent-category').children(':checked').trigger('click');

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

            <h1 class="m-0 font-weight-bold text-primary">新增商品</h1>

            @include('admin.includes.breadcrumb', [
                'data' => [
                    '商品清單' => route('admin.product.index'),
                    $product->name => route('admin.product.show', ['product' => $product->id]),
                    '修改'  => ''
                ]
            ])

        </div>

        <div class="card-body">

            {!! Form::open(['url' => route('admin.product.update', ['product' => $product->id]), 'method' => 'POST', 'id' => 'form-create']) !!}

                @method('PUT')

                <div class="row mb-3 ml-5">

                    <div class="col-7 p-3">

                        {!! Form::bsText('名稱', 'name', request()->old('name') ?? $product->name, 'vertical') !!}

                    </div>

                    <div class="col-7 p-3">

                        {!! Form::bsSelect('品牌', 'brand', request()->old('brand') ?? $product->brand_id, Brand::getSelectOptions(), '', 'vertical') !!}

                    </div>

                    {{-- category --}}
                    <div class="col-7 p-3">

                        <p style="margin:0 -0.75rem">種類 (可複選)</p>

                        <hr>

                        <div class="row mt-3" style="width: 100%;">

                            @foreach ($categoryList as $category)

                                <div class="col-3 mb-5">

                                    <label class="parent-category">

                                        <input type="checkbox" name='category[]' value="{{ $category->id }}" {{ in_array($category->id, request()->old('category') ?? $ownedCategoryList) ? 'checked' : '' }}>

                                        {{ $category->name }}

                                    </label>

                                    @if (!$category->subcategories->isEmpty())

                                        <hr style="margin: 0 0 5px 0">

                                        @foreach ($category->subcategories as $subcategory)

                                            <label class="ml-2 sub-category" style="width: 100%">

                                                <input type="checkbox" name='category[]' value="{{ $subcategory->id }}"  {{ in_array($subcategory->id, request()->old('category') ?? $ownedCategoryList) ? 'checked' : '' }}>

                                                {{ $subcategory->name }}

                                            </label>

                                        @endforeach

                                    @endif

                                </div>

                            @endforeach

                        </div>

                    </div>

                    <div class="col-7 p-3">

                        <div class="row">

                            <label>價格(原價)</label>

                            <input type="number" min="0" class="form-control" name="original_price" value="{{ request()->old('original_price') ?? $product->original_price }}">

                        </div>

                    </div>

                    <div class="col-7 p-3">

                        {!! Form::bsSelect('狀態', 'status', request()->old('status') ?? $product->status, Product::getStatusLabels(), '', 'vertical') !!}

                    </div>

                    <div class="col-7 p-3">

                        <div class="row" style="display: block">

                            <label>商品簡介</label>

                            <textarea name="description" style="visibility: hidden" value="{{ request()->old('description') ?? $product->description }}"></textarea>

                            <div id="description-editor" style="width: 100%; height: 500px"></div>

                        </div>

                    </div>

                    <div class="col-7 p-3">

                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#uploadImage">

                            上傳圖片

                        </button>

                        <button id="btn-delete-image" type="button" class="btn btn-danger">

                            刪除所選圖片

                        </button>

                        <button type="button" class="btn btn-warning btn-image-modal" data-type="first-image" data-toggle="modal" data-target="#imageModal">

                            設定封面圖片

                        </button>

                        <!-- Croppie Modal -->
                        <div class="modal fade" id="uploadImage" tabindex="-1" role="dialog">

                            <div class="modal-dialog modal-dialog-centered" role="document" style="width: 75vw; max-width: 75vw">

                                <div class="modal-content">

                                    <div class="modal-header">

                                        <h5 class="modal-title" id="exampleModalCenterTitle">上傳圖片</h5>

                                        <button type="button" class="close" data-dismiss="modal">

                                            <span aria-hidden="true">&times;</span>

                                        </button>

                                    </div>

                                    <div class="modal-body">

                                        <div class="container">

                                            <div class="row mb-2">

                                                <label>選擇圖片</label>

                                                <input type="file" name="image_url" accept="image/*">

                                                <button id="btn-croppie" type="button" class="btn button-small btn-success">檢視修改結果</button>

                                            </div>

                                            <div class="row">

                                                <div class="col-6">

                                                    <div id="image-for-croppie"></div>

                                                </div>

                                                <div class="col-6">

                                                    <div id="croppied-block"></div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                    <div class="modal-footer">

                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

                                        <button id="btn-upload-image" type="button" class="btn btn-primary">儲存圖片</button>

                                    </div>

                                </div>

                            </div>

                        </div>

                        {{-- Image Modal --}}
                        <div class="modal fade" id="imageModal" tabindex="-1" role="dialog">

                            <div class="modal-dialog modal-dialog-centered" role="document" style="width: 75vw; max-width: 75vw">

                                <div class="modal-content">

                                    <div class="modal-header">

                                        <h5 class="modal-title" id="exampleModalCenterTitle">圖片</h5>

                                        <button type="button" class="close btn-image-modal" data-dismiss="modal">

                                            <span aria-hidden="true">&times;</span>

                                        </button>

                                    </div>

                                    <div class="modal-body">

                                        <div class="container">

                                            <div id="image-modal-container" class="row" style="overflow-y: scroll; max-height: 300px"></div>

                                        </div>

                                    </div>

                                    <div class="modal-footer">

                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

                                        <button id="btn-selected-image" type="button" class="btn btn-primary">確定</button>

                                    </div>

                                </div>

                            </div>

                        </div>

                        {{-- 圖片展示 --}}
                        <div id="image-block" class="row" style="overflow-y: scroll; max-height: 300px">

                            @if (!$mergedImages->isEmpty())

                                @foreach ($mergedImages as $image)

                                    <div class="col-2 m-2">

                                        <label>

                                            <input type="checkbox" name="image[]" class="input-image-block" value="{{ $image->id }}">

                                            <img style="width:100%" src="{{ asset($image->url) }}">

                                            <input type="text" name="images_to_store[]" value="{{ $image->id }}" style="visibility: hidden; width: 0; height: 0">

                                        </label>

                                    </div>

                                @endforeach

                            @endif

                        </div>

                        {{-- first_image --}}
                        <input style="visibility: hidden; height: 0px" type="text" name="first_image" value="{{ request()->old('first_image') ?? $firstImageId }}">

                    </div>

                    <div class="col-7 p-3">

                        <hr>

                        <label>

                            <button type="button" id="create-stock" class="btn btn-info">新增商品規格</button>

                            <span class='text-danger'>(至少要一個)</span>

                            <button type="button" id="delete-stock" class="btn btn-danger">刪除商品規格</button>

                        </label>

                        <div id="stock-block"
                            style="width: 100%; max-height:400px; border:1px solid #ccc; border-radius: 5px; overflow-y: scroll">

                            @foreach (request()->old('stock') ?? $product->stocks()->with('image')->get()->toArray() as $index => $stock)

                                <label>

                                    <input type="checkbox" class="row-stock" value="{{ $stock['id'] }}">

                                    <div class="row m-2">

                                        <div class="col-3 row-stock-image" style="display: flex; flex-direction: row; justify-content: center">

                                            <img style="width: 75px" class="{{ sprintf('row-stock-image-%d', $stock['image_id']) }}" src="{{ $stock['image_for_stock_url'] ?? asset($stock['image']['url']) }}">

                                            <input type="text" class="image-for-stock-url {{ sprintf('row-stock-image-%d', $stock['image_id']) }}" name="stock[{{ $index }}][image_for_stock_url]" value="{{ $stock['image_for_stock_url'] ?? asset($stock['image']['url']) }}" style="visibility: hidden; height: 0px; width: 0px">

                                            <input type="text" class="image-for-stock {{ sprintf('row-stock-image-%d', $stock['image_id']) }}" name="stock[{{ $index }}][image_id]" value="{{ $stock['image_id'] ?? '' }}" style="visibility: hidden; height: 0px; width: 0px">

                                            <input type="number" class="stock-id" name="stock[{{ $index }}][id]" value="{{ $stock['id'] ?? 0 }}" style="visibility: hidden; height: 0px; width: 0px">

                                        </div>

                                        <div class="col-3 mt-2">

                                            <label>規格 ex:藍色 XL...</label>

                                            <input name="stock[{{ $index }}][attribute]" value="{{ $stock['attribute'] ?? '' }}" class="form-control">

                                        </div>

                                        <div class="col-2 mt-2">

                                            <label>庫存</label>

                                            <input name="stock[{{ $index }}][quantity]" value="{{ $stock['quantity'] ?? '' }}" class="form-control">

                                        </div>

                                        <div class="col-2 mt-2">

                                            <label>價格</label>

                                            <input name="stock[{{ $index }}][price]" value="{{ $stock['price'] ?? '' }}" class="form-control">

                                        </div>

                                        <div class="col-2" style="display: flex; flex-direction: column; justify-content: center">

                                            <button id="{{ sprintf('row-stock-%d', $index) }}" type="button" class="btn btn-primary btn-image-modal" data-type="stock-image" data-toggle="modal" data-target="#imageModal">設定圖片</button>

                                        </div>

                                    </div>

                                    <hr>

                                </label>

                            @endforeach

                        </div>

                    </div>

                    <div class="col-7 p-3">

                        {!! Form::button('修改', ['type' => 'Submit', 'class' => 'btn btn-primary mt-2']) !!}

                    </div>

                    <hr>

                </div>

            {!! Form::close() !!}

        </div>

    </div>

@endsection
