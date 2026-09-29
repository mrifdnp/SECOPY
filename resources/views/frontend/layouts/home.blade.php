<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('assets/homepage/Logo.svg') }}" type="image/svg+xml" />
    <title>@yield('title', 'Seco')</title>
    <meta name="title" content="@yield('meta_title', 'Seco')" />
    <meta name="description" content="@yield('meta_description', 'Seken Living Official Website')" />
    
    <!-- SEO Optimization -->
    <link rel="canonical" href="@yield('canonical', url()->current())" />
    <meta name="robots" content="@yield('meta_robots', 'index, follow')" />
    <meta property="og:title" content="@yield('meta_title', 'Seco')" />
    <meta property="og:description" content="@yield('meta_description', 'Seco by Seken Living Official Website')" />
    <meta property="og:url" content="{{ url()->current() }}" />

    <script src="{{ asset('assets/js/swiper-bundle.min.js') }}"></script>

    <!-- Fonts -->
    <link href="{{ asset('assets/fonts/cabinet-grotesk.css') }}" rel="stylesheet" />
    @vite(['resources/js/app.js', 'resources/css/app.css'], 'build-vite')
    @yield('css')
</head>

<body class="font-cabinet md:overflow-visible">

    @include('frontend.includes.header')

    <main id="main">
        @yield('content')
    </main>

    @include('frontend.includes.footer')


    @if(session('lang') === 'id')
        <div id="google_translate_element" style="display:none;"></div>

        <script>
            function googleTranslateElementInit() {
                 new google.translate.TranslateElement({
                    pageLanguage: 'en',
                    includedLanguages: 'id,en',
                    autoDisplay: false
                }, 'google_translate_element');

                // Tunggu widget load, lalu paksa translate ke 'id'
                setTimeout(function() {
                    var select = document.querySelector("select.goog-te-combo");
                    if (select) {
                        select.value = "id"; // target language
                        select.dispatchEvent(new Event("change"));
                    }
                }, 1000);
            }
        </script>
        <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    @endif

    <!-- AI Chat Floating Button -->
    @php
        $lang = session('lang', 'en');
        $chatTitle = 'Seco AI Assistant';
        $aiWelcomeMsg = $lang === 'id' 
            ? 'Hai! Saya adalah AI Assistant Seco. Ada yang bisa saya bantu hari ini terkait koleksi furniture atau dekorasi kami?' 
            : 'Hi! I am the Seco AI Assistant. How can I help you today regarding our furniture or decor collections?';
        $placeholderTxt = $lang === 'id' ? 'Ketik pesan...' : 'Type a message...';
    @endphp

    <div id="ai-chat-container" style="position:fixed; bottom:40px; right:40px; z-index:9999;">
        <!-- Chat Window -->
        <div id="ai-chat-window" class="hidden bg-white shadow-xl flex-col overflow-hidden absolute transition-all duration-300 ease-in-out opacity-0 pointer-events-none" style="width:350px; height:500px; border-radius:16px; bottom:80px; right:0; transform:translateY(20px);">
            <!-- Header -->
            <div class="bg-primary-dark-950 text-white flex justify-between items-center" style="padding:16px;">
                <div class="flex items-center gap-2">
                    <div class="bg-secondary-terra-700 rounded-full" style="width:10px; height:10px;"></div>
                    <h3 class="m-0 font-semibold font-cabinet" style="font-size:16px;">{{ $chatTitle }}</h3>
                </div>
                <button onclick="toggleAiChat()" class="bg-transparent border-none text-white cursor-pointer" style="padding:4px;">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13 1L1 13M1 1L13 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            
            <!-- Messages Area -->
            <div id="chat-messages-area" class="flex-grow overflow-y-auto flex flex-col gap-3" style="padding:16px; background-color:#f9f9f9;">
                <!-- Initial AI Message -->
                <div class="self-start bg-white shadow-sm border border-gray-200 flex-shrink-0" style="padding:12px 16px; border-radius:12px 12px 12px 0; max-width:85%;">
                    <p class="m-0 text-gray-800 font-cabinet" style="font-size:14px; line-height:1.5;">{{ $aiWelcomeMsg }}</p>
                </div>

                <!-- Suggestion Menu -->
                <div id="ai-suggestion-chips" class="self-start bg-white shadow-sm border border-gray-200 mt-2 flex-shrink-0" style="border-radius:12px; max-width:85%; overflow:hidden; width:100%;">
                    <div style="padding:12px 16px; border-bottom: 1px solid #e5e7eb; background-color:#fafafa;">
                        <p class="m-0 text-gray-600 font-cabinet" style="font-size:13px;">
                            {{ $lang === 'id' ? 'Pilih topik yang Anda cari:' : 'Choose what you are interested in:' }}
                        </p>
                    </div>
                    <div style="display: flex; flex-direction: column;">
                        @php
                            $suggestions = $lang === 'id' 
                                ? ['Cari piring keramik', 'Rekomendasi gelas', 'Hiasan meja kayu', 'Koleksi fabric']
                                : ['Search ceramic plates', 'Glass recommendations', 'Wooden table decor', 'Fabric collection'];
                        @endphp
                        @foreach($suggestions as $index => $sug)
                            <div onclick="sendSuggestion('{{ $sug }}')" class="font-cabinet cursor-pointer transition-colors text-primary-dark-950 hover:bg-gray-50 hover:text-primary-dark-900" style="padding:14px 16px; font-size:14px; font-weight:500; {{ $index !== count($suggestions) - 1 ? 'border-bottom: 1px solid #e5e7eb;' : '' }} width: 100%; display: block; text-align: left; box-sizing: border-box;">
                                {{ $sug }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Input Area -->
            <div class="bg-white border-t border-gray-100 flex gap-2" style="padding:16px;">
                <input type="text" id="chat-input" placeholder="{{ $placeholderTxt }}" class="flex-grow border border-gray-300 font-cabinet outline-none focus:border-primary-dark-950" style="padding:10px 16px; border-radius:24px; font-size:14px;" onkeypress="handleKeyPress(event)">
                <button id="chat-send-btn" onclick="sendMessage()" class="bg-primary-dark-950 text-white border-none rounded-full flex justify-center items-center cursor-pointer hover:opacity-90" style="width:40px; height:40px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22 2L11 13M22 2L15 22L11 13M11 13L2 9L22 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Float Button -->
        <button onclick="toggleAiChat()" id="ai-chat-btn" class="bg-primary-dark-950 text-white border-none rounded-full shadow-lg cursor-pointer flex justify-center items-center hover:scale-110 transition-transform duration-300" style="width:60px; height:60px;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="9" cy="10" r="1" fill="currentColor"/>
                <circle cx="15" cy="10" r="1" fill="currentColor"/>
                <path d="M9 15C9 15 10.5 17 12 17C13.5 17 15 15 15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>

    <script>
        let chatHistory = [];

        document.addEventListener('DOMContentLoaded', function() {
            appendWABubble(false);
        });

        function toggleAiChat() {
            const window = document.getElementById('ai-chat-window');
            if (window.classList.contains('opacity-0')) {
                window.classList.remove('hidden');
                window.style.display = 'flex';
                setTimeout(() => {
                    window.classList.remove('opacity-0', 'pointer-events-none');
                    window.classList.add('opacity-100', 'pointer-events-auto');
                    window.style.transform = 'translateY(0)';
                }, 10);
            } else {
                window.classList.remove('opacity-100', 'pointer-events-auto');
                window.classList.add('opacity-0', 'pointer-events-none');
                window.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    window.style.display = 'none';
                    window.classList.add('hidden');
                }, 300);
            }
        }

        function sendSuggestion(text) {
            // Sembunyikan chips agar tidak menumpuk
            const chips = document.getElementById('ai-suggestion-chips');
            if (chips) {
                chips.style.display = 'none';
            }

            // Masukkan teks ke input dan otomatis kirim
            const input = document.getElementById('chat-input');
            input.value = text;
            sendMessage();
        }

        function handleKeyPress(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        }

        function appendMessage(role, text) {
            const messagesArea = document.getElementById('chat-messages-area');
            const msgDiv = document.createElement('div');
            
            // Mengubah markdown URL sederhana ke tag <a>
            // Match format [text](url)
            const formattedText = text.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" style="color: blue; text-decoration: underline;">$1</a>');

            if (role === 'user') {
                msgDiv.className = 'self-end bg-primary-dark-950 shadow-sm flex-shrink-0';
                msgDiv.style.cssText = 'padding:12px 16px; border-radius:12px 12px 0 12px; max-width:85%;';
                msgDiv.innerHTML = `<p class="m-0 text-white font-cabinet" style="font-size:14px; line-height:1.5;">${formattedText}</p>`;
            } else {
                msgDiv.className = 'self-start bg-white shadow-sm border border-gray-200 flex-shrink-0';
                msgDiv.style.cssText = 'padding:12px 16px; border-radius:12px 12px 12px 0; max-width:85%;';
                msgDiv.innerHTML = `<p class="m-0 text-gray-800 font-cabinet" style="font-size:14px; line-height:1.5;">${formattedText}</p>`;
            }
            
            messagesArea.appendChild(msgDiv);
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }

        function appendTypingIndicator() {
            const messagesArea = document.getElementById('chat-messages-area');
            const msgDiv = document.createElement('div');
            msgDiv.id = 'typing-indicator';
            msgDiv.className = 'self-start bg-white shadow-sm border border-gray-200';
            msgDiv.style.cssText = 'padding:12px 16px; border-radius:12px 12px 12px 0; max-width:85%;';
            msgDiv.innerHTML = `
                <div style="display:flex; align-items:center; gap:4px; height:21px;">
                    <span style="width:6px; height:6px; background-color:#9ca3af; border-radius:50%; display:inline-block; animation:chatbotBounce 1.4s infinite ease-in-out both; animation-delay:-0.32s;"></span>
                    <span style="width:6px; height:6px; background-color:#9ca3af; border-radius:50%; display:inline-block; animation:chatbotBounce 1.4s infinite ease-in-out both; animation-delay:-0.16s;"></span>
                    <span style="width:6px; height:6px; background-color:#9ca3af; border-radius:50%; display:inline-block; animation:chatbotBounce 1.4s infinite ease-in-out both;"></span>
                </div>
                <style>
                @keyframes chatbotBounce {
                  0%, 80%, 100% { transform: scale(0); }
                  40% { transform: scale(1); }
                }
                </style>
            `;
            messagesArea.appendChild(msgDiv);
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }

        function removeTypingIndicator() {
            const indicator = document.getElementById('typing-indicator');
            if (indicator) {
                indicator.remove();
            }
        }

        function appendProducts(products, categoryInfo = null) {
            console.log('Chatbot received products:', products, 'categoryInfo:', categoryInfo);
            if (!products || products.length === 0) return;

            const currentLang = '{{ session("lang", "en") }}';
            const cartBtnTxt = currentLang === 'id' ? '+ Keranjang' : '+ Add to Cart';
            const seeAllTxt = currentLang === 'id' ? 'Lihat semua' : 'See all';

            try {
                const messagesArea = document.getElementById('chat-messages-area');
                
                // Container utama
                const wrapperDiv = document.createElement('div');
                wrapperDiv.className = 'self-start w-full mb-3';
                wrapperDiv.style.cssText = 'max-width:90%;';

                // Container scrollable untuk produk
                const msgDiv = document.createElement('div');
                msgDiv.className = 'w-full overflow-x-auto flex flex-shrink-0 gap-3 pb-2';
                msgDiv.style.cssText = 'padding-bottom:8px; min-height:240px;';

                let html = '';
                products.forEach(p => {
                    const priceFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(p.price || 0);
                    html += `
                    <div class="flex-shrink-0 bg-white shadow border border-gray-100 flex flex-col justify-between" style="width:160px; border-radius:8px; overflow:hidden;">
                        <a href="${p.url}" target="_blank" class="block bg-gray-100" style="height:120px; overflow:hidden;">
                            <img src="${p.image}" alt="${p.name}" class="w-full h-full object-cover">
                        </a>
                        <div style="padding:10px;">
                            <a href="${p.url}" target="_blank" class="text-gray-800 font-cabinet font-semibold text-xs leading-tight block mb-1 hover:text-primary-dark-950" style="text-decoration:none; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                ${p.name}
                            </a>
                            <p class="text-secondary-terra-700 font-bold m-0" style="font-size:12px;">${priceFormatted}</p>
                        </div>
                        <div style="padding:0 10px 10px;">
                            <button onclick="addChatProductToCart('${p.detail_id}')" class="w-full bg-primary-dark-950 text-white border-none cursor-pointer font-cabinet rounded hover:opacity-90" style="padding:6px 0; font-size:11px;">
                                ${cartBtnTxt}
                            </button>
                        </div>
                    </div>`;
                });

                msgDiv.innerHTML = html;
                wrapperDiv.appendChild(msgDiv);
                
                // Tambahkan tombol Lihat Kategori jika ada
                if (categoryInfo && categoryInfo.url && categoryInfo.name) {
                    const btnDiv = document.createElement('div');
                    btnDiv.className = 'mt-2';
                    btnDiv.innerHTML = `
                        <a href="${categoryInfo.url}" target="_blank" class="inline-block bg-[#E8E4DF] text-gray-800 font-cabinet font-semibold text-xs rounded shadow-sm hover:bg-[#d8d4cf] transition-colors" style="padding:8px 16px; text-decoration:none;">
                            ${seeAllTxt} ${categoryInfo.name} &rarr;
                        </a>
                    `;
                    wrapperDiv.appendChild(btnDiv);
                }

                messagesArea.appendChild(wrapperDiv);
                messagesArea.scrollTop = messagesArea.scrollHeight;
            } catch (error) {
                console.error('Error rendering product cards:', error);
            }
        }

        function showChatbotToast(message, type = 'success') {
            const existing = document.getElementById('chatbot-toast');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.id = 'chatbot-toast';
            toast.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 z-[10000] max-w-sm w-[90%] transition-all duration-300 ease-out translate-y-0 opacity-100';

            if (type === 'success') {
                toast.innerHTML = `
                <div class="bg-[#E8F0F7] border-l-4 border-[#8CA5CE] p-4 rounded-lg shadow-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-[#8CA5CE]" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-[#6F86BC]">Success</p>
                            <p class="mt-1 text-sm text-[#6F86BC]">${message}</p>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <button onclick="document.getElementById('chatbot-toast').remove()" class="inline-flex text-[#8CA5CE] hover:text-[#6F86BC] focus:outline-none transition">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>`;
            } else {
                toast.innerHTML = `
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16M8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-red-800">Error</p>
                            <p class="mt-1 text-sm text-red-700">${message}</p>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <button onclick="document.getElementById('chatbot-toast').remove()" class="inline-flex text-red-400 hover:text-red-600 focus:outline-none transition">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>`;
            }

            document.body.appendChild(toast);
            setTimeout(() => {
                if (toast) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translate(-50%, -20px)';
                    setTimeout(() => toast.remove(), 300);
                }
            }, 3000);
        }

        async function addChatProductToCart(detailId) {
            if (!detailId) {
                showChatbotToast('Detail produk tidak ditemukan.', 'error');
                return;
            }

            try {
                let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                const response = await fetch("{{ route('frontend.product.addToCart') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        detail_product_id: detailId,
                        qty: 1
                    })
                });

                const data = await response.json();
                if (response.ok && data.status === 200) {
                    showChatbotToast('Produk berhasil ditambahkan ke keranjang!', 'success');
                    // Jika ada event cart update di navbar, bisa di trigger di sini
                    if(typeof window.getCartData === 'function') window.getCartData();
                } else {
                    showChatbotToast(data.message || 'Gagal menambahkan produk, mungkin butuh login.', 'error');
                }
            } catch (err) {
                showChatbotToast('Gagal menghubungkan ke server.', 'error');
            }
        }

        function appendWABubble(isError = false) {
            const messagesArea = document.getElementById('chat-messages-area');
            const msgDiv = document.createElement('div');
            msgDiv.className = 'self-start w-full flex flex-col gap-2 pb-2 mt-2 flex-shrink-0';
            msgDiv.style.cssText = 'max-width:85%;';

            const currentLang = '{{ session("lang", "en") }}';
            let textMsg = '';
            
            if (isError) {
                textMsg = currentLang === 'id' 
                    ? 'Maaf, terjadi kesalahan koneksi atau server sedang sibuk. Jangan ragu untuk menghubungi Customer Service kami melalui WhatsApp ya!'
                    : 'Sorry, a connection error occurred or the server is busy. Feel free to contact our Customer Service via WhatsApp!';
            } else {
                textMsg = currentLang === 'id'
                    ? 'Silakan klik tombol di bawah ini untuk terhubung langsung dengan Customer Service kami:'
                    : 'Please click the button below to connect directly with our Customer Service:';
            }
                
            const chatBtnTxt = currentLang === 'id' ? 'Hubungi CS via WhatsApp' : 'Contact CS via WhatsApp';

            msgDiv.innerHTML = `
                <div class="bg-white shadow-sm border border-gray-200" style="padding:12px 16px; border-radius:12px 12px 12px 0;">
                    <p class="m-0 text-gray-800 font-cabinet" style="font-size:14px; line-height:1.5;">${textMsg}</p>
                    <a href="https://wa.me/6285117078163" target="_blank" class="mt-3 flex items-center justify-center gap-2 text-white border-none rounded-lg font-cabinet font-semibold transition-colors" style="background-color:#25D366; padding:10px 14px; text-decoration:none; font-size:13px; box-shadow: 0 2px 4px rgba(37,211,102,0.2);" onmouseover="this.style.backgroundColor='#128C7E'" onmouseout="this.style.backgroundColor='#25D366'">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.888-.788-1.489-1.761-1.663-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.575-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.98 1.005-3.645-.235-.373a9.86 9.86 0 01-1.51-5.26c0-5.445 4.439-9.884 9.888-9.884 2.64 0 5.122 1.029 6.988 2.898a9.825 9.825 0 012.893 6.994c-.002 5.448-4.441 9.882-9.89 9.882m8.413-18.297A11.815 11.815 0 0012.052 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        ${chatBtnTxt}
                    </a>
                </div>
            `;
            messagesArea.appendChild(msgDiv);
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }

        function appendErrorWithWA() {
            appendWABubble(true);
        }

        async function sendMessage() {
            const input = document.getElementById('chat-input');
            const text = input.value.trim();
            if (!text) return;

            // Clear input
            input.value = '';

            // Append user message UI
            appendMessage('user', text);

            // Add to history
            chatHistory.push({ role: 'user', content: text });

            // Show typing...
            appendTypingIndicator();

            try {
                // Buat tag meta csrf jika tidak ada di head
                let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                const response = await fetch("{{ route('frontend.chatbot.message') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ messages: chatHistory })
                });

                const data = await response.json();
                removeTypingIndicator();

                if (response.ok && data.reply) {
                    // Bersihkan JSON yang bocor dari halusinasi AI
                    let cleanReply = data.reply.replace(/\{"keyword".*?\}/g, '').trim();
                    cleanReply = cleanReply.replace(/<function.*?>.*?<\/function>/gs, '').trim();

                    // Cek apakah AI menyebutkan nomor WA
                    const hasWA = cleanReply.includes('085117078163');
                    if (hasWA) {
                        cleanReply = cleanReply.replace(/085117078163/g, '').trim();
                    }

                    appendMessage('assistant', cleanReply);
                    chatHistory.push({ role: 'assistant', content: cleanReply });
                    
                    if (data.products && data.products.length > 0) {
                        appendProducts(data.products, data.category_info);
                    }

                    if (hasWA) {
                        appendWABubble(false);
                    }
                } else {
                    appendErrorWithWA();
                }
            } catch (error) {
                removeTypingIndicator();
                appendErrorWithWA();
                console.error('Chatbot Error:', error);
            }
        }
    </script>

    @yield('script')
</body>

</html>
