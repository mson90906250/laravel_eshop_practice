<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        @foreach($data as $title => $path)

            @if (!$loop->last)
                <li class="breadcrumb-item"><a href="{{ $path }}">{{ $title }}</a></li>
            @else
                <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
            @endif

        @endforeach
    </ol>
</nav>
