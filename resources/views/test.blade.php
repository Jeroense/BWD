

@foreach($products as $product)
    <strong>Titel: </strong><p>{{ $product->title }}</p>
    <Strong>Omschrijving: </Strong><p>{{ $product->description }}</p>
@endforeach
