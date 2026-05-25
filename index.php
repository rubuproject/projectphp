<?php
// Load products from JSON
$productsJson = file_get_contents('products.json');
$products = json_decode($productsJson, true);

// Generate placeholder images
$placeholderColors = ['FF6B6B', '4ECDC4', '45B7D1', '96CEB4', 'FFEAA7', 'DDA0DD', '98D8C8', 'F7DC6F', 'BB8FCE', '85C1E9'];
$imageIndex = 0;

// Filter logic
$filteredProducts = $products;

// Search filter
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = strtolower($_GET['search']);
    $filteredProducts = array_filter($filteredProducts, function($product) use ($search) {
        return strpos(strtolower($product['nama']), $search) !== false;
    });
}

// Price filter
if (isset($_GET['min']) && $_GET['min'] !== '') {
    $filteredProducts = array_filter($filteredProducts, function($product) {
        return $product['harga'] >= $_GET['min'];
    });
}
if (isset($_GET['max']) && $_GET['max'] !== '') {
    $filteredProducts = array_filter($filteredProducts, function($product) {
        return $product['harga'] <= $_GET['max'];
    });
}

// Rating filter
if (isset($_GET['rating']) && $_GET['rating'] !== '') {
    $filteredProducts = array_filter($filteredProducts, function($product) {
        return $product['rating'] >= $_GET['rating'];
    });
}

// Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
switch($sort) {
    case 'terendah':
        usort($filteredProducts, function($a, $b) {
            return $a['harga'] - $b['harga'];
        });
        break;
    case 'tertinggi':
        usort($filteredProducts, function($a, $b) {
            return $b['harga'] - $a['harga'];
        });
        break;
    case 'terbaru':
        usort($filteredProducts, function($a, $b) {
            return $b['id'] - $a['id'];
        });
        break;
    default:
        usort($filteredProducts, function($a, $b) {
            return $b['id'] - $a['id'];
        });
}

// Pagination
$perPage = 10;
$totalProducts = count($filteredProducts);
$totalPages = ceil($totalProducts / $perPage);
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages));
$offset = ($currentPage - 1) * $perPage;
$paginatedProducts = array_slice($filteredProducts, $offset, $perPage);

// Reset array keys
$paginatedProducts = array_values($paginatedProducts);
$filteredProducts = array_values($filteredProducts);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEPATUKU - Marketplace Sepatu Terlengkap</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Loading Animation -->
    <div class="loading" id="loading">
        <div class="spinner"></div>
    </div>

    <!-- Header -->
    <header class="header" id="header">
        <div class="header-container">
            <div class="logo">
                <h1>SEPATUKU</h1>
                <span class="logo-sub">Marketplace</span>
            </div>
            
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Cari sepatu favoritmu..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="search-btn" onclick="performSearch()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </div>
            
            <div class="header-icons">
                <button class="icon-btn" title="Keranjang">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                        <path d="M3 6h18"></path>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span class="badge">3</span>
                </button>
                <button class="icon-btn" title="Profil">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <form id="filterForm" method="GET" action="">
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Metode Pembayaran</h3>
                    <label class="checkbox-label">
                        <input type="checkbox" name="cod" value="1" class="checkbox-input">
                        <span class="checkmark"></span>
                        COD (Bayar di Tempat)
                    </label>
                </div>

                <div class="sidebar-section">
                    <h3 class="sidebar-title">Opsi Pengiriman</h3>
                    <label class="checkbox-label">
                        <input type="checkbox" name="shipping[]" value="same_day" class="checkbox-input">
                        <span class="checkmark"></span>
                        Same Day
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="shipping[]" value="regular" class="checkbox-input">
                        <span class="checkmark"></span>
                        Reguler
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="shipping[]" value="hemat" class="checkbox-input">
                        <span class="checkmark"></span>
                        Hemat
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="shipping[]" value="next_day" class="checkbox-input">
                        <span class="checkmark"></span>
                        Next Day
                    </label>
                </div>

                <div class="sidebar-section">
                    <h3 class="sidebar-title">Program Promo</h3>
                    <label class="checkbox-label">
                        <input type="checkbox" name="promo[]" value="xtra" class="checkbox-input">
                        <span class="checkmark"></span>
                        Promo XTRA
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="promo[]" value="gratis_ongkir" class="checkbox-input">
                        <span class="checkmark"></span>
                        Gratis Ongkir
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="promo[]" value="cashback" class="checkbox-input">
                        <span class="checkmark"></span>
                        Cashback
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="promo[]" value="diskon" class="checkbox-input">
                        <span class="checkmark"></span>
                        Diskon
                    </label>
                </div>

                <div class="sidebar-section">
                    <h3 class="sidebar-title">Batas Harga</h3>
                    <div class="price-inputs">
                        <input type="number" name="min" class="price-input" placeholder="Min" value="<?php echo isset($_GET['min']) ? htmlspecialchars($_GET['min']) : ''; ?>">
                        <span class="price-separator">-</span>
                        <input type="number" name="max" class="price-input" placeholder="Max" value="<?php echo isset($_GET['max']) ? htmlspecialchars($_GET['max']) : ''; ?>">
                    </div>
                    <button type="submit" class="btn-apply">PAKAI</button>
                </div>

                <div class="sidebar-section">
                    <h3 class="sidebar-title">Rating</h3>
                    <div class="rating-options">
                        <?php for($i = 5; $i >= 1; $i--): ?>
                        <label class="rating-label">
                            <input type="radio" name="rating" value="<?php echo $i; ?>" <?php echo (isset($_GET['rating']) && $_GET['rating'] == $i) ? 'checked' : ''; ?>>
                            <span class="stars">
                                <?php for($j = 0; $j < $i; $j++): ?>
                                ★
                                <?php endfor; ?>
                                <?php for($j = $i; $j < 5; $j++): ?>
                                ☆
                                <?php endfor; ?>
                            </span>
                            <span class="rating-text">ke atas</span>
                        </label>
                        <?php endfor; ?>
                    </div>
                </div>
            </form>
        </aside>

        <!-- Content -->
        <div class="content">
            <!-- Tabs -->
            <div class="tabs-container">
                <div class="tabs">
                    <button class="tab">Untukmu</button>
                    <button class="tab active">Terlaris</button>
                    <button class="tab">Terbaru</button>
                    <button class="tab">Diskon</button>
                </div>
                <select class="sort-select" onchange="sortProducts(this.value)">
                    <option value="">Urutkan</option>
                    <option value="terendah" <?php echo $sort == 'terendah' ? 'selected' : ''; ?>>Harga Terendah</option>
                    <option value="tertinggi" <?php echo $sort == 'tertinggi' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                    <option value="terbaru" <?php echo $sort == 'terbaru' ? 'selected' : ''; ?>>Terbaru</option>
                </select>
            </div>

            <!-- Products Grid -->
            <div class="products-grid">
                <?php if(empty($paginatedProducts)): ?>
                <div class="no-results">
                    <p>Tidak ada produk yang ditemukan</p>
                </div>
                <?php else: ?>
                <?php foreach($paginatedProducts as $index => $product): 
                    $discount = $product['harga_lama'] > $product['harga'] ? round((($product['harga_lama'] - $product['harga']) / $product['harga_lama']) * 100) : 0;
                ?>
                <div class="product-card" data-id="<?php echo $product['id']; ?>">
                    <div class="product-image">
                        <div class="placeholder-img" style="background: linear-gradient(135deg, #<?php echo $placeholderColors[$product['id'] % count($placeholderColors)]; ?>, #<?php echo $placeholderColors[($product['id'] + 1) % count($placeholderColors)]; ?>);">
                            <span class="placeholder-text"><?php echo substr($product['nama'], 0, 20); ?></span>
                        </div>
                        <?php if($discount > 0): ?>
                        <span class="discount-badge">-<?php echo $discount; ?>%</span>
                        <?php endif; ?>
                        <button class="wishlist-btn" onclick="toggleWishlist(this, <?php echo $product['id']; ?>)">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($product['nama']); ?></h3>
                        <div class="product-price">
                            <span class="current-price">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></span>
                            <?php if($product['harga_lama'] > $product['harga']): ?>
                            <span class="old-price">Rp <?php echo number_format($product['harga_lama'], 0, ',', '.'); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-meta">
                            <span class="rating">★ <?php echo $product['rating']; ?></span>
                            <span class="location"><?php echo htmlspecialchars($product['lokasi']); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if($totalPages > 1): ?>
            <div class="pagination">
                <?php if($currentPage > 1): ?>
                <a href="?page=<?php echo $currentPage - 1; ?>&search=<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>&min=<?php echo isset($_GET['min']) ? $_GET['min'] : ''; ?>&max=<?php echo isset($_GET['max']) ? $_GET['max'] : ''; ?>&sort=<?php echo $sort; ?>" class="page-btn">&laquo;</a>
                <?php endif; ?>
                
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                
                if($startPage > 1): ?>
                <a href="?page=1&search=<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>&min=<?php echo isset($_GET['min']) ? $_GET['min'] : ''; ?>&max=<?php echo isset($_GET['max']) ? $_GET['max'] : ''; ?>&sort=<?php echo $sort; ?>" class="page-btn">1</a>
                <?php if($startPage > 2): ?>
                <span class="page-dots">...</span>
                <?php endif; endif; ?>
                
                <?php for($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>&min=<?php echo isset($_GET['min']) ? $_GET['min'] : ''; ?>&max=<?php echo isset($_GET['max']) ? $_GET['max'] : ''; ?>&sort=<?php echo $sort; ?>" class="page-btn <?php echo $i == $currentPage ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if($endPage < $totalPages): ?>
                <?php if($endPage < $totalPages - 1): ?>
                <span class="page-dots">...</span>
                <?php endif; ?>
                <a href="?page=<?php echo $totalPages; ?>&search=<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>&min=<?php echo isset($_GET['min']) ? $_GET['min'] : ''; ?>&max=<?php echo isset($_GET['max']) ? $_GET['max'] : ''; ?>&sort=<?php echo $sort; ?>" class="page-btn"><?php echo $totalPages; ?></a>
                <?php endif; ?>
                
                <?php if($currentPage < $totalPages): ?>
                <a href="?page=<?php echo $currentPage + 1; ?>&search=<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>&min=<?php echo isset($_GET['min']) ? $_GET['min'] : ''; ?>&max=<?php echo isset($_GET['max']) ? $_GET['max'] : ''; ?>&sort=<?php echo $sort; ?>" class="page-btn">&raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="script.js"></script>
</body>
</html>