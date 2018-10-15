@extends('layouts.app')
@section('content')
<div class="container column">
    <div class="columns m-t-5">
        <div class="column">
            <h1 class="title">Producten</h1>
        </div> <!-- end of column -->
        <div class="column">
        </div>
    </div>
    <hr class="m-t-0">
    <div class="columns">
        <div class="column">
            <div class="field">
                @foreach($products as $product)
                    <table style="width:80%">
                        <tr>
                            <th><strong>{{ $product->title }}</strong></th>
                        </tr>
                        <tr>
                            <td>Product id: <strong>{{ $product->id }}</strong></td>
                        </tr>
                        <tr>
                            <td colspan="2">{{ $product->description }}</td>
                            <td><a href="javascript:void(0)" class="button is-danger productDetail">Details</a></td>
                        </tr>
                        <tr class="hideDetail">
                            <td>
                                <ul>
                                    <li><strong>Variants:</strong></li>
                                </ul>
                                <hr class="hrVariant">
                                @foreach($product->variants as $variant)
                                    <ul>
                                        <li>Variant id: <strong>{{ $variant->id }}</strong></li>
                                        {{-- <li><img src="{{public_path('tshirtImages')}}/{{$variant->fileName}}" width="200"></li> --}}
                                        <li><img src="{{asset('tshirtImages/').'/'.$variant->fileName}}" width='100'></li>
                                        <li>Origin code: <strong>{{ $variant->origin->code }}</strong></li>
                                        <li>Prijs: <strong>{{ $variant->price }}</strong></li>
                                        <li>BTW: <strong>{{ $variant->tax }}</strong></li>
                                        <li>Totaal: <strong>{{ $variant->total }}</strong></li>
                                        <li>BTW tarief: <strong>{{ $variant->tax_rate }}</strong></li>
                                        <hr class="hrVariant">
                                        @foreach($variant->attributes as $attribute)
                                            <ul class="m-l-15">
                                                <li>{{ $attribute->name }}: <strong>{{ $attribute->value }}</strong></li>
                                            </ul>
                                        @endforeach
                                        <hr class="hrVariant">
                                        <ul class="m-l-30">
                                            <li>Media ID: <strong>{{ $variant->media_id }}</strong></li>
                                            <li>Back view: Composite media id: <strong>{{ $variant->views->back->composite_media_id }}</strong></li>
                                            @foreach($variant->views->back->customizations as $customization)
                                                <ul class="m-l-15">
                                                    <li>Customization:</li>
                                                    <ul class="m-l-15">
                                                        <li> Type: <strong>{{ $customization->type or 'Type not defined'}}</strong></li>
                                                        <li> production media id: <strong>{{ $customization->production_media_id or 'No media found'}}</strong></li>
                                                        <li> Preview media id: <strong>{{ $customization->preview_media_id or 'No media found'}}</strong></li>
                                                        <li> Width: <strong>{{ $customization->dimension->width or 'No width found'}}</strong></li>
                                                        <li> Height: <strong>{{ $customization->dimension->height or 'No height found'}}</strong></li>
                                                    </ul>
                                                </ul>
                                            @endforeach
                                            <li>Left view: Composite media id: <strong>{{ $variant->views->left->composite_media_id }}</strong></li>
                                            @foreach($variant->views->left->customizations as $customization)
                                                <ul class="m-l-15">
                                                    <li>Customization:</li>
                                                    <ul class="m-l-15">
                                                        <li> Type: <strong>{{ $customization->type or 'Type not defined'}}</strong></li>
                                                        <li> production media id: <strong>{{ $customization->production_media_id or 'No media found'}}</strong></li>
                                                        <li> Preview media id: <strong>{{ $customization->preview_media_id or 'No media found'}}</strong></li>
                                                        <li> Width: <strong>{{ $customization->dimension->width or 'No width found'}}</strong></li>
                                                        <li> Height: <strong>{{ $customization->dimension->height or 'No height found'}}</strong></li>
                                                    </ul>
                                                </ul>
                                            @endforeach
                                            <li>Front view: Composite media id: <strong>{{ $variant->views->front->composite_media_id }}</strong></li>
                                            @foreach($variant->views->front->customizations as $customization)
                                                <ul class="m-l-15">
                                                    <li>Customization:</li>
                                                    <ul class="m-l-15">
                                                        <li> Type: <strong>{{ $customization->type or 'Type not defined'}}</strong></li>
                                                        <li> production media id: <strong>{{ $customization->production_media_id or 'No media found'}}</strong></li>
                                                        <li> Preview media id: <strong>{{ $customization->preview_media_id or 'No media found'}}</strong></li>
                                                        <li> Width: <strong>{{ $customization->dimension->width or 'No width found'}}</strong></li>
                                                        <li> Height: <strong>{{ $customization->dimension->height or 'No height found'}}</strong></li>
                                                    </ul>
                                                </ul>
                                            @endforeach
                                            <li>Right view: Composite media id: <strong>{{ $variant->views->right->composite_media_id }}</strong></li>
                                            @foreach($variant->views->right->customizations as $customization)
                                                <ul class="m-l-15">
                                                    <li>Customization:</li>
                                                    <ul class="m-l-15">
                                                        <li> Type: <strong>{{ $customization->type or 'Type not defined'}}</strong></li>
                                                        <li> production media id: <strong>{{ $customization->production_media_id or 'No media found'}}</strong></li>
                                                        <li> Preview media id: <strong>{{ $customization->preview_media_id or 'No media found'}}</strong></li>
                                                        <li> Width: <strong>{{ $customization->dimension->width or 'No width found'}}</strong></li>
                                                        <li> Height: <strong>{{ $customization->dimension->height or 'No height found'}}</strong></li>
                                                    </ul>
                                                </ul>
                                            @endforeach
                                        </ul>
                                        <hr class="hrVariant">
                                        <li>Created at: <strong>{{ $variant->created_at }}</strong></li>
                                        <li>Updated at: <strong>{{ $variant->updated_at }}</strong></li>
                                    </ul>
                                    <hr class="detailSection">
                                @endforeach
                            </td>
                        </tr>
                    </table>
                    <hr>
                @endforeach
                <a href="{{ route('products.download') }}"  class="button is-danger downloadProducts">Download alle Smake producten</a>
            </div>
        </div>
    </div>
</div>
@endsection
