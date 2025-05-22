<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>
        Toko Mas Logam Mulia
    </title>
    <script src="https://cdn.tailwindcss.com">
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display&family=Roboto&display=swap"
        rel="stylesheet" />
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        .font-playfair {
            font-family: 'Playfair Display', serif;
        }

        .vertical-text {
            writing-mode: vertical-rl;
            text-orientation: mixed;
        }

        @media print {
            @page {
                size: 255mm 140mm;
                /* Lebar x Tinggi */
                margin: 0;
                /* Optional, bisa diatur sesuai layout */
                padding: 0;
            }

            body {
                font-size: 12pt;
                color: black;
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body class="flex justify-center">
    {{-- Desain Nota --}}
    <div id="area" class="max-w-5xl w-full relative">
        <!-- Header -->
        <div class="flex justify-between items-center bg-[#000000] px-6 py-4">
            <div class="flex items-center space-x-3">
                <img alt="Gold colored logo icon of Toko Mas Logam Mulia" class="w-16 h-12 object-contain"
                    src="{{ asset('logo-cetak.png') }}" width="100" />
                <div>
                    <p class="text-[#C89A35] font-playfair text-2xl leading-none tracking-wide">
                        TOKO MAS
                    </p>
                    <p class="text-[#C89A35] font-playfair text-3xl leading-none tracking-wide -mt-1">
                        LOGAM MULIA
                    </p>
                </div>
            </div>
            <div class="flex items-end space-x-6 text-white text-sm font-light">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-map-marker-alt text-white">
                    </i>
                    <span>
                        Jl. Mesumai Pasar Bawah Bangko
                    </span>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <i class="fab fa-instagram text-white">
                        </i>
                        <span>
                            logammulia.bangko
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fab fa-whatsapp text-white">
                        </i>
                        <span>
                            0852-6666-9064 (Fajri)
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Form content -->
        <div class="flex border border-[#daa520] border-t-0">
            <!-- Vertical text left -->
            <div class="border-r  w-16 flex justify-center items-center bg-[#fff7e1]">
                <p class="text-[#daa520] text-lg font-bold" style="
                     writing-mode: vertical-rl;
                     text-orientation: mixed;
                     transform: rotate(180deg);
                     letter-spacing: 0.2rem;
                     font-family: 'Great Vibes', cursive;
                   ">
                    Berhias Sambil Menabung
                </p>
            </div>
            <!-- Main form -->
            @yield('content')
        </div>
    </div>
    </div>
    {{-- Desain Nota --}}
    <script>
        window.addEventListener('load', function () {
        window.print();
        window.onafterprint = function () {
          history.back();
        };
      });
    </script>
</body>


</html>