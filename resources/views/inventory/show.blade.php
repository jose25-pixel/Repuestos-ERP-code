<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $product->name }} | {{ $company->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700" rel="stylesheet">
    <style>
        :root { --ink: #172a43; --muted: #65758a; --line: #e3e9ef; --paper: #f6f8fb; --panel: #fff; --blue: #087ca7; --cyan: #00a9d4; --red: #df383f; --nav: #142b4c; }
        * { box-sizing: border-box; }
        body { background: var(--paper); color: var(--ink); font-family: Figtree, sans-serif; margin: 0; }
        a { color: inherit; text-decoration: none; }
        .header-inner, main { margin: auto; max-width: 1180px; padding-left: 28px; padding-right: 28px; }
        header { background: var(--panel); border-bottom: 1px solid var(--line); }
        .header-inner { align-items: center; display: flex; justify-content: space-between; min-height: 82px; }
        .brand { color: var(--nav); font-size: 23px; font-weight: 700; }
        .brand small { color: var(--red); display: block; font-size: 10px; margin-bottom: 5px; }
        .brand span { color: var(--cyan); }
        .back { color: var(--blue); font-weight: 700; }
        main { padding-bottom: 60px; padding-top: 28px; }
        .crumb { color: var(--muted); font-size: 14px; margin-bottom: 18px; }
        .detail { background: var(--panel); border: 1px solid var(--line); display: grid; gap: 38px; grid-template-columns: minmax(0, 1.08fr) minmax(340px, .92fr); padding: 30px; }
        .gallery-main { align-items: center; background: #f5f8fa; display: flex; height: 500px; justify-content: center; overflow: hidden; }
        .gallery-main img { height: 100%; object-fit: contain; width: 100%; }
        .placeholder { color: var(--cyan); font-size: 90px; font-weight: 700; }
        .thumbnails { display: flex; gap: 12px; margin-top: 14px; }
        .thumbnail { background: #f5f8fa; border: 2px solid transparent; cursor: pointer; height: 78px; padding: 3px; width: 88px; }
        .thumbnail.active { border-color: var(--cyan); }
        .thumbnail img { height: 100%; object-fit: contain; width: 100%; }
        .company { color: var(--muted); font-size: 13px; font-weight: 700; text-transform: uppercase; }
        h1 { color: var(--nav); font-size: 30px; line-height: 1.2; margin: 10px 0; }
        .sku { color: var(--muted); font-size: 14px; }
        .promotion { color: var(--red); font-size: 13px; font-weight: 700; margin: 28px 0 5px; text-transform: uppercase; }
        .price { color: var(--cyan); font-size: 36px; font-weight: 700; margin: 26px 0 4px; }
        .regular-price { color: var(--muted); margin: 0 0 18px; text-decoration: line-through; }
        .availability { color: #1d7b57; font-size: 14px; font-weight: 700; }
        .availability.out { color: var(--red); }
        .shipping { border-top: 1px solid var(--line); color: var(--muted); margin-top: 24px; padding-top: 18px; }
        .add-cart { background: #f58a1f; border: 0; color: #fff; cursor: pointer; font: inherit; font-weight: 700; margin-top: 22px; padding: 14px 20px; width: 100%; }
        .add-cart:disabled { background: #a6b0bc; cursor: not-allowed; }
        .description { background: var(--panel); border: 1px solid var(--line); margin-top: 24px; padding: 26px 30px; }
        .description h2 { color: var(--nav); font-size: 20px; margin: 0 0 12px; }
        .description p { color: #475b72; line-height: 1.65; margin: 0; white-space: pre-line; }
        @media (max-width: 760px) { .header-inner { padding-bottom: 18px; padding-top: 18px; } .detail { display: block; padding: 16px; } .gallery-main { height: 330px; } .product-info { margin-top: 28px; } h1 { font-size: 25px; } main { padding-left: 16px; padding-right: 16px; } }
    </style>
</head>
<body>
    <header>
        <div class="header-inner">
            <a class="brand" href="{{ route('inventory.index') }}">
                <small>{{ strtoupper($company->country ?? 'SV') }} · {{ strtoupper($company->plan ?? 'BÁSICO') }}</small>
                {{ $company->name }} <span>· Catálogo</span>
            </a>
            <a class="back" href="{{ route('inventory.index') }}">Volver al catálogo</a>
        </div>
    </header>
    <main>
        <p class="crumb"><a href="{{ route('inventory.index') }}">Inicio</a> / {{ $product->name }}</p>
        @php($images = collect($product->images ?? [])->filter()->values())
        @php($product->loadMissing('inventarios'))
        @php($quantity = (int) $product->inventarios->sum('cantidad_disponible'))
        @php($hasPromotion = $product->regular_price !== null && (float) $product->regular_price > (float) $product->price)
        <section class="detail">
            <div class="gallery">
                <div class="gallery-main">
                    @if ($images->isNotEmpty())
                        <img data-main-image src="{{ asset('storage/'.$images->first()) }}" alt="{{ $product->name }}">
                    @else
                        <span class="placeholder">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                    @endif
                </div>
                @if ($images->count() > 1)
                    <div class="thumbnails" aria-label="Imágenes del producto">
                        @foreach ($images as $image)
                            <button class="thumbnail {{ $loop->first ? 'active' : '' }}" type="button" data-thumbnail data-image-url="{{ asset('storage/'.$image) }}" aria-label="Ver imagen {{ $loop->iteration }}">
                                <img src="{{ asset('storage/'.$image) }}" alt="Vista {{ $loop->iteration }} de {{ $product->name }}">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="product-info">
                <span class="company">{{ $product->brand ?: ($product->company?->name ?? 'RepuestosERP') }}</span>
                <h1>{{ $product->name }}</h1>
                <span class="sku">Código: {{ $product->sku }} @if ($product->barcode) · {{ $product->barcode }} @endif</span>
                @if ($hasPromotion)
                    <p class="promotion">Promoción exclusiva en línea</p>
                @endif
                <p class="price">${{ number_format((float) $product->price, 2) }}</p>
                @if ($hasPromotion)
                    <p class="regular-price">Precio normal ${{ number_format((float) $product->regular_price, 2) }}</p>
                @endif
                <span class="availability {{ $quantity === 0 ? 'out' : '' }}">{{ $quantity === 0 ? 'Agotado' : 'Disponible' }}</span>
                <p class="shipping">Envío desde <strong>$5.00</strong><br><small>El costo final depende del departamento, cantidad y peso.</small></p>
                <button class="add-cart" type="button" data-add-cart data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->precio_venta_sugerido ?? $product->price }}" {{ $quantity === 0 ? 'disabled' : '' }}>Agregar al carrito</button>
            </div>
        </section>
        <section class="description">
            <h2>Descripción del producto</h2>
            <p>{{ $product->description ?: 'Consulta las características y disponibilidad de este producto con nuestra empresa.' }}</p>
        </section>
    </main>
    <script>
        const cartKey = 'repuestoserp-cart-company-{{ $company->id }}';
        const cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
        const save = () => localStorage.setItem(cartKey, JSON.stringify(cart));
        document.querySelectorAll('[data-thumbnail]').forEach(button => button.addEventListener('click', () => {
            document.querySelector('[data-main-image]').src = button.dataset.imageUrl;
            document.querySelectorAll('[data-thumbnail]').forEach(item => item.classList.remove('active'));
            button.classList.add('active');
        }));
        document.querySelector('[data-add-cart]')?.addEventListener('click', event => {
            const button = event.currentTarget;
            const item = cart.find(item => item.id === button.dataset.id);
            item ? item.quantity++ : cart.push({ id: button.dataset.id, name: button.dataset.name, price: button.dataset.price, quantity: 1 });
            save();
            button.textContent = 'Agregado al carrito';
            button.disabled = true;
            setTimeout(() => { button.textContent = 'Agregar al carrito'; button.disabled = false; }, 1000);
        });
    </script>
</body>
</html>
