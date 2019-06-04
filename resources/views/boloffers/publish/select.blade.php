@extends('layouts.app')
@section('pageTitle', 'Publiceer offers op BOL')
@section('content')



<div class="container column is-10 pull-left">
    <div class="columns m-t-5">

    </div>
    <hr class="m-t-0">
    <div class="card">

        <div class="card-content">

                @if ($errors->any())
                    <div class="is-danger">
                        @foreach ($errors->all() as $error)
                            <a class="button is-danger m-t-6">{{ $error }}</a>
                        @endforeach
                    </div>
                @endif

            @if( count($cvars) == 0 )
                <p>Geen custom varianten aanwezig met status 'not published' voor publicatie op BOL. </p>
            @endif
            @if( count($cvars) > 0 )

            <form method="POST" action="{{ route('boloffers.publish.published') }}">
                @csrf

            <table class="table is-narrow">
                <thead>
                <tr>
                    <th>Image</th>
                    {{-- <th>offerId</th> --}}
                    <th>EAN</th>
                    <th>Name</th>
                    <th>Size</th>
                    {{-- <th>OnHold</th> --}}
                    <th>Price</th>
                    <th>Fulfil</th>
                    <th>Del.Code</th>
                    <th>Stock</th>
                    <th>Hold?</th>
                    <th>Publish</th>
                    {{-- <th>Corr.Stock</th> --}}

                    {{-- <th>Condition</th> --}}
                    {{-- <th>Updated</th> --}}
                    {{-- <th>Err-Code</th> --}}
                    {{-- <th>Err-Mssg</th> --}}
                    {{-- <th>Actions</th> --}}
                </tr>
                </thead>
                <tbody>
                    @foreach ($cvars as $cvar)


                        <tr class="message">
                            @if($cvar->fileName)
                            {{-- onderstaande werkt! --}}
                            {{-- <td><img class="customVariants" src="/customVariants/{{ $offer->fileName }}" alt="Geen image!" width="100"> </td> --}}

                            {{-- hieronder werkt ook! Maar er mogen  geen spaties in  scr="" staan, in: }}/{{ --}}
                            {{-- dus src=" {{ url('customVariants') }} / {{ $offer->fileName }}" werkt niet,  door de spaties in }} / {{ !! --}}
                            <td><img  src="{{ url('customVariants') }}/{{ $cvar->fileName }}" alt="Geen image!" width="140"> </td>

                            @else
                            <td> geen image! </td>
                            @endif

                            <td>{{ $cvar->ean }} </td>

                            @if($cvar->variantName)
                            <td>{{ $cvar->variantName }} </td>
                            @else
                            <td> onbekend! </td>
                            @endif

                            <td>{{ $cvar->size }} </td>

                            {{-- <td>{{ $cvar->onHoldByRetailer }}</td> --}}

                            <td>
                                <div class="field">
                                    <div class="control">
                                        <input type="number" name="salePrice_{{$cvar->ean}}" class="input is-primary"
                                        @isset($cvar->salePrice)
                                        value="{{$cvar->salePrice}}"
                                        @endisset
                                        step="0.01" required>
                                    </div>
                                </div>
                            </td>

                            <td>FBR</td>



                            <td>
                                    <div class="field">
                                        <div class="control">
                                            <div class="select is-primary">
                                                <select name="deliveryCode_{{$cvar->ean}}">
                                                <option selected>3-5d</option>
                                                <option>1-2d</option>
                                                <option>2-3d</option>
                                                <option>4-8d</option>
                                                <option>1-8d</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                            </td>



                            <input type="hidden" name="ean_{{$cvar->ean}}" value="{{$cvar->ean}}">
                            <input type="hidden" name="size_{{$cvar->ean}}" value="{{$cvar->size}}">

                            <td>
                                <div class="field">
                                    <div class="control">
                                        <input name="stockfor_{{$cvar->ean}}" class="input is-primary" type="number" placeholder="Stock" style="width: 30;" value="{{ old('stockfor_' . $cvar->ean)}}">
                                    </div>

                                </div>

                            </td>

                            <td>
                                <label class="checkbox">
                                    <input name="onhold_{{$cvar->ean}}" type="checkbox"
                                    @if( (old('onhold_' . $cvar->ean)) ) checked @endif>
                                            Hold?
                                </label>
                            </td>

                            <td>
                                <label class="checkbox">
                                        <input name="publish_{{$cvar->ean}}" type="checkbox"
                                        @if( (old('publish_' . $cvar->ean)) ) checked @endif>
                                        Publish on BOL.
                                </label>
                            </td>


                        <input type="hidden" name="variantName_{{$cvar->ean}}" value="{{$cvar->variantName}}">
                        <input type="hidden" name="baseColor_{{$cvar->ean}}" value="{{$cvar->baseColor}}">

                        </tr>

                    @endforeach
                </tbody>
            </table>
            <button class="button is-danger is-outlined" type="submit" onclick="return confirm('customvarianten publiceren?')">Publish</button>
            </form>

            @endif

            @if ($errors->any())
                <div class="is-danger">
                    @foreach ($errors->all() as $error)
                        <a class="button is-danger m-t-6">{{ $error }}</a>
                    @endforeach
                </div>
             @endif

        </div>
    </div>
</div>



@endsection

{{-- @section('scripts')
<script>


</script>
@endsection --}}





