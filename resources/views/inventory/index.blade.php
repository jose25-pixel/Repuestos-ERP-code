<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RepuestosERP | Repuestos</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700" rel="stylesheet">
    <style>
        :root { --ink: #172a43; --muted: #65758a; --line: #e3e9ef; --paper: #f6f8fb; --panel: #fff; --blue: #087ca7; --cyan: #00a9d4; --red: #df383f; --nav: #142b4c; }
        * { box-sizing: border-box; }
        body { margin: 0; color: var(--ink); background: var(--paper); font-family: Figtree, sans-serif; }
        a { color: inherit; text-decoration: none; }
        .utility, .header-inner, .menu-inner, .catalog { margin: auto; max-width: 1440px; padding-left: 28px; padding-right: 28px; }
        .utility { align-items: center; color: var(--muted); display: flex; font-size: 13px; justify-content: flex-end; min-height: 34px; }
        header { background: var(--panel); border-bottom: 1px solid var(--line); }
        .header-inner { align-items: center; display: grid; gap: 34px; grid-template-columns: 190px minmax(320px, 1fr) auto auto; min-height: 102px; }
        .brand { color: var(--nav); font-size: 23px; font-weight: 700; line-height: 1; }
        .brand small { color: var(--red); display: block; font-size: 10px; font-weight: 700; margin-bottom: 6px; }
        .brand span { color: var(--cyan); }
        .search { display: flex; height: 48px; }
        .search input { border: 1px solid var(--line); border-right: 0; color: var(--ink); flex: 1; font: inherit; font-size: 16px; min-width: 0; padding: 0 16px; }
        .search button { background: var(--cyan); border: 0; color: #fff; cursor: pointer; font: inherit; font-size: 15px; font-weight: 700; padding: 0 24px; }
        .account { border-left: 1px solid var(--line); color: var(--nav); font-size: 15px; font-weight: 700; padding-left: 28px; white-space: nowrap; }
        .cart-trigger { background: transparent; border: 0; color: var(--nav); cursor: pointer; font: inherit; font-size: 15px; font-weight: 700; padding: 0; white-space: nowrap; }
        .cart-badge { align-items: center; background: var(--cyan); border-radius: 50%; color: #fff; display: inline-flex; font-size: 11px; height: 21px; justify-content: center; margin-left: 5px; width: 21px; }
        .menu { background: #fff; border-bottom: 1px solid var(--line); }
        .menu-inner { display: flex; gap: 36px; overflow-x: auto; }
        .menu a { color: #44576f; font-size: 16px; padding: 18px 0; white-space: nowrap; }
        .menu a:first-child { color: var(--blue); font-weight: 700; }
        .catalog { display: grid; gap: 28px; grid-template-columns: 260px minmax(0, 1fr); padding-bottom: 50px; padding-top: 30px; }
        aside { background: #fff; border: 1px solid var(--line); height: fit-content; padding: 20px; }
        aside h2 { font-size: 17px; margin: 0 0 15px; }
        .category { border-top: 1px solid var(--line); color: #475b72; display: flex; font-size: 15px; gap: 10px; justify-content: space-between; padding: 13px 0; }
        .category.active, .category:hover { color: var(--cyan); font-weight: 700; }
        .catalog-head { align-items: center; display: flex; justify-content: space-between; margin-bottom: 20px; }
        h1 { font-size: 25px; margin: 0; }
        .count { color: var(--muted); margin: 6px 0 0; }
        .sort { background: #fff; border: 1px solid var(--line); color: var(--muted); font: inherit; padding: 9px 12px; }
        .grid { background: #fff; border: 1px solid var(--line); display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .product { border-bottom: 1px solid var(--line); border-right: 1px solid var(--line); cursor: pointer; display: flex; flex-direction: column; min-height: 390px; padding: 18px; position: relative; }
        .product:focus-visible { outline: 3px solid var(--cyan); outline-offset: -3px; }
        .product:nth-child(4n) { border-right: 0; }
        .product-image { align-items: center; background: #f5f8fa; display: flex; height: 180px; justify-content: center; margin: -18px -18px 20px; overflow: hidden; }
        .product-image img { height: 100%; object-fit: cover; width: 100%; }
        .wishlist { align-items: center; background: #fff; border: 1px solid var(--line); border-radius: 50%; color: var(--nav); cursor: pointer; display: flex; font-size: 22px; height: 36px; justify-content: center; position: absolute; right: 14px; top: 14px; width: 36px; z-index: 1; }
        .wishlist.saved { color: var(--red); }
        .saved-filter { background: #fff; border: 1px solid var(--line); color: var(--nav); cursor: pointer; font: inherit; font-size: 13px; font-weight: 700; margin-right: 10px; padding: 9px 12px; }
        .saved-filter.active { border-color: var(--cyan); color: var(--cyan); }
        .placeholder { color: var(--cyan); font-size: 48px; font-weight: 700; }
        .company { color: var(--muted); font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .product h2 { color: var(--nav); font-size: 17px; line-height: 1.35; margin: 9px 0 8px; }
        .sku { color: var(--muted); font-size: 13px; }
        .promotion { color: var(--red); font-size: 12px; font-weight: 700; margin: auto 0 5px; padding-top: 20px; text-transform: uppercase; }
        .price { color: var(--cyan); font-size: 25px; font-weight: 700; margin: 0 0 4px; }
        .regular-price { color: var(--muted); font-size: 13px; margin: 0 0 12px; text-decoration: line-through; }
        .shipping-note { color: var(--muted); font-size: 12px; margin: 7px 0 0; }
        .availability { color: #1d7b57; font-size: 13px; font-weight: 700; }
        .availability.out { color: var(--red); }
        .add-cart { background: var(--nav); border: 0; color: #fff; cursor: pointer; font: inherit; font-size: 13px; font-weight: 700; margin-top: 14px; padding: 10px; }
        .add-cart:disabled { background: #a6b0bc; cursor: not-allowed; }
        .empty { background: #fff; border: 1px solid var(--line); color: var(--muted); padding: 42px; text-align: center; }
        .cart-panel { background: #fff; box-shadow: -8px 0 28px rgba(20, 43, 76, .18); display: flex; flex-direction: column; height: 100vh; max-width: 430px; padding: 24px; position: fixed; right: 0; top: 0; transform: translateX(105%); transition: transform .2s ease; width: 100%; z-index: 10; }
        .cart-panel.open { transform: translateX(0); }
        .cart-head, .cart-total { align-items: center; display: flex; justify-content: space-between; }
        .cart-head h2 { font-size: 21px; margin: 0; }.close-cart { background: none; border: 0; color: var(--muted); cursor: pointer; font-size: 24px; }
        .cart-items { flex: 1; margin: 20px 0; overflow: auto; }.cart-item { border-top: 1px solid var(--line); display: grid; gap: 8px; grid-template-columns: 1fr auto; padding: 15px 0; }.cart-item strong { font-size: 15px; }.cart-item small { color: var(--muted); }.cart-item button { background: none; border: 0; color: var(--red); cursor: pointer; font: inherit; grid-column: 2; grid-row: 1; }.cart-empty { color: var(--muted); padding: 28px 0; text-align: center; }
        .cart-total { border-top: 2px solid var(--nav); font-size: 18px; padding: 18px 0; }.checkout { background: var(--cyan); border: 0; color: #fff; cursor: pointer; font: inherit; font-weight: 700; padding: 14px; width: 100%; }.checkout:disabled { background: #a6b0bc; cursor: not-allowed; }
        .checkout-modal { align-items: center; background: rgba(20, 43, 76, .58); display: none; inset: 0; justify-content: center; padding: 20px; position: fixed; z-index: 20; }.checkout-modal.open { display: flex; }.checkout-box { background: #fff; max-width: 550px; padding: 28px; width: 100%; }.checkout-box h2 { margin: 0 0 8px; }.checkout-box p { color: var(--muted); margin: 0 0 22px; }.payment-options { display: grid; gap: 12px; }.payment { align-items: center; border: 1px solid var(--line); cursor: pointer; display: flex; gap: 12px; padding: 15px; }.payment:has(input:checked) { border-color: var(--cyan); box-shadow: inset 3px 0 var(--cyan); }.payment strong, .payment span { display: block; }.payment span { color: var(--muted); font-size: 13px; margin-top: 3px; }.pay-button { background: var(--nav); border: 0; color: #fff; cursor: pointer; font: inherit; font-weight: 700; margin-top: 20px; padding: 14px; width: 100%; }
        .customer-fields { display: grid; gap: 10px; margin: 16px 0; }.customer-fields input { border: 1px solid var(--line); font: inherit; padding: 11px; width: 100%; }.checkout-feedback { color: var(--red); font-size: 14px; margin-top: 12px; }.checkout-feedback.success { color: #1d7b57; }
        .auth-modal { align-items: center; background: rgba(20, 43, 76, .58); display: none; inset: 0; justify-content: center; padding: 20px; position: fixed; z-index: 25; }.auth-modal.open { display: flex; }.auth-box { background: #fff; max-width: 560px; padding: 26px 28px 20px; width: 100%; }.auth-box h2 { margin: 0 0 12px; }.auth-box .auth-tabs { border-bottom: 1px solid var(--line); margin-bottom: 20px; }.auth-box .auth-tab { align-items: center; display: flex; gap: 10px; font-size: 26px; font-weight: 700; margin-bottom: 16px; }.auth-box .auth-icon { align-items: center; background: #eff7fb; border-radius: 50%; color: var(--blue); display: inline-flex; font-size: 20px; height: 34px; justify-content: center; width: 34px; }.auth-form { display: grid; gap: 14px; }.auth-form input { border: 1px solid var(--line); font: inherit; padding: 12px 14px; width: 100%; }.auth-submit, .auth-google { border: 1px solid var(--line); cursor: pointer; font: inherit; font-weight: 700; padding: 14px 18px; width: 100%; }.auth-submit { background: var(--nav); border-color: var(--nav); color: #fff; }.auth-google { background: #fff; color: var(--nav); margin-top: 12px; }.auth-link { color: var(--cyan); cursor: pointer; font-weight: 700; }
        @media (max-width: 1050px) { .grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } .product:nth-child(4n) { border-right: 1px solid var(--line); } .product:nth-child(3n) { border-right: 0; } }
        @media (max-width: 760px) { .utility { display: none; } .header-inner { gap: 18px; grid-template-columns: 1fr auto; min-height: auto; padding-bottom: 16px; padding-top: 16px; } .search { grid-column: 1 / -1; grid-row: 2; } .account { border: 0; padding: 0; } .cart-trigger { grid-column: 2; grid-row: 1; }.menu-inner { gap: 23px; } .catalog { display: block; padding-left: 16px; padding-right: 16px; } aside { margin-bottom: 24px; } .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .product { min-height: 340px; padding: 13px; } .product-image { height: 130px; margin: -13px -13px 14px; } .product h2 { font-size: 15px; } .price { font-size: 20px; } .catalog-head { align-items: flex-start; gap: 12px; } .sort { font-size: 12px; } }
    </style>
</head>
<body>
    <header>
        <div class="utility">Atención a clientes · Venta de repuestos para línea blanca</div>
        <div class="header-inner">
            <a class="brand" href="{{ route('inventory.index') }}">
                @php($companyBrand = $company ?? \App\Models\Company::query()->where('is_active', true)->latest('id')->first())
                @if ($companyBrand && $companyBrand->logo_path)
                    <img src="{{ asset('storage/' . $companyBrand->logo_path) }}" alt="{{ $companyBrand->name }}" style="width: 64px; height: 64px; object-fit: contain; border-radius: 50%; border: 2px solid #e3e9ef; background: white; margin-right: 12px; vertical-align: middle;">
                @else
                    <span style="display:inline-flex; align-items:center; justify-content:center; width:64px; height:64px; border-radius:50%; background:#ecf8ff; color:#087ca7; font-weight:700; border:2px solid #cfeaf5; margin-right:12px; vertical-align:middle;">{{ strtoupper(substr(($companyBrand->name ?? 'RERP'), 0, 1)) }}</span>
                @endif
                <span style="display:inline-block; vertical-align:middle;">
                    <small>{{ strtoupper($companyBrand?->country ?? 'MX') }} · {{ strtoupper($companyBrand?->plan ?? 'BÁSICO') }}</small>
                    {{ $companyBrand?->name ?? 'RepuestosERP' }}
                </span>
            </a>
            <form class="search" action="{{ route('inventory.index') }}" method="GET"><input name="search" type="search" value="{{ $search }}" placeholder="Buscar por nombre o SKU"><button type="submit">Buscar</button></form>
            <button class="account" type="button" data-open-auth>Mi cuenta</button>
            <button class="cart-trigger" type="button" data-open-cart>Carrito <span class="cart-badge" data-cart-count>0</span></button>
        </div>
    </header>
    <nav class="menu"><div class="menu-inner"><a href="{{ route('inventory.index') }}">Inicio</a><a href="#catalogo">Electrodomésticos y Línea Blanca</a><a href="#catalogo">Refacciones</a><a href="#sucursales">Sucursales</a><a href="/admin/login">Administración</a></div></nav>
    <main class="catalog" id="catalogo">
        <aside>
            <h2>Categorías</h2>
            <a class="category {{ $selectedCategoryId === 0 ? 'active' : '' }}" href="{{ route('inventory.index', ['search' => $search]) }}"><span>Todos los repuestos</span><span>{{ $products->count() }}</span></a>
            @foreach ($categories as $category)
                <a class="category {{ $selectedCategoryId === $category->id ? 'active' : '' }}" href="{{ route('inventory.index', ['category' => $category->id, 'search' => $search]) }}"><span>{{ $category->name }}</span><span>{{ $category->products_count }}</span></a>
            @endforeach
        </aside>
        <section>
            <div class="catalog-head"><div><h1>Refacciones</h1><p class="count">Mostrando {{ $products->count() }} productos · Envío desde $5.00</p></div><div><button class="saved-filter" type="button" data-saved-filter>Guardados (<span data-saved-count>0</span>)</button><select class="sort" aria-label="Ordenar productos"><option>Más recientes</option><option>Menor precio</option><option>Mayor precio</option></select></div></div>
            @if ($products->isEmpty())
                <div class="empty">Aún no hay productos publicados con estos criterios.</div>
            @else
                <div class="grid">
                    @foreach ($products as $product)
                        @php($quantity = (int) ($product->inventarios_sum_cantidad_disponible ?? $product->inventarios->sum('cantidad_disponible')))
                        @php($hasPromotion = $product->regular_price !== null && (float) $product->regular_price > (float) $product->price)
                        <article class="product" data-product-card data-product-id="{{ $product->id }}" data-detail-url="{{ route('inventory.show', $product) }}" tabindex="0" role="link" aria-label="Ver detalles de {{ $product->name }}">
                            <button class="wishlist" type="button" aria-label="Guardar {{ $product->name }}" data-wishlist data-product-id="{{ $product->id }}">&#9825;</button>
                            <div class="product-image">
                                @if (! empty($product->images))
                                    <img src="{{ asset('storage/'.$product->images[0]) }}" alt="{{ $product->name }}">
                                @else
                                    <span class="placeholder">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <span class="company">{{ $product->brand ?: ($product->company?->name ?? 'RepuestosERP') }}</span>
                            <h2>{{ $product->name }}</h2>
                            <span class="sku">SKU: {{ $product->sku }}</span>
                            @if ($hasPromotion)
                                <span class="promotion">Promoción</span>
                            @endif
                            <p class="price">${{ number_format((float) $product->price, 2) }}</p>
                            @if ($hasPromotion)
                                <p class="regular-price">Normal ${{ number_format((float) $product->regular_price, 2) }}</p>
                            @endif
                            <span class="availability {{ $quantity === 0 ? 'out' : '' }}">{{ $quantity === 0 ? 'Agotado' : 'Disponible' }}</span>
                            <p class="shipping-note">Envío desde $5.00</p>
                            <button class="add-cart" type="button" data-add-cart data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->precio_venta_sugerido ?? $product->price }}" {{ $quantity === 0 ? 'disabled' : '' }}>Agregar al carrito</button>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </main>
    <aside class="cart-panel" aria-label="Carrito de compras" data-cart-panel>
        <div class="cart-head"><h2>Tu carrito</h2><button class="close-cart" type="button" aria-label="Cerrar carrito" data-close-cart>&times;</button></div>
        <div class="cart-items" data-cart-items></div>
        <div class="cart-total"><strong>Total</strong><strong data-cart-total>$0.00</strong></div>
        <button class="checkout" type="button" data-checkout disabled>Continuar al pago</button>
    </aside>
    <div class="checkout-modal" data-checkout-modal>
        <form class="checkout-box" data-payment-form>
            <div class="cart-head"><h2>Forma de pago</h2><button class="close-cart" type="button" aria-label="Cerrar pago" data-close-checkout>&times;</button></div>
            <p>Ingresa tus datos y selecciona cómo deseas pagar.</p>
            <div class="customer-fields"><input name="name" required placeholder="Nombre completo"><input name="email" type="email" required placeholder="Correo electrónico"><input name="phone" required placeholder="Teléfono"><input name="country" required placeholder="País" value="El Salvador"><input name="department" required placeholder="Departamento"><input name="municipality" required placeholder="Municipio"><input name="address" required placeholder="Dirección exacta"><input name="address_reference" placeholder="Referencia de entrega (opcional)"></div>
            <div class="payment-options"><label class="payment"><input type="radio" name="payment" value="card" checked><span><strong>Tarjeta Visa / Mastercard</strong><span>Paga con tarjeta en línea</span></span></label><label class="payment"><input type="radio" name="payment" value="paypal"><span><strong>PayPal</strong><span>Pago por cuenta PayPal</span></span></label><label class="payment"><input type="radio" name="payment" value="qr_transfer"><span><strong>QR / Transferencia</strong><span>Paga con código QR o transferencia bancaria</span></span></label><label class="payment"><input type="radio" name="payment" value="cash_on_delivery"><span><strong>Contra entrega</strong><span>Paga al recibir el pedido</span></span></label><label class="payment"><input type="radio" name="payment" value="lightning"><span><strong>Bitcoin Lightning</strong><span>Paga el equivalente actual en BTC</span></span></label></div>
            <div data-bitcoin-quote hidden style="margin-top: 12px; border:1px solid var(--line); padding:12px; background:#fff8e8;"><strong>Monto real a pagar en Bitcoin</strong><div data-bitcoin-value style="margin-top:6px;">Consultando cotización...</div><small data-bitcoin-updated style="color:var(--muted);"></small></div>
            <div style="margin-top: 18px; border:1px solid var(--line); padding:12px; background:#f8fbff;">
                <strong>Envío</strong>
                <div style="margin-top:8px; color:var(--muted);">La Unión, Morazán, Ahuachapán, Cabañas y Santa Ana: $6.00 USD</div>
                <div style="margin-top:6px; color:var(--muted);">Resto de departamentos: $5.00 USD</div>
                <div style="margin-top:6px; color:var(--muted);">+ $1 si hay más de 5 unidades, + $2 si hay más de 15, + $2 si es pesado (>10 kg).</div>
            </div>
            <p class="checkout-feedback" data-checkout-feedback hidden></p><button class="pay-button" type="submit">Crear orden pendiente</button>
        </form>
    </div>
    <div class="auth-modal" data-auth-modal>
        <div class="auth-box">
            <button class="close-cart" type="button" aria-label="Cerrar sesión" data-close-auth>&times;</button>
            <h2>Iniciar sesión</h2>
            <div class="auth-tabs">
                <div class="auth-tab"><span class="auth-icon">◔</span> Tengo cuenta</div>
            </div>
            <form class="auth-form">
                <input type="email" name="auth_email" placeholder="Correo electrónico" required>
                <input type="password" name="auth_password" placeholder="Contraseña" required>
                <button class="auth-submit" type="submit">Siguiente</button>
                <button class="auth-google" type="button">Ingresar con Google</button>
                <p style="margin:0; color: var(--muted); text-align:center;">¿No tienes una cuenta? <span class="auth-link">Crear cuenta</span></p>
            </form>
        </div>
    </div>
    <script>
        const cartKey = 'repuestoserp-cart-company-{{ $company?->id ?? 0 }}';
        const wishlistKey = 'repuestoserp-wishlist';
        const cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
        const wishlist = new Set(JSON.parse(localStorage.getItem(wishlistKey) || '[]').map(String));
        const panel = document.querySelector('[data-cart-panel]');
        const modal = document.querySelector('[data-checkout-modal]');
        const format = value => `$${Number(value).toFixed(2)}`;
        const escapeHtml = value => String(value).replace(/[&<>'"]/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character]);
        const save = () => localStorage.setItem(cartKey, JSON.stringify(cart));
        const saveWishlist = () => localStorage.setItem(wishlistKey, JSON.stringify([...wishlist]));
        const getShippingCost = () => {
            const form = document.querySelector('[data-payment-form]');
            const department = (form?.department.value || '').trim().toLowerCase();
            const totalItems = cart.reduce((sum, item) => sum + Number(item.quantity), 0);
            if (! cart.length) return 0;
            return (['la_union', 'morazan', 'ahuachapan', 'cabanas', 'santa_ana'].includes(department) ? 6 : 5) + (totalItems > 5 ? 1 : 0) + (totalItems > 15 ? 2 : 0);
        };
        const updateBitcoinQuote = async () => {
            const quote = document.querySelector('[data-bitcoin-quote]');
            if (! quote) return;
            if (document.querySelector('input[name="payment"]:checked')?.value !== 'lightning') { quote.hidden = true; return; }
            quote.hidden = false;
            const amount = cart.reduce((sum, item) => sum + Number(item.price) * item.quantity, 0) + getShippingCost();
            document.querySelector('[data-bitcoin-value]').textContent = 'Consultando cotización...';
            try {
                const response = await fetch(`{{ route('bitcoin.quote') }}?amount=${encodeURIComponent(amount)}`, { headers: { Accept: 'application/json' } });
                const data = await response.json();
                if (! response.ok) throw new Error(data.message || 'Cotización no disponible.');
                document.querySelector('[data-bitcoin-value]').textContent = `${data.satoshis.toLocaleString('en-US')} sats (${data.bitcoin_amount.toFixed(8)} BTC) · Total USD: $${data.usd.toFixed(2)}`;
                document.querySelector('[data-bitcoin-updated]').textContent = `1 BTC = $${Number(data.bitcoin_usd).toLocaleString('en-US', { minimumFractionDigits: 2 })} · Actualizado ${new Date(data.updated_at).toLocaleTimeString()}`;
            } catch (error) {
                document.querySelector('[data-bitcoin-value]').textContent = error.message;
                document.querySelector('[data-bitcoin-updated]').textContent = '';
            }
        };
        const renderWishlist = () => {
            document.querySelector('[data-saved-count]').textContent = wishlist.size;
            document.querySelectorAll('[data-wishlist]').forEach(button => {
                const saved = wishlist.has(button.dataset.productId);
                button.classList.toggle('saved', saved);
                button.innerHTML = saved ? '&#9829;' : '&#9825;';
                button.setAttribute('aria-label', saved ? 'Quitar de guardados' : 'Guardar producto');
            });
        };
        const render = () => {
            const items = document.querySelector('[data-cart-items]');
            const total = cart.reduce((sum, item) => sum + Number(item.price) * item.quantity, 0);
            document.querySelector('[data-cart-count]').textContent = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.querySelector('[data-cart-total]').textContent = format(total);
            document.querySelector('[data-checkout]').disabled = cart.length === 0;
            items.innerHTML = cart.length ? cart.map(item => `<div class="cart-item"><div><strong>${escapeHtml(item.name)}</strong><small>${item.quantity} x ${format(item.price)}</small></div><button type="button" data-remove="${item.id}">Quitar</button></div>`).join('') : '<p class="cart-empty">Tu carrito está vacío.</p>';
            updateBitcoinQuote();
        };
        document.querySelectorAll('[data-add-cart]').forEach(button => button.addEventListener('click', () => {
            const item = cart.find(item => item.id === button.dataset.id);
            item ? item.quantity++ : cart.push({ id: button.dataset.id, name: button.dataset.name, price: button.dataset.price, quantity: 1 });
            save(); render(); panel.classList.add('open');
        }));
        document.querySelectorAll('[data-product-card]').forEach(card => card.addEventListener('click', event => {
            if (event.target.closest('button, a, select, input')) return;
            window.location.href = card.dataset.detailUrl;
        }));
        document.querySelectorAll('[data-product-card]').forEach(card => card.addEventListener('keydown', event => {
            if ((event.key === 'Enter' || event.key === ' ') && event.target === card) {
                event.preventDefault();
                window.location.href = card.dataset.detailUrl;
            }
        }));
        document.querySelectorAll('[data-wishlist]').forEach(button => button.addEventListener('click', () => {
            wishlist.has(button.dataset.productId) ? wishlist.delete(button.dataset.productId) : wishlist.add(button.dataset.productId);
            saveWishlist(); renderWishlist();
            const filter = document.querySelector('[data-saved-filter]');
            if (filter.classList.contains('active')) {
                button.closest('[data-product-card]').hidden = !wishlist.has(button.dataset.productId);
            }
        }));
        document.querySelector('[data-saved-filter]').addEventListener('click', event => {
            const active = event.currentTarget.classList.toggle('active');
            document.querySelectorAll('[data-product-card]').forEach(card => { card.hidden = active && !wishlist.has(card.dataset.productId); });
        });
        document.querySelector('[data-cart-items]').addEventListener('click', event => { if (event.target.dataset.remove) { cart.splice(cart.findIndex(item => item.id === event.target.dataset.remove), 1); save(); render(); } });
        const authModal = document.querySelector('[data-auth-modal]');
        document.querySelector('[data-open-cart]').addEventListener('click', () => panel.classList.add('open'));
        document.querySelector('[data-open-auth]').addEventListener('click', () => authModal.classList.add('open'));
        document.querySelector('[data-close-auth]').addEventListener('click', () => authModal.classList.remove('open'));
        document.querySelector('[data-close-cart]').addEventListener('click', () => panel.classList.remove('open'));
        document.querySelector('[data-checkout]').addEventListener('click', () => { panel.classList.remove('open'); modal.classList.add('open'); });
        document.querySelectorAll('input[name="payment"]').forEach(input => input.addEventListener('change', updateBitcoinQuote));
        document.querySelector('[data-payment-form]').department.addEventListener('input', updateBitcoinQuote);
        document.querySelector('[data-close-checkout]').addEventListener('click', () => modal.classList.remove('open'));
        document.querySelector('[data-auth-modal]').addEventListener('click', event => { if (event.target === authModal) authModal.classList.remove('open'); });
        document.querySelector('[data-payment-form]').addEventListener('submit', async event => {
            event.preventDefault();
            const form = event.currentTarget;
            const feedback = document.querySelector('[data-checkout-feedback]');
            const button = form.querySelector('[type="submit"]');
            button.disabled = true;
            feedback.hidden = true;
            try {
                const totalItems = cart.reduce((sum, item) => sum + Number(item.quantity), 0);
                const shippingCost = cart.length > 0 ? (['la_union', 'morazan', 'ahuachapan', 'cabanas', 'santa_ana'].includes((form.department.value || '').trim().toLowerCase()) ? 6 : 5) + (totalItems > 5 ? 1 : 0) + (totalItems > 15 ? 2 : 0) : 0;
                const response = await fetch('{{ route('checkout.store') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ customer: { name: form.name.value, email: form.email.value, phone: form.phone.value, country: form.country.value, department: form.department.value, municipality: form.municipality.value, address: form.address.value, address_reference: form.address_reference.value }, payment_method: form.payment.value, shipping_zone: (form.department.value || '').trim(), shipping_cost: shippingCost, items: cart.map(item => ({ id: Number(item.id), quantity: item.quantity })) }) });
                const data = await response.json();
                if (! response.ok) throw new Error(data.message || 'No fue posible crear la orden.');
                cart.splice(0); save(); render(); form.reset(); feedback.textContent = `Orden ${data.order_number} creada. El pago está pendiente de confirmación.`; feedback.classList.add('success'); feedback.hidden = false;
            } catch (error) { feedback.textContent = error.message; feedback.classList.remove('success'); feedback.hidden = false; }
            button.disabled = false;
        });
        render(); renderWishlist();
    </script>
</body>
</html>