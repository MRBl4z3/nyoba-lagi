<?php
// =================================================================
// APLIKASI MANAJEMEN INVENTARIS (DEMO UNTUK VERCEL)
// Dibuat dengan PHP, terinspirasi oleh Laravel.
//
// Oleh: Gemini
//
// INSTRUKSI DEPLOY KE VERCEL:
// 1. Buat proyek baru di Vercel dan hubungkan ke repository Git Anda.
// 2. Buat file `vercel.json` di root direktori Anda dengan konten di bawah ini.
// 3. Buat direktori `api` dan pindahkan file `index.php` ini ke dalamnya.
//    (Struktur: /api/index.php)
// 4. Push ke repository Anda. Vercel akan otomatis men-deploy.
//
// KONTEN UNTUK `vercel.json`:
/*
{
  "version": 2,
  "builds": [
    {
      "src": "api/index.php",
      "use": "@vercel/php"
    }
  ],
  "routes": [
    {
      "src": "/(.*)",
      "dest": "/api/index.php"
    }
  ]
}
*/
// =================================================================

// =================================================================
// BAGIAN 1: DATA MOCK (TIRUAN DATABASE)
// Dalam aplikasi Laravel sungguhan, ini akan berasal dari Model (e.g., Product::all()).
// =================================================================

function getMockData() {
    return [
        'products' => [
            ['id' => 1, 'sku' => 'APL-IP15-PRO', 'name' => 'Apple iPhone 15 Pro', 'category' => 'Elektronik', 'stock' => 25, 'price' => 18500000, 'supplier' => 'Distributor Apple Resmi'],
            ['id' => 2, 'sku' => 'SMSG-S24-ULT', 'name' => 'Samsung Galaxy S24 Ultra', 'category' => 'Elektronik', 'stock' => 15, 'price' => 21000000, 'supplier' => 'Distributor Samsung Resmi'],
            ['id' => 3, 'sku' => 'SNY-WH1000XM5', 'name' => 'Sony WH-1000XM5 Headphones', 'category' => 'Aksesoris', 'stock' => 40, 'price' => 4500000, 'supplier' => 'Sony Indonesia'],
            ['id' => 4, 'sku' => 'DELL-XPS15-9530', 'name' => 'Dell XPS 15 Laptop', 'category' => 'Komputer', 'stock' => 8, 'price' => 32000000, 'supplier' => 'Dell Official Store'],
            ['id' => 5, 'sku' => 'LGT-MXM3S', 'name' => 'Logitech MX Master 3S Mouse', 'category' => 'Aksesoris', 'stock' => 3, 'price' => 1600000, 'supplier' => 'Logitech Store'],
            ['id' => 6, 'sku' => 'NKE-AF1-WHT', 'name' => 'Nike Air Force 1 \'07', 'category' => 'Fashion', 'stock' => 50, 'price' => 1500000, 'supplier' => 'Nike Indonesia'],
        ],
        'orders' => [
            ['id' => 'ORD-001', 'customer' => 'Budi Santoso', 'date' => '2025-07-15', 'items' => 2, 'total' => 37000000, 'status' => 'Terkirim'],
            ['id' => 'ORD-002', 'customer' => 'Citra Lestari', 'date' => '2025-07-16', 'items' => 1, 'total' => 4500000, 'status' => 'Diproses'],
            ['id' => 'ORD-003', 'customer' => 'Ahmad Dahlan', 'date' => '2025-07-17', 'items' => 3, 'total' => 4700000, 'status' => 'Menunggu Pembayaran'],
            ['id' => 'ORD-004', 'customer' => 'Dewi Anggraini', 'date' => '2025-07-18', 'items' => 1, 'total' => 21000000, 'status' => 'Diproses'],
        ]
    ];
}

// =================================================================
// BAGIAN 2: LOGIKA & KONTROLER
// Dalam Laravel, ini akan berada di dalam file Controller (e.g., DashboardController.php).
// =================================================================

function handleRequest() {
    // Simulasi mendapatkan peran pengguna. Di aplikasi nyata, ini dari session/auth.
    $role = $_GET['role'] ?? 'admin';
    $data = getMockData();

    // Logika untuk statistik dashboard
    $stats = [
        'total_products' => count($data['products']),
        'low_stock_count' => count(array_filter($data['products'], fn($p) => $p['stock'] < 10)),
        'pending_orders' => count(array_filter($data['orders'], fn($o) => $o['status'] === 'Diproses')),
        'total_stock_value' => array_reduce($data['products'], fn($carry, $p) => $carry + ($p['stock'] * $p['price']), 0)
    ];

    // Mengirim data ke view untuk dirender
    renderPage($role, $data, $stats);
}

// =================================================================
// BAGIAN 3: TAMPILAN (VIEWS)
// Dalam Laravel, ini akan menjadi file-file Blade terpisah (e.g., dashboard.blade.php).
// =================================================================

function renderPage($role, $data, $stats) {
    // Helper untuk status badge
    $status_classes = [
        'Terkirim' => 'bg-green-100 text-green-800',
        'Diproses' => 'bg-blue-100 text-blue-800',
        'Menunggu Pembayaran' => 'bg-yellow-100 text-yellow-800',
        'Dibatalkan' => 'bg-red-100 text-red-800',
    ];

    // Helper untuk stok badge
    function getStockBadge($stock) {
        if ($stock < 10) return 'bg-red-100 text-red-800';
        if ($stock < 20) return 'bg-yellow-100 text-yellow-800';
        return 'bg-green-100 text-green-800';
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Manajer Inventaris</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar { transition: transform 0.3s ease-in-out; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar fixed md:relative z-20 w-64 bg-gray-800 text-white flex flex-col">
            <div class="p-4 text-2xl font-bold border-b border-gray-700">
                Inventaris Pro
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="#" class="flex items-center px-4 py-2 rounded bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" /></svg>
                    Dashboard
                </a>
                <?php if (in_array($role, ['admin', 'staf_gudang'])): ?>
                <a href="#produk" class="flex items-center px-4 py-2 rounded hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor"><path d="M5 8a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" /><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v1a1 1 0 01-1 1H4a1 1 0 01-1-1V3zm2 5a3 3 0 013-3h4a3 3 0 013 3v6a3 3 0 01-3 3H8a3 3 0 01-3-3V8z" clip-rule="evenodd" /></svg>
                    Produk
                </a>
                <?php endif; ?>
                <?php if (in_array($role, ['admin', 'staf_penjualan'])): ?>
                <a href="#pesanan" class="flex items-center px-4 py-2 rounded hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" /><path fill-rule="evenodd" d="M4 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h4a1 1 0 100-2H7zm0 4a1 1 0 100 2h4a1 1 0 100-2H7z" clip-rule="evenodd" /></svg>
                    Pesanan
                </a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-md p-4 flex justify-between items-center">
                <button id="menu-btn" class="md:hidden text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
                <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
                <div>
                    <form method="GET" class="flex items-center space-x-2">
                        <label for="role" class="text-sm font-medium text-gray-600">Anda masuk sebagai:</label>
                        <select name="role" id="role" onchange="this.form.submit()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="admin" <?= $role == 'admin' ? 'selected' : '' ?>>Admin / Manajer</option>
                            <option value="staf_gudang" <?= $role == 'staf_gudang' ? 'selected' : '' ?>>Staf Gudang</option>
                            <option value="staf_penjualan" <?= $role == 'staf_penjualan' ? 'selected' : '' ?>>Staf Penjualan</option>
                        </select>
                    </form>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <!-- Stat Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-500">Total Produk</h3>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?= $stats['total_products'] ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-500">Stok Rendah (< 10)</h3>
                        <p class="text-3xl font-bold text-red-600 mt-2"><?= $stats['low_stock_count'] ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-500">Pesanan Diproses</h3>
                        <p class="text-3xl font-bold text-blue-600 mt-2"><?= $stats['pending_orders'] ?></p>
                    </div>
                    <?php if ($role === 'admin'): ?>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-500">Nilai Total Stok</h3>
                        <p class="text-3xl font-bold text-green-600 mt-2">Rp <?= number_format($stats['total_stock_value'], 0, ',', '.') ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Daftar Produk -->
                <?php if (in_array($role, ['admin', 'staf_gudang'])): ?>
                <div id="produk" class="bg-white p-6 rounded-lg shadow mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Daftar Produk</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">SKU</th>
                                    <th scope="col" class="px-6 py-3">Nama Produk</th>
                                    <th scope="col" class="px-6 py-3">Kategori</th>
                                    <th scope="col" class="px-6 py-3 text-center">Stok</th>
                                    <th scope="col" class="px-6 py-3 text-right">Harga Jual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['products'] as $product): ?>
                                <tr class="bg-white border-b">
                                    <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($product['sku']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($product['name']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($product['category']) ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-1 font-semibold leading-tight rounded-full <?= getStockBadge($product['stock']) ?>">
                                            <?= $product['stock'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Daftar Pesanan -->
                <?php if (in_array($role, ['admin', 'staf_penjualan'])): ?>
                <div id="pesanan" class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Pesanan Terbaru</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">ID Pesanan</th>
                                    <th scope="col" class="px-6 py-3">Pelanggan</th>
                                    <th scope="col" class="px-6 py-3">Tanggal</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="px-6 py-3 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['orders'] as $order): ?>
                                <tr class="bg-white border-b">
                                    <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($order['id']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($order['customer']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($order['date']) ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 font-semibold leading-tight rounded-full <?= $status_classes[$order['status']] ?? '' ?>">
                                            <?= htmlspecialchars($order['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">Rp <?= number_format($order['total'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script>
        // Script untuk toggle sidebar mobile
        const menuBtn = document.getElementById('menu-btn');
        const sidebar = document.getElementById('sidebar');
        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    </script>
</body>
</html>
<?php
}

// =================================================================
// BAGIAN 4: ROUTER SEDERHANA
// Dalam Laravel, ini ditangani oleh `routes/web.php`.
// =================================================================
handleRequest();
?>
