{{-- resources/views/layouts/partials/path.blade.php --}}
@php
    use App\Models\Category;
    use App\Models\Product;

    $route = request()->route();
    $routeName = $route?->getName();

    // =========================
    // NON MOSTRARE IN HOME
    // =========================
    if ($routeName === 'home') {
        return;
    }

    // Breadcrumb: ogni item = ['label' => string, 'url' => string|null]
    $crumbs = [];
    $crumbs[] = ['label' => 'Home', 'url' => route('home')];

    // =========================
    // STAFF: Home / Dashboard / ...
    // =========================
    if (str_starts_with($routeName ?? '', 'staff.')) {

        // sempre presente
        $crumbs[] = ['label' => 'Dashboard', 'url' => route('staff.dashboard')];

        // se NON siamo esattamente nella dashboard
        if ($routeName !== 'staff.dashboard') {
            $staffMap = [
                'staff.orders.index'      => 'Ordini',
                'staff.orders.show'       => 'Ordini',
                'staff.products.index'    => 'Prodotti',
                'staff.products.create'   => 'Nuovo prodotto',
                'staff.products.edit'     => 'Modifica prodotto',
                'staff.users.index'       => 'Utenti',
                'staff.capacity.edit'     => 'Capienza',
                'staff.categories.index'  => 'Categorie',
                'staff.categories.create' => 'Nuova categoria',
                'staff.categories.edit'   => 'Modifica categoria',
            ];

            if (isset($staffMap[$routeName])) {
                $crumbs[] = ['label' => $staffMap[$routeName], 'url' => null];
            } else {
                $crumbs[] = [
                    'label' => ucfirst(str_replace(['staff.', '.'], ['', ' '], $routeName ?? '')),
                    'url'   => null
                ];
            }
        }
    }

    // =========================
    // ORDINI UTENTE
    // =========================
    elseif ($routeName === 'orders.my') {
        $crumbs[] = ['label' => 'Ordini', 'url' => null];
    }
    elseif ($routeName === 'orders.show') {
        $crumbs[] = ['label' => 'Ordini', 'url' => route('orders.my')];
        $orderParam = $route?->parameter('order');
        $crumbs[] = [
            'label' => 'Ordine #' . (is_object($orderParam) ? ($orderParam->id ?? '') : $orderParam),
            'url'   => null
        ];
    }

    // =========================
    // CATEGORIA
    // =========================
    elseif ($routeName === 'category.show') {
        $slug = $route?->parameter('slug');
        $cat  = $slug ? Category::where('slug', $slug)->first() : null;

        $crumbs[] = [
            'label' => $cat?->name ?? 'Categoria',
            'url'   => null
        ];
    }

    // =========================
    // PRODOTTO
    // =========================
    elseif ($routeName === 'product.show') {
        $slug = $route?->parameter('slug');
        $prod = $slug ? Product::with('category')->where('slug', $slug)->first() : null;

        if ($prod?->category) {
            $crumbs[] = [
                'label' => $prod->category->name,
                'url'   => route('category.show', $prod->category->slug),
            ];
        }

        $crumbs[] = [
            'label' => $prod?->name ?? 'Prodotto',
            'url'   => null,
        ];
    }

    // =========================
    // FALLBACK: altre pagine
    // =========================
    else {
        $rawPath  = trim(request()->path(), '/');
        $segments = $rawPath === '' ? [] : explode('/', $rawPath);

        $labelMap = [
            'cart'     => ['label' => 'Carrello',   'url' => route('cart.index')],
            'checkout' => ['label' => 'Checkout',   'url' => route('checkout.show')],
            'profile'  => ['label' => 'Profilo',    'url' => route('profile.edit')],
            'login'    => ['label' => 'Login',      'url' => route('login')],
            'register' => ['label' => 'Registrati', 'url' => route('register')],
        ];

        foreach ($segments as $seg) {
            $crumbs[] = $labelMap[$seg]
                ?? ['label' => ucfirst(str_replace('-', ' ', urldecode($seg))), 'url' => null];
        }
    }
@endphp

{{-- =========================
     RENDER BREADCRUMB
     ========================= --}}
<div class="bg-slate-50 border-b border-slate-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 text-xs text-slate-600">
        <nav class="flex items-center flex-wrap gap-1" aria-label="Breadcrumb">
            @foreach($crumbs as $i => $c)
                @if($i > 0)
                    <span class="text-slate-400">/</span>
                @endif

                @if(!empty($c['url']) && $i < count($crumbs) - 1)
                    <a href="{{ $c['url'] }}" class="hover:text-slate-900 hover:underline">
                        {{ $c['label'] }}
                    </a>
                @else
                    <span class="font-medium text-slate-900">
                        {{ $c['label'] }}
                    </span>
                @endif
            @endforeach
        </nav>
    </div>
</div>
