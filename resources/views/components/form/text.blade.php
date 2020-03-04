@if ($direction === 'horizontal')

    {{-- horizontal --}}
    <div class="row">

        <div class="col-4">

            <label for="{{ $name }}">{{ $label }}</label>

        </div>

        <div class="col-8">

            <input type="text" class="form-control" name="{{ $name }}" id="{{ $name }}" value="{{ old($name) ?? $value }}">

        </div>

    </div>


@else

    {{-- vertical --}}
    <div class="row">

        <label for="{{ $name }}">{{ $label }}</label>

        <input type="text" class="form-control" name="{{ $name }}" id="{{ $name }}" value="{{ old($name) ?? $value }}">

    </div>



@endif
