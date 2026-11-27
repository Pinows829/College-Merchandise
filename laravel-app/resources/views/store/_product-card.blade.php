<article class="card product-card">
    <a class="product-visual" href="{{ route('products.show', $product) }}">
        <img src="{{ asset('assets/merch-'.strtolower($product->category).'.jpg') }}" alt="" loading="lazy">
        <span class="product-category">{{ $product->category }}</span>
        <span class="product-college">{{ $product->college->code }} COLLECTION</span>
    </a>
    <div class="product-info">
        <small>{{ $product->college->code }}@if($product->course) · {{ $product->course->name }}@endif · {{ $product->category }}</small>
        <b>{{ $product->name }}</b>
        <div class="product-bottom"><strong>₱{{ number_format((float)$product->price, 2) }}</strong><a class="round-link" aria-label="View {{ $product->name }}" href="{{ route('products.show', $product) }}">↗</a></div>
    </div>
</article>
