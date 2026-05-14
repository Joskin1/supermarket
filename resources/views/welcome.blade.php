<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>White-Mart — Freshness & Quality Every Day</title>
    <meta name="description" content="White-Mart at Iyana Era, Ijanikin, Lagos. Fresh groceries, household essentials, beverages & more. Open Mon-Sat 8AM-9PM, Sun 10AM-8PM.">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --wm-green: #009245;
            --wm-green-dark: #007a3a;
            --wm-red: #E10600;
            --wm-dark: #1a1a2e;
            --wm-light: #f8faf9;
        }
        * { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-white text-gray-800 antialiased">

    {{-- ===== NAVBAR ===== --}}
    <nav id="navbar" class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 transition-shadow">
        <div class="max-w-7xl mx-auto flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">
            <a href="#home" class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-white font-black text-lg" style="background:var(--wm-green)">W</span>
                <span class="font-extrabold text-xl tracking-tight" style="color:var(--wm-dark)">White<span style="color:var(--wm-green)">-Mart</span></span>
            </a>
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
                <a href="#home" class="hover:text-[#009245] transition">Home</a>
                <a href="#categories" class="hover:text-[#009245] transition">Products</a>
                <a href="#about" class="hover:text-[#009245] transition">About Us</a>
                <a href="#location" class="hover:text-[#009245] transition">Location</a>
            </div>
            <a href="{{ url('/admin/login') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:scale-105 hover:shadow-lg"
               style="background:var(--wm-green)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                Staff Portal
            </a>
        </div>
    </nav>

    {{-- ===== HERO ===== --}}
    <section id="home" class="relative min-h-[92vh] flex items-center justify-center overflow-hidden pt-16">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image:url('/images/hero-bg.png')"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/50 to-black/30"></div>
        <div class="relative z-10 max-w-4xl mx-auto px-6 text-center">
            <span class="inline-block px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest text-white/90 border border-white/20 backdrop-blur-sm mb-6"
                  style="background:rgba(0,146,69,0.35)">
                🛒 Your Neighborhood Store
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-tight mb-6">
                Welcome to <span style="color:#4ade80">White-Mart</span><br>
                <span class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white/90">Freshness & Quality Every Day</span>
            </h1>
            <p class="text-lg sm:text-xl text-white/80 max-w-2xl mx-auto mb-10">
                Your trusted neighborhood store at Iyana Era, Ijanikin. Fresh groceries, household essentials, and everything your family needs — all under one roof.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#location"
                   class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl text-base font-bold text-white shadow-xl transition-all hover:scale-105"
                   style="background:var(--wm-green)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    Visit Us Today
                </a>
                <a href="#categories"
                   class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl text-base font-bold text-white border-2 border-white/30 backdrop-blur-sm transition-all hover:bg-white/10">
                    Explore Products
                </a>
            </div>
        </div>
    </section>

    {{-- ===== FEATURED CATEGORIES ===== --}}
    <section id="categories" class="py-20 lg:py-28" style="background:var(--wm-light)">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest mb-4" style="background:#e6f4ec;color:var(--wm-green)">What We Offer</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold" style="color:var(--wm-dark)">Shop by Category</h2>
                <p class="text-gray-500 mt-3 max-w-xl mx-auto">Everything you need for your home, stocked fresh and priced right.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $cats = [
                        ['icon'=>'🥬','title'=>'Fresh Groceries','desc'=>'Rice, pasta, cooking oil, seasonings & pantry staples for every meal.','color'=>'#009245'],
                        ['icon'=>'🧴','title'=>'Household Essentials','desc'=>'Cleaning supplies, detergents, and everything to keep your home spotless.','color'=>'#2563eb'],
                        ['icon'=>'🥤','title'=>'Beverages','desc'=>'Soft drinks, juices, bottled water & refreshments served chilled daily.','color'=>'#dc2626'],
                        ['icon'=>'🧼','title'=>'Personal Care','desc'=>'Toiletries, skincare, oral care & grooming products you trust.','color'=>'#7c3aed'],
                    ];
                @endphp
                @foreach($cats as $cat)
                <div class="group bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center text-3xl mb-5" style="background:{{ $cat['color'] }}15">{{ $cat['icon'] }}</div>
                    <h3 class="text-lg font-bold mb-2" style="color:var(--wm-dark)">{{ $cat['title'] }}</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ $cat['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== ABOUT & STORE INFO ===== --}}
    <section id="about" class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest mb-4" style="background:#e6f4ec;color:var(--wm-green)">About Us</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold mb-6" style="color:var(--wm-dark)">Serving the Ijanikin Community with Pride</h2>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        White-Mart is more than just a store — we're your neighbors. Located in the heart of Iyana Era, Ijanikin, we've built our reputation on three simple promises: <strong>fresh products</strong>, <strong>fair prices</strong>, and <strong>friendly service</strong>.
                    </p>
                    <p class="text-gray-600 leading-relaxed mb-8">
                        From everyday pantry staples to trusted household brands, we stock everything your family needs. Our shelves are restocked daily to ensure you always find what you're looking for.
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-xl p-5 text-center">
                            <div class="text-2xl font-black" style="color:var(--wm-green)">26+</div>
                            <div class="text-xs font-medium text-gray-500 mt-1">Products Available</div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-5 text-center">
                            <div class="text-2xl font-black" style="color:var(--wm-green)">7</div>
                            <div class="text-xs font-medium text-gray-500 mt-1">Departments</div>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <div class="rounded-2xl overflow-hidden shadow-2xl">
                        <img src="/images/storefront.png" alt="White-Mart storefront at Iyana Era, Ijanikin" class="w-full h-auto object-cover" loading="lazy">
                    </div>
                    {{-- Hours card --}}
                    <div class="absolute -bottom-6 -left-4 sm:left-6 bg-white rounded-xl shadow-xl p-5 border border-gray-100">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-sm" style="background:var(--wm-green)">🕐</span>
                            <span class="font-bold text-sm" style="color:var(--wm-dark)">Opening Hours</span>
                        </div>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between gap-8"><span class="text-gray-500">Mon – Sat</span><span class="font-semibold">8:00 AM – 9:00 PM</span></div>
                            <div class="flex justify-between gap-8"><span class="text-gray-500">Sunday</span><span class="font-semibold">10:00 AM – 8:00 PM</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== LOCATION & CONTACT ===== --}}
    <section id="location" class="py-20 lg:py-28" style="background:var(--wm-dark)">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest mb-4" style="background:rgba(0,146,69,0.2);color:#4ade80">Find Us</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white">Visit White-Mart Today</h2>
                <p class="text-gray-400 mt-3 max-w-xl mx-auto">We're conveniently located at Iyana Era, Ijanikin — easy to find, easy to shop.</p>
            </div>
            <div class="grid lg:grid-cols-2 gap-8">
                <div class="rounded-2xl overflow-hidden h-80 lg:h-auto shadow-xl">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3964.0!2d3.26!3d6.47!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwMjgnMTIuMCJOIDPCsDE1JzM2LjAiRQ!5e0!3m2!1sen!2sng!4v1!5m2!1sen!2sng"
                        width="100%" height="100%" style="border:0;min-height:320px" allowfullscreen loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade" title="White-Mart location on Google Maps">
                    </iframe>
                </div>
                <div class="space-y-6">
                    {{-- Address --}}
                    <div class="bg-white/5 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                        <div class="flex items-start gap-4">
                            <span class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 text-white" style="background:var(--wm-green)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            </span>
                            <div>
                                <h3 class="font-bold text-white mb-1">Address</h3>
                                <p class="text-gray-300 text-sm leading-relaxed">59, Ibasa Nla Road, Iyana Era,<br>Ijanikin, Lagos State, Nigeria</p>
                            </div>
                        </div>
                    </div>
                    {{-- Phone --}}
                    <div class="bg-white/5 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                        <div class="flex items-start gap-4">
                            <span class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 text-white" style="background:var(--wm-green)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                            </span>
                            <div>
                                <h3 class="font-bold text-white mb-1">Phone</h3>
                                <p class="text-gray-300 text-sm">+234 xxx xxx xxxx</p>
                            </div>
                        </div>
                    </div>
                    {{-- Email --}}
                    <div class="bg-white/5 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                        <div class="flex items-start gap-4">
                            <span class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 text-white" style="background:var(--wm-green)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                            </span>
                            <div>
                                <h3 class="font-bold text-white mb-1">Email</h3>
                                <p class="text-gray-300 text-sm">hello@whitemart.ng</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== FOOTER ===== --}}
    <footer class="bg-gray-950 text-gray-400 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm">
            <p>&copy; {{ date('Y') }} White-Mart. All rights reserved.</p>
            <a href="{{ url('/admin/login') }}" class="hover:text-white transition text-xs">System Login</a>
        </div>
    </footer>

</body>
</html>
