<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>White-Mart Supermarket | Freshness & Quality Every Day</title>
    <meta name="description" content="Your trusted neighborhood supermarket at Iyana Era, Ijanikin.">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN (landing page standalone) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#009245',
                        secondary: '#E10600',
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --color-primary: #009245;
            --color-secondary: #E10600;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        h1,
        h2,
        h3,
        .font-heading {
            font-family: 'Roboto Slab', serif;
        }

        .loaded-success .preloader {
            visibility: hidden !important;
            opacity: 0;
            transition: all 0.6s ease-in-out;
        }
    </style>
</head>

<body class="text-zinc-700 bg-zinc-50 font-sans antialiased">
    <!-- preloader -->
    <div
        class="preloader fixed inset-0 z-50 bg-zinc-50 flex items-center justify-center transition-all duration-1000 ease-in-out">
        <svg class="animate-spin h-10 w-10 text-[var(--color-primary)]" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
    </div>

    <!-- ========== { HEADER }==========  -->
    <header class="fixed top-0 left-0 right-0 z-40 transition-all duration-300">
        <nav class="main-nav w-full py-4 bg-white/95 backdrop-blur-sm border-b border-zinc-200/50 shadow-sm">
            <div class="container xl:max-w-6xl mx-auto px-4">
                <div class="lg:flex lg:justify-between items-center">
                    <div class="flex justify-between items-center w-full lg:w-auto">
                        <div class="text-3xl font-bold text-[var(--color-primary)] flex items-center font-heading">
                            White-Mart
                        </div>
                        <!-- mobile nav toggle -->
                        <div class="block lg:hidden">
                            <button type="button"
                                class="menu-mobile p-2 text-zinc-600 hover:text-[var(--color-primary)] focus:outline-none">
                                <svg class="open h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                                <svg class="close h-6 w-6 hidden" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div
                        class="hidden lg:flex flex-col lg:flex-row navbar lg:items-center w-full lg:w-auto mt-4 lg:mt-0 bg-white lg:bg-transparent">
                        <ul
                            class="flex flex-col lg:flex-row text-center lg:text-left text-zinc-600 font-semibold lg:space-x-8">
                            <li><a class="block py-3 lg:py-0 hover:text-[var(--color-primary)] transition-colors duration-200"
                                    href="#hero">Home</a></li>
                            <li><a class="block py-3 lg:py-0 hover:text-[var(--color-primary)] transition-colors duration-200"
                                    href="#departments">Departments</a></li>
                            <li><a class="block py-3 lg:py-0 hover:text-[var(--color-primary)] transition-colors duration-200"
                                    href="#store-info">Store Info</a></li>
                            <li><a class="block py-3 lg:py-0 hover:text-[var(--color-primary)] transition-colors duration-200"
                                    href="#location">Location</a></li>
                        </ul>
                        <div class="mt-4 lg:mt-0 lg:ml-8 text-center lg:text-left">
                            <a href="/admin/login"
                                class="inline-block px-6 py-2.5 bg-[var(--color-primary)] text-white font-medium rounded-full shadow-md hover:bg-green-700 hover:shadow-lg transition-all duration-300">Staff
                                Portal</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main id="content">
        <!-- hero start -->
        <div id="hero" class="section relative z-0 py-24 md:py-36 bg-white overflow-hidden">
            <!-- Background Decoration -->
            <div class="absolute inset-0 z-[-1] opacity-10"
                style="background-image: radial-gradient(var(--color-primary) 1px, transparent 1px); background-size: 32px 32px;">
            </div>

            <div class="container xl:max-w-6xl mx-auto px-4">
                <div class="flex flex-wrap flex-row -mx-4 items-center">

                    <!-- text -->
                    <div class="w-full lg:w-1/2 px-4 order-2 lg:order-1 mt-12 lg:mt-0 text-center lg:text-left">
                        <span
                            class="inline-block py-1 px-3 rounded-full bg-green-50 text-[var(--color-primary)] text-sm font-semibold tracking-wider mb-4 border border-green-200 shadow-sm uppercase">Ijanikin's
                            Finest</span>
                        <h1
                            class="text-4xl md:text-5xl lg:text-6xl leading-tight text-zinc-900 font-bold mb-6 font-heading">
                            Welcome to <span class="text-[var(--color-primary)]">White-Mart</span><br>
                            Freshness & Quality
                        </h1>
                        <p class="text-zinc-600 leading-relaxed text-lg md:text-xl max-w-2xl mx-auto lg:mx-0 mb-10">
                            Your trusted neighborhood supermarket at Iyana Era, Ijanikin. We provide the best products
                            at the most affordable prices every single day.
                        </p>

                        <div
                            class="flex flex-col sm:flex-row justify-center lg:justify-start space-y-4 sm:space-y-0 sm:space-x-4">
                            <a class="py-3 px-8 text-center font-semibold text-white bg-[var(--color-primary)] rounded-full shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
                                href="#location">
                                Visit Us Today
                            </a>
                            <a class="py-3 px-8 text-center font-semibold text-zinc-700 bg-white border border-zinc-200 rounded-full shadow-sm hover:border-[var(--color-primary)] hover:text-[var(--color-primary)] hover:-translate-y-1 transition-all duration-300"
                                href="#departments">
                                Explore Departments
                            </a>
                        </div>
                    </div>

                    <!-- hero image -->
                    <div class="w-full lg:w-1/2 px-4 order-1 lg:order-2">
                        <div class="relative rounded-2xl overflow-hidden shadow-2xl transform hover:scale-[1.02] transition-transform duration-500 bg-zinc-100 flex items-center justify-center"
                            style="min-height: 400px;">
                            <img src="/landing-img/hero" class="w-full h-full object-cover absolute inset-0"
                                alt="White-Mart Supermarket Interior">
                            <div class="text-zinc-400 font-medium italic z-10" id="hero-placeholder"
                                style="display: none;">Hero Image Generating...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Departments -->
        <div id="departments" class="section relative py-20 bg-zinc-50">
            <div class="container xl:max-w-6xl mx-auto px-4">
                <header class="text-center mx-auto mb-16 lg:px-20 max-w-3xl">
                    <h2 class="text-3xl md:text-4xl leading-normal mb-4 font-bold text-zinc-900 font-heading">Our
                        Departments</h2>
                    <div class="h-1 w-20 bg-[var(--color-primary)] mx-auto rounded-full mb-6"></div>
                    <p class="text-zinc-500 leading-relaxed text-lg">We stock a wide variety of high-quality products to
                        meet all your daily needs.</p>
                </header>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Dept 1 -->
                    <div
                        class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transform hover:-translate-y-2 transition-all duration-300 border border-zinc-100 text-center group">
                        <div
                            class="inline-flex items-center justify-center w-20 h-20 bg-green-50 text-[var(--color-primary)] rounded-full mb-6 group-hover:bg-[var(--color-primary)] group-hover:text-white transition-colors duration-300">
                            <!-- Icon can be replaced with AI generated 3D icon later -->
                            <img src="/landing-img/produce" class="w-12 h-12 object-contain" alt="Fresh Produce"
                                onerror="this.style.display='none'">
                        </div>
                        <h3 class="text-xl font-bold text-zinc-900 mb-3">Fresh Produce</h3>
                        <p class="text-zinc-500">Farm-fresh fruits and vegetables sourced daily to guarantee the best
                            quality.</p>
                    </div>

                    <!-- Dept 2 -->
                    <div
                        class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transform hover:-translate-y-2 transition-all duration-300 border border-zinc-100 text-center group">
                        <div
                            class="inline-flex items-center justify-center w-20 h-20 bg-green-50 text-[var(--color-primary)] rounded-full mb-6 group-hover:bg-[var(--color-primary)] group-hover:text-white transition-colors duration-300">
                            <img src="/landing-img/household" class="w-12 h-12 object-contain"
                                alt="Household Essentials" onerror="this.style.display='none'">
                        </div>
                        <h3 class="text-xl font-bold text-zinc-900 mb-3">Household Essentials</h3>
                        <p class="text-zinc-500">Everything you need to keep your home clean, organized, and running
                            smoothly.</p>
                    </div>

                    <!-- Dept 3 -->
                    <div
                        class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transform hover:-translate-y-2 transition-all duration-300 border border-zinc-100 text-center group">
                        <div
                            class="inline-flex items-center justify-center w-20 h-20 bg-green-50 text-[var(--color-primary)] rounded-full mb-6 group-hover:bg-[var(--color-primary)] group-hover:text-white transition-colors duration-300">
                            <img src="/landing-img/beverages" class="w-12 h-12 object-contain" alt="Beverages"
                                onerror="this.style.display='none'">
                        </div>
                        <h3 class="text-xl font-bold text-zinc-900 mb-3">Beverages</h3>
                        <p class="text-zinc-500">A wide selection of refreshing drinks, from premium juices to daily
                            water supplies.</p>
                    </div>

                    <!-- Dept 4 -->
                    <div
                        class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transform hover:-translate-y-2 transition-all duration-300 border border-zinc-100 text-center group">
                        <div
                            class="inline-flex items-center justify-center w-20 h-20 bg-green-50 text-[var(--color-primary)] rounded-full mb-6 group-hover:bg-[var(--color-primary)] group-hover:text-white transition-colors duration-300">
                            <img src="/landing-img/personal-care" class="w-12 h-12 object-contain"
                                alt="Personal Care" onerror="this.style.display='none'">
                        </div>
                        <h3 class="text-xl font-bold text-zinc-900 mb-3">Personal Care</h3>
                        <p class="text-zinc-500">Top brands in health, beauty, and personal hygiene for you and your
                            family.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Store Info -->
        <div id="store-info" class="section relative py-20 bg-white">
            <div class="container xl:max-w-4xl mx-auto px-4 text-center">
                <header class="mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-zinc-900 mb-4 font-heading">Store Information</h2>
                    <div class="h-1 w-20 bg-[var(--color-primary)] mx-auto rounded-full mb-6"></div>
                    <p class="text-zinc-500 text-lg max-w-2xl mx-auto">White-Mart is committed to serving the Ijanikin
                        community with excellence, providing a clean, safe, and well-stocked shopping environment.</p>
                </header>

                <div
                    class="bg-green-50/50 rounded-3xl p-8 md:p-12 border border-green-100 shadow-sm max-w-2xl mx-auto transform transition duration-500 hover:shadow-md">
                    <div class="flex items-center justify-center mb-6 text-[var(--color-primary)]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-zinc-900 mb-6">Opening Hours</h3>
                    <div class="space-y-4 text-lg">
                        <div class="flex justify-between items-center border-b border-green-200/50 pb-4">
                            <span class="font-medium text-zinc-700">Monday - Saturday</span>
                            <span
                                class="text-[var(--color-primary)] font-bold bg-white px-4 py-1 rounded-full shadow-sm">8:00
                                AM - 9:00 PM</span>
                        </div>
                        <div class="flex justify-between items-center pt-2">
                            <span class="font-medium text-zinc-700">Sunday</span>
                            <span
                                class="text-[var(--color-primary)] font-bold bg-white px-4 py-1 rounded-full shadow-sm">10:00
                                AM - 8:00 PM</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location -->
        <div id="location" class="section relative py-20 bg-zinc-900 text-zinc-300">
            <div class="container xl:max-w-6xl mx-auto px-4">
                <div class="flex flex-col lg:flex-row -mx-4 gap-y-12">

                    <!-- Contact Details -->
                    <div class="w-full lg:w-1/3 px-4 flex flex-col justify-center">
                        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6 font-heading">Find Us Here</h2>
                        <div class="h-1 w-16 bg-[var(--color-primary)] rounded-full mb-8"></div>

                        <div class="space-y-8">
                            <div class="flex items-start">
                                <div class="bg-zinc-800 p-3 rounded-lg text-[var(--color-primary)] mr-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-white font-semibold text-lg mb-1">Address</h4>
                                    <p class="text-zinc-400 leading-relaxed">59, Ibasa Nla Road,<br>Iyana Era,
                                        Ijanikin,<br>Lagos State</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="bg-zinc-800 p-3 rounded-lg text-[var(--color-primary)] mr-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-white font-semibold text-lg mb-1">Contact</h4>
                                    <p class="text-zinc-400">+234 (0) 800 000 0000<br>support@white-mart.com</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Map -->
                    <div class="w-full lg:w-2/3 px-4">
                        <div class="rounded-2xl overflow-hidden shadow-2xl h-[400px] border border-zinc-800 relative">
                            <!-- Placeholder map, iframe to actual Google map -->
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15858.530366669966!2d3.149179911964121!3d6.441018318463994!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x103b9e4a3c10bdcd%3A0x867ea64ebde7f722!2sIjanikin%2C%20Lagos!5e0!3m2!1sen!2sng!4v1700000000000!5m2!1sen!2sng"
                                width="100%" height="100%" style="border:0; filter: grayscale(50%) contrast(1.2);"
                                allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- =========={ FOOTER }==========  -->
    <footer class="bg-zinc-950 text-zinc-400 py-8 border-t border-zinc-800">
        <div class="container xl:max-w-6xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center text-sm">
            <div class="mb-4 md:mb-0">
                &copy; {{ date('Y') }} White-Mart Supermarket. All rights reserved.
            </div>
            <div>
                <a href="/login" class="hover:text-white transition-colors duration-200">System Login</a>
            </div>
        </div>
    </footer>

    <script>
        // Preloader
        window.addEventListener('load', function () {
            document.body.classList.add('loaded-success');
        });
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
        // Mobile menu toggle
        document.querySelectorAll('.menu-mobile').forEach(btn => {
            btn.addEventListener('click', function () {
                const navbar = document.querySelector('.navbar');
                if (navbar) navbar.classList.toggle('hidden');
                btn.querySelector('.open').classList.toggle('hidden');
                btn.querySelector('.close').classList.toggle('hidden');
            });
        });
    </script>
</body>

</html>