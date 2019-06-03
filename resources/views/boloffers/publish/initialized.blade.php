@extends('layouts.app')
@section('pageTitle', 'Verzoek tot publicatie op BOL-api gedaan voor:')
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
                <p>Geen custom varianten aanwezig met status 'publish_at_api_initialized'. </p>
            @endif
            @if( count($cvars) > 0 )



            <table class="table is-narrow">
                <thead>
                <tr>
                    <th>Image</th>

                    <th>EAN</th>
                    <th>Name</th>
                    <th>Size</th>

                    <th>Price</th>
                    <th>In Catalog</th>
                    <th>Del.Code</th>
                    {{-- <th>Stock</th> --}}
                    {{-- <th>Hold?</th> --}}
                    <th>Publish</th>

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



                            <td>
                                    {{ $cvar->salePrice }}
                            </td>

                            <td>
                                @if($cvar->isInBolCatalog)
                                In catalog
                                @else
                                Not in catalog
                                @endif
                            </td>

                            <td>
                                    @if($cvar->boldeliverycode)
                                    {{ $cvar->boldeliverycode }}
                                    @else
                                    Onbekend
                                    @endif

                            </td>

                            <td>
                                    {{ $cvar->isPublishedAtBol }}
                            </td>
                        </tr>

                    @endforeach
                </tbody>
            </table>
            <a class="button is-danger is-outlined" href="{{ route('boloffers.index') }}">Terug</a>


            @endif



        </div>
    </div>
</div>



@endsection

@section('scripts')
<script>


</script>
@endsection


