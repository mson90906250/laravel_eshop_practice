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

            var oldDescription = String.raw`{!! request()->old("description") ?? json_encode('') !!}`;

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
            var count = 1 + {{ collect(request()->old('stock'))->keys()->max() ?? 0 }};

            $('#create-stock').on('click', function () {

                var row = '<label>'
                            +'<input type="checkbox" class="row-stock">'
                            +'<div class="row m-2">'
                                +'<div class="col-3 row-stock-image" style="display: flex; flex-direction: row; justify-content: center">'
                                    +'<img style="width: 75px" src="'+ noImageUrl +'">'
                                    +'<input class="image-for-stock-url" type="text" name="stock['+ count +'][image_for_stock_url]" style="visibility: hidden; height: 0px; width: 0px">'
                                    +'<input class="image-for-stock" type="text" name="stock['+ count +'][image_for_stock]" style="visibility: hidden; height: 0px; width: 0px">'
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

                $('input.row-stock:checked').parent().remove();

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

            //--勾選創建新品牌
            var textInput = '<input type="text" id="new_brand_name" name="new_brand_name" placeholder="請輸入要創建的品牌名稱" class="form-control">';

            var selectInput;

            $('#new_brand').on('change', function () {

                var input =$('#brand-input');

                if (this.checked) {

                    //以創建新品牌的input取代select
                    selectInput = input.html();

                    input.html(textInput);


                } else {

                    input.html(selectInput);

                }

            });
            //--

            //--勾選創建新種類
            var newCategoryNameHtml = '<label for="new_category_name">新類型名稱</label>' +
                                    '<input type="text" class="form-control" name="new_category_name" id="new_category_name" value="{{ old('new_category_name') }}">';

            var newCategoryParentIdHtml = '<label for="new_category_parent_id">主類型</label>' +
                                            '<select class="form-control" name="new_category_parent_id" id="new_category_parent_id">' +
                                                '<option value="">沒有主類型</option>' +
                                                @foreach (Category::getSelectOptions(TRUE) as $k => $v)
                                                    '<option value="{{ $k }}" {{ $k == old('new_category_parent_id') ? 'selected' : '' }} >{{ $v }}</option>' +
                                                @endforeach
                                            '</select>';

            var oldCategoryHtml;

            $('#new_category').on('change', function () {

                var input =$('#category-input');

                if (this.checked) {

                    //以創建新種類的input取代select
                    oldCategoryHtml = input.html();

                    input.html(newCategoryNameHtml + newCategoryParentIdHtml);

                } else {

                    input.html(oldCategoryHtml);

                }

            });
            //--

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
                    '新增商品' => ''
                ]
            ])

        </div>

        <div class="card-body">

            {!! Form::open(['url' => route('admin.product.store'), 'method' => 'POST', 'id' => 'form-create']) !!}

                <div class="row mb-3 ml-5">

                    <div class="col-7 p-3">

                        {!! Form::bsText('名稱', 'name', request()->old('name') ?? '', 'vertical') !!}

                    </div>

                    <div class="col-7 p-3">

                        <div class="row">

                            <label for="brand">

                                品牌

                                <label><input class="ml-3" type="checkbox" id="new_brand" name="new_brand" value=1>創建新品牌</label>

                            </label>

                            <div id="brand-input" style="width: 100%">

                                <select class="form-control" name="brand" id="brand">

                                    @foreach (Brand::getSelectOptions() as $k => $v)

                                            <option value="{{ $k }}" {{ $k == request()->old('brand') ? 'selected' : '' }} >{{ $v }}</option>

                                    @endforeach

                                </select>

                            </div>

                        </div>

                    </div>

                    {{-- category --}}
                    <div class="col-7 p-3">

                        <p style="margin:0 -0.75rem">

                            種類 (可複選)

                            <label><input class="ml-3" type="checkbox" id="new_category" name="new_category" value=1>創建新種類</label>

                        </p>

                        <hr>

                        <div id="category-input" style="width: 100%">

                            <div class="row mt-3" style="width: 100%;">

                                @foreach ($categoryList as $category)

                                    <div class="col-3 mb-5">

                                        <label class="parent-category">

                                            <input type="checkbox" name='category[]' value="{{ $category->id }}" {{ in_array($category->id, request()->old('category') ?? []) ? 'checked' : '' }}>

                                            {{ $category->name }}

                                        </label>

                                        @if (!$category->subcategories->isEmpty())

                                            <hr style="margin: 0 0 5px 0">

                                            @foreach ($category->subcategories as $subcategory)

                                                <label class="ml-2 sub-category" style="width: 100%">

                                                    <input type="checkbox" name='category[]' value="{{ $subcategory->id }}"  {{ in_array($subcategory->id, request()->old('category') ?? []) ? 'checked' : '' }}>

                                                    {{ $subcategory->name }}

                                                </label>

                                            @endforeach

                                        @endif

                                    </div>

                                @endforeach

                            </div>

                        </div>

                    </div>

                    <div class="col-7 p-3">

                        <div class="row">

                            <label>價格(原價)</label>

                            <input type="number" min="0" class="form-control" name="original_price" value="{{ request()->old('original_price') ?? '' }}">

                        </div>

                    </div>

                    <div class="col-7 p-3">

                        {!! Form::bsSelect('狀態', 'status', request()->old('status') ?? '', Product::getStatusLabels(), '', 'vertical') !!}

                    </div>

                    <div class="col-7 p-3">

                        <div class="row" style="display: block">

                            <label>商品簡介</label>

                            <textarea name="description" style="visibility: hidden" value="{{ request()->old('description') ?? '' }}"></textarea>

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

                            @if (!$cachedImages->isEmpty())

                                @foreach ($cachedImages as $cachedImage)

                                    <div class="col-2 m-2">

                                        <label>

                                            <input type="checkbox" name="image[]" class="input-image-block" value="{{ $cachedImage->id }}">

                                            <img style="width:100%" src="{{ asset($cachedImage->url) }}">

                                            <input type="text" name="images_to_store[]" value="{{ $cachedImage->id }}" style="visibility: hidden; width: 0; height: 0">

                                        </label>

                                    </div>

                                @endforeach

                            @endif

                        </div>

                        {{-- first_image --}}
                        <input style="visibility: hidden; height: 0px" type="text" name="first_image" value="{{ request()->old('first_image') ?? '' }}">

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

                            @if (request()->old('stock'))

                                @foreach (request()->old('stock') as $index => $stock)

                                    <label>

                                        <input type="checkbox" class="row-stock">

                                        <div class="row m-2">

                                            <div class="col-3 row-stock-image" style="display: flex; flex-direction: row; justify-content: center">

                                                <img style="width: 75px" class="{{ sprintf('row-stock-image-%d', $stock['image_for_stock']) }}" src="{{ $stock['image_for_stock_url'] ?? asset(config("custom.no_image_url")) }}">

                                                <input type="text" class="image-for-stock-url {{ sprintf('row-stock-image-%d', $stock['image_for_stock']) }}" name="stock[{{ $index }}][image_for_stock_url]" value="{{ $stock['image_for_stock_url'] ?? '' }}" style="visibility: hidden; height: 0px; width: 0px">

                                                <input type="text" class="image-for-stock {{ sprintf('row-stock-image-%d', $stock['image_for_stock']) }}" name="stock[{{ $index }}][image_for_stock]" value="{{ $stock['image_for_stock'] ?? '' }}" style="visibility: hidden; height: 0px; width: 0px">

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

                            @endif

                        </div>

                    </div>

                    <div class="col-7 p-3">

                        {!! Form::button('創建', ['type' => 'Submit', 'class' => 'btn btn-primary mt-2']) !!}

                    </div>

                    <hr>

                </div>

            {!! Form::close() !!}

        </div>

    </div>

@endsection
