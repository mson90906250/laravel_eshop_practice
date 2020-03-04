@section('scripts')

    @parent

    <script type="text/javascript">

        // 給予訊息視窗自動消失的動畫
        $(".alert").fadeTo(30000, 500).slideUp(500);

    </script>

@endsection

@if (session('status'))

    <div class="alert alert-success">

        {{ session('status') }}

    </div>

@endif

@if ($errors->any())

    <div class="alert alert-danger">

        <ul>

            @foreach ($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif

