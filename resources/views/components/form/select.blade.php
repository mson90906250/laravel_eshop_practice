@if ($direction === 'horizontal')

    {{-- horizontal --}}
    <div class="row">

        <div class="col-4">

            <label for="{{ $name }}">{{ $label }}</label>

        </div>

        <div class="col-8">

            <select class="form-control" name="{{ $name }}" id="{{ $name }}">

                @if ($prompt)

                    <option value="">{{ $prompt }}</option>

                @endif

                @foreach ($options as $k => $v)

                    <option value="{{ $k }}" {{ $k == old($name) || $k == $value ? 'selected' : '' }} >{{ $v }}</option>

                @endforeach

            </select>

        </div>

    </div>


@else

    {{-- vertical --}}
    <div class="row">

        <label for="{{ $name }}">{{ $label }}</label>

        <select class="form-control" name="{{ $name }}" id="{{ $name }}">

            @if ($prompt)

                <option value="">{{ $prompt }}</option>

            @endif

            @foreach ($options as $k => $v)

                    <option value="{{ $k }}" {{ $k == old($name) || $k == $value ? 'selected' : '' }} >{{ $v }}</option>

            @endforeach

        </select>

    </div>



@endif
