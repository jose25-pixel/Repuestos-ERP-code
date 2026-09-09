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
        .product { border-bottom: 1px solid var(--line); border-right: 1px solid var(--line); display: flex; flex-direction: column; min-height: 390px; padding: 18px; }
        .product:nth-child(4n) { border-right: 0; }
        .product-image { align-items: center; background: #f5f8fa; display: flex; height: 180px; justify-content: center; margin: -18px -18px 20px; overflow: hidden; }
        .product-image img { height: 100%; object-fit: cover; width: 100%; }
        .placeholder { color: var(--cyan); font-size: 48px; font-weight: 700; }
        .company { color: var(--muted); font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .product h2 { color: var(--nav); font-size: 17px; line-height: 1.35; margin: 9px 0 8px; }
        .sku { color: var(--muted); font-size: 13px; }
        .price { color: var(--cyan); font-size: 25px; font-weight: 700; margin: auto 0 12px; padding-top: 20px; }
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
        @media (max-width: 1050px) { .grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } .product:nth-child(4n) { border-right: 1px solid var(--line); } .product:nth-child(3n) { border-right: 0; } }
        @media (max-width: 760px) { .utility { display: none; } .header-inner { gap: 18px; grid-template-columns: 1fr auto; min-height: auto; padding-bottom: 16px; padding-top: 16px; } .search { grid-column: 1 / -1; grid-row: 2; } .account { border: 0; padding: 0; } .cart-trigger { grid-column: 2; grid-row: 1; }.menu-inner { gap: 23px; } .catalog { display: block; padding-left: 16px; padding-right: 16px; } aside { margin-bottom: 24px; } .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .product { min-height: 340px; padding: 13px; } .product-image { height: 130px; margin: -13px -13px 14px; } .product h2 { font-size: 15px; } .price { font-size: 20px; } .catalog-head { align-items: flex-start; gap: 12px; } .sort { font-size: 12px; } }
    </style>
</head>
<body>
    <header>
        <div class="utility">Atención a clientes · Venta de repuestos para línea blanca</div>
        <div class="header-inner">
            <a class="brand" href="{{ route('inventory.index') }}"><small>MAYOREO · MENUDEO</small>REPUES<span>TOS</span>ERP</a>
            <form class="search" action="{{ route('inventory.index') }}" method="GET"><input name="search" type="search" value="{{ $search }}" placeholder="Buscar por nombre o SKU"><button type="submit">Buscar</button></form>
            <a class="account" href="/admin/login">Mi cuenta</a>
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
            <div class="catalog-head"><div><h1>Refacciones</h1><p class="count">Mostrando {{ $products->count() }} productos</p></div><select class="sort" aria-label="Ordenar productos"><option>Más recientes</option><option>Menor precio</option><option>Mayor precio</option></select></div>
            @if ($products->isEmpty())
                <div class="empty">Aún no hay productos publicados con estos criterios.</div>
            @else
                <div class="grid">
                    @foreach ($products as $product)
                        @php($quantity = $product->stock?->quantity ?? 0)
                        <article class="product">
                            <div class="product-image">
                                @if (! empty($product->images))
                                    <img src="{{ asset('storage/'.$product->images[0]) }}" alt="{{ $product->name }}">
                                @else
                                    <span class="placeholder">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <span class="company">{{ $product->company?->name ?? 'RepuestosERP' }}</span>
                            <h2>{{ $product->name }}</h2>
                            <span class="sku">SKU: {{ $product->sku }}</span>
                            <p class="price">${{ number_format((float) $product->price, 2) }}</p>
                            <span class="availability {{ $quantity === 0 ? 'out' : '' }}">{{ $quantity === 0 ? 'Agotado' : 'Disponible' }}</span>
                            <button class="add-cart" type="button" data-add-cart data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price }}" {{ $quantity === 0 ? 'disabled' : '' }}>Agregar al carrito</button>
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
            <div class="customer-fields"><input name="name" required placeholder="Nombre completo"><input name="email" type="email" required placeholder="Correo electrónico"><input name="phone" placeholder="Teléfono (opcional)"></div>
            <div class="payment-options"><label class="payment"><input type="radio" name="payment" value="card" checked><span><strong>Tarjeta de crédito o débito</strong><span>Visa y Mastercard</span></span></label><label class="payment"><input type="radio" name="payment" value="bank"><span><strong>Transferencia bancaria</strong><span>Confirmación manual de depósito o transferencia</span></span></label><label class="payment"><input type="radio" name="payment" value="lightning"><span><strong>Bitcoin Lightning</strong><span>Pago instantáneo por Lightning Network</span></span></label></div>
            <p class="checkout-feedback" data-checkout-feedback hidden></p><button class="pay-button" type="submit">Crear orden pendiente</button>
        </form>
    </div>
    <script>
        const cartKey = 'repuestoserp-cart';
        const cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
        const panel = document.querySelector('[data-cart-panel]');
        const modal = document.querySelector('[data-checkout-modal]');
        const format = value => `$${Number(value).toFixed(2)}`;
        const escapeHtml = value => String(value).replace(/[&<>'"]/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character]);
        const save = () => localStorage.setItem(cartKey, JSON.stringify(cart));
        const render = () => {
            const items = document.querySelector('[data-cart-items]');
            const total = cart.reduce((sum, item) => sum + Number(item.price) * item.quantity, 0);
            document.querySelector('[data-cart-count]').textContent = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.querySelector('[data-cart-total]').textContent = format(total);
            document.querySelector('[data-checkout]').disabled = cart.length === 0;
            items.innerHTML = cart.length ? cart.map(item => `<div class="cart-item"><div><strong>${escapeHtml(item.name)}</strong><small>${item.quantity} x ${format(item.price)}</small></div><button type="button" data-remove="${item.id}">Quitar</button></div>`).join('') : '<p class="cart-empty">Tu carrito está vacío.</p>';
        };
        document.querySelectorAll('[data-add-cart]').forEach(button => button.addEventListener('click', () => {
            const item = cart.find(item => item.id === button.dataset.id);
            item ? item.quantity++ : cart.push({ id: button.dataset.id, name: button.dataset.name, price: button.dataset.price, quantity: 1 });
            save(); render(); panel.classList.add('open');
        }));
        document.querySelector('[data-cart-items]').addEventListener('click', event => { if (event.target.dataset.remove) { cart.splice(cart.findIndex(item => item.id === event.target.dataset.remove), 1); save(); render(); } });
        document.querySelector('[data-open-cart]').addEventListener('click', () => panel.classList.add('open'));
        document.querySelector('[data-close-cart]').addEventListener('click', () => panel.classList.remove('open'));
        document.querySelector('[data-checkout]').addEventListener('click', () => { panel.classList.remove('open'); modal.classList.add('open'); });
        document.querySelector('[data-close-checkout]').addEventListener('click', () => modal.classList.remove('open'));
        document.querySelector('[data-payment-form]').addEventListener('submit', async event => {
            event.preventDefault();
            const form = event.currentTarget;
            const feedback = document.querySelector('[data-checkout-feedback]');
            const button = form.querySelector('[type="submit"]');
            button.disabled = true;
            feedback.hidden = true;
            try {
                const response = await fetch('{{ route('checkout.store') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ customer: { name: form.name.value, email: form.email.value, phone: form.phone.value }, payment_method: form.payment.value, items: cart.map(item => ({ id: Number(item.id), quantity: item.quantity })) }) });
                const data = await response.json();
                if (! response.ok) throw new Error(data.message || 'No fue posible crear la orden.');
                cart.splice(0); save(); render(); form.reset(); feedback.textContent = `Orden ${data.order_number} creada. El pago está pendiente de confirmación.`; feedback.classList.add('success'); feedback.hidden = false;
            } catch (error) { feedback.textContent = error.message; feedback.classList.remove('success'); feedback.hidden = false; }
            button.disabled = false;
        });
        render();
    </script>
</body>
</html>