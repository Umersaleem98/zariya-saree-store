<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saree Store - Admin Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed: 70px;
            --primary: #c2185b;
            --primary-light: #fce4ec;
            --secondary: #d4a017;
            --dark: #1a1d29;
            --sidebar-bg: #1e2235;
            --sidebar-hover: #252a40;
            --body-bg: #f4f6f9;
            --card-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }

        * { font-family: 'Inter', sans-serif; }

        body {
            background-color: var(--body-bg);
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            background: var(--sidebar-bg);
            color: #a0a3bd;
            transition: all 0.3s ease;
            z-index: 1040;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .sidebar-brand {
            color: white;
            font-size: 1.4rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-brand i { color: var(--secondary); }

        .sidebar-menu {
            list-style: none;
            padding: 15px 0;
            margin: 0;
            flex: 1;
            overflow-y: auto;
        }

        .sidebar-menu li { padding: 6px 20px; }

        .sidebar-menu a {
            color: #a0a3bd;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: var(--sidebar-hover);
            color: white;
        }

        .sidebar-menu a.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(194,24,89,0.3);
        }

        .sidebar-menu i { font-size: 1.1rem; }

        .sidebar-footer {
            padding: 15px 20px;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        .user-profile-sm {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
        }

        .user-profile-sm img {
            width: 36px; height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar */
        .topbar {
            height: 70px;
            background: white;
            border-bottom: 1px solid #eef0f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .toggle-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark);
            cursor: pointer;
            display: none;
        }

        .search-bar {
            position: relative;
            width: 350px;
        }

        .search-bar input {
            width: 100%;
            border: 1px solid #eef0f5;
            border-radius: 10px;
            padding: 10px 15px 10px 40px;
            background: var(--body-bg);
            font-size: 0.9rem;
        }

        .search-bar i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0a3bd;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .icon-btn {
            position: relative;
            width: 40px; height: 40px;
            border-radius: 10px;
            border: 1px solid #eef0f5;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.2s;
        }

        .icon-btn:hover {
            background: var(--primary-light);
            color: var(--primary);
            border-color: var(--primary-light);
        }

        .icon-btn .badge {
            position: absolute;
            top: -4px; right: -4px;
            font-size: 0.65rem;
            padding: 3px 6px;
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 10px;
            transition: all 0.2s;
        }

        .user-dropdown:hover { background: var(--body-bg); }

        .user-dropdown img {
            width: 36px; height: 36px;
            border-radius: 10px;
            object-fit: cover;
        }

        /* Content Area */
        .content-area {
            padding: 30px;
            flex: 1;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .breadcrumb-custom {
            font-size: 0.85rem;
            color: #a0a3bd;
            margin-bottom: 25px;
        }

        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            border: none;
            height: 100%;
            transition: transform 0.2s;
        }

        .stat-card:hover { transform: translateY(-3px); }

        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 15px;
        }

        .stat-icon.pink { background: var(--primary-light); color: var(--primary); }
        .stat-icon.blue { background: #e3f2fd; color: #1976d2; }
        .stat-icon.green { background: #e8f5e9; color: #388e3c; }
        .stat-icon.orange { background: #fff3e0; color: #f57c00; }

        .stat-label {
            font-size: 0.85rem;
            color: #a0a3bd;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .stat-change {
            font-size: 0.8rem;
            font-weight: 600;
        }

        .stat-change.up { color: #388e3c; }
        .stat-change.down { color: #d32f2f; }

        /* Chart Card */
        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            height: 100%;
        }

        .card-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        /* Table */
        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .table-card .table {
            margin: 0;
        }

        .table-card thead th {
            background: #fafbfc;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #a0a3bd;
            border: none;
            padding: 15px 20px;
        }

        .table-card tbody td {
            padding: 15px 20px;
            vertical-align: middle;
            font-size: 0.9rem;
            color: #555;
            border-color: #f4f6f9;
        }

        .table-card tbody tr:hover { background: #fafbfc; }

        .product-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .product-cell img {
            width: 40px; height: 40px;
            border-radius: 8px;
            object-fit: cover;
            background: #f4f6f9;
        }

        .product-cell strong {
            color: var(--dark);
            font-weight: 600;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-delivered { background: #e8f5e9; color: #388e3c; }
        .badge-pending { background: #fff3e0; color: #f57c00; }
        .badge-cancelled { background: #ffebee; color: #d32f2f; }

        /* Progress List */
        .progress-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f4f6f9;
        }

        .progress-item:last-child { border: none; }

        .progress-img {
            width: 45px; height: 45px;
            border-radius: 10px;
            object-fit: cover;
            background: #f4f6f9;
        }

        .progress-info { flex: 1; }

        .progress-info h6 {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 4px;
            color: var(--dark);
        }

        .progress-info small {
            color: #a0a3bd;
            font-size: 0.8rem;
        }

        .progress-bar-custom {
            height: 6px;
            border-radius: 10px;
            background: #f4f6f9;
            overflow: hidden;
            margin-top: 8px;
        }

        .progress-bar-custom .bar {
            height: 100%;
            border-radius: 10px;
            background: var(--primary);
        }

        /* Mobile Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1035;
        }

        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .toggle-btn {
                display: block;
            }
            .search-bar {
                display: none;
            }
            .sidebar-overlay.show {
                display: block;
            }
        }

        @media (max-width: 576px) {
            .content-area { padding: 20px 15px; }
            .topbar { padding: 0 15px; }
        }
    </style>
</head>
<body>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-brand">
                <i class="bi bi-gem"></i>
                <span>SAREE ADMIN</span>
            </a>
        </div>

        <ul class="sidebar-menu">
            <li><a href="#" class="active"><i class="bi bi-grid-1x2-fill"></i> <span>Dashboard</span></a></li>
            <li><a href="#"><i class="bi bi-bag-fill"></i> <span>Products</span></a></li>
            <li><a href="#"><i class="bi bi-cart-fill"></i> <span>Orders</span> <span class="badge bg-danger ms-auto" style="font-size:0.7rem">12</span></a></li>
            <li><a href="#"><i class="bi bi-people-fill"></i> <span>Customers</span></a></li>
            <li><a href="#"><i class="bi bi-tags-fill"></i> <span>Categories</span></a></li>
            <li><a href="#"><i class="bi bi-star-fill"></i> <span>Reviews</span></a></li>
            
            <li class="mt-3" style="padding: 0 25px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #6c757d; font-weight: 600;">Finance</li>
            
            <li><a href="#"><i class="bi bi-wallet2"></i> <span>Transactions</span></a></li>
            <li><a href="#"><i class="bi bi-graph-up"></i> <span>Analytics</span></a></li>
            
            <li class="mt-3" style="padding: 0 25px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #6c757d; font-weight: 600;">System</li>
            
            <li><a href="#"><i class="bi bi-gear-fill"></i> <span>Settings</span></a></li>
            <li><a href="#"><i class="bi bi-shield-lock-fill"></i> <span>Admins</span></a></li>
        </ul>

        <div class="sidebar-footer">
            <div class="user-profile-sm">
                <img src="https://ui-avatars.com/api/?name=Admin+User&background=c2185b&color=fff" alt="Admin">
                <div>
                    <div style="font-size: 0.85rem; font-weight: 600;">Super Admin</div>
                    <div style="font-size: 0.75rem; color: #6c757d;">admin@sareestore.com</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Topbar -->
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="toggle-btn" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
                <div class="search-bar">
                    <i class="bi bi-search"></i>
                    <input type="text" placeholder="Search orders, products, customers...">
                </div>
            </div>

            <div class="topbar-actions">
                <div class="icon-btn">
                    <i class="bi bi-bell-fill"></i>
                    <span class="badge bg-danger rounded-pill">5</span>
                </div>
                <div class="icon-btn d-none d-sm-flex">
                    <i class="bi bi-envelope-fill"></i>
                    <span class="badge bg-primary rounded-pill">3</span>
                </div>
                <div class="user-dropdown dropdown">
                    <div data-bs-toggle="dropdown" class="d-flex align-items-center gap-2">
                        <img src="https://ui-avatars.com/api/?name=Admin+User&background=c2185b&color=fff" alt="Admin">
                        <div class="d-none d-md-block">
                            <div style="font-size: 0.85rem; font-weight: 600; color: var(--dark);">Super Admin</div>
                            <div style="font-size: 0.75rem; color: #a0a3bd;">Manager</div>
                        </div>
                        <i class="bi bi-chevron-down small text-muted"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow" style="border-radius: 12px;">
                        <li><a class="dropdown-item py-2" href="#"><i class="bi bi-person me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item py-2" href="#"><i class="bi bi-gear me-2"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="#"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="content-area">
            <h1 class="page-title">Dashboard Overview</h1>
            <div class="breadcrumb-custom">Home / Dashboard</div>

            <!-- Stats Row -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon pink"><i class="bi bi-currency-rupee"></i></div>
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-value">₹8,42,500</div>
                        <div class="stat-change up"><i class="bi bi-arrow-up-short"></i> +12.5% from last month</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon blue"><i class="bi bi-cart-check"></i></div>
                        <div class="stat-label">Total Orders</div>
                        <div class="stat-value">1,248</div>
                        <div class="stat-change up"><i class="bi bi-arrow-up-short"></i> +8.2% from last month</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon green"><i class="bi bi-people"></i></div>
                        <div class="stat-label">Customers</div>
                        <div class="stat-value">3,640</div>
                        <div class="stat-change up"><i class="bi bi-arrow-up-short"></i> +5.1% from last month</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon orange"><i class="bi bi-box-seam"></i></div>
                        <div class="stat-label">Products</div>
                        <div class="stat-value">486</div>
                        <div class="stat-change down"><i class="bi bi-arrow-down-short"></i> -2.4% from last month</div>
                    </div>
                </div>
            </div>

            <!-- Charts & Tables Row -->
            <div class="row g-4 mb-4">
                <!-- Sales Chart -->
                <div class="col-lg-8">
                    <div class="chart-card">
                        <div class="card-header-flex">
                            <h5 class="card-title">Sales Analytics</h5>
                            <select class="form-select form-select-sm" style="width: 120px; border-radius: 8px;">
                                <option>This Month</option>
                                <option>Last Month</option>
                                <option>This Year</option>
                            </select>
                        </div>
                        <div style="height: 300px; background: linear-gradient(180deg, var(--primary-light) 0%, transparent 100%); border-radius: 12px; display: flex; align-items: flex-end; padding: 20px; gap: 12px;">
                            <!-- Simple CSS Bar Chart -->
                            <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:8px;">
                                <div style="width:100%; background:var(--primary); border-radius:6px 6px 0 0; height:120px; opacity:0.8;"></div>
                                <small class="text-muted" style="font-size:0.75rem">Week 1</small>
                            </div>
                            <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:8px;">
                                <div style="width:100%; background:var(--primary); border-radius:6px 6px 0 0; height:180px;"></div>
                                <small class="text-muted" style="font-size:0.75rem">Week 2</small>
                            </div>
                            <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:8px;">
                                <div style="width:100%; background:var(--primary); border-radius:6px 6px 0 0; height:90px; opacity:0.6;"></div>
                                <small class="text-muted" style="font-size:0.75rem">Week 3</small>
                            </div>
                            <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:8px;">
                                <div style="width:100%; background:var(--primary); border-radius:6px 6px 0 0; height:220px;"></div>
                                <small class="text-muted" style="font-size:0.75rem">Week 4</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="col-lg-4">
                    <div class="chart-card">
                        <div class="card-header-flex">
                            <h5 class="card-title">Top Selling Sarees</h5>
                            <a href="#" class="text-decoration-none small" style="color: var(--primary);">View All</a>
                        </div>
                        
                        <div class="progress-item">
                            <img src="https://ui-avatars.com/api/?name=Banarasi&background=800020&color=fff" class="progress-img" alt="Banarasi">
                            <div class="progress-info">
                                <h6>Royal Banarasi Silk</h6>
                                <small>124 sold this month</small>
                                <div class="progress-bar-custom"><div class="bar" style="width: 85%;"></div></div>
                            </div>
                            <div class="fw-bold" style="color: var(--primary); font-size: 0.9rem;">₹12,999</div>
                        </div>

                        <div class="progress-item">
                            <img src="https://ui-avatars.com/api/?name=Kanjee&background=b8860b&color=fff" class="progress-img" alt="Kanjeevaram">
                            <div class="progress-info">
                                <h6>Pure Kanjeevaram</h6>
                                <small>98 sold this month</small>
                                <div class="progress-bar-custom"><div class="bar" style="width: 70%;"></div></div>
                            </div>
                            <div class="fw-bold" style="color: var(--primary); font-size: 0.9rem;">₹15,499</div>
                        </div>

                        <div class="progress-item">
                            <img src="https://ui-avatars.com/api/?name=Georgette&background=4272d7&color=fff" class="progress-img" alt="Georgette">
                            <div class="progress-info">
                                <h6>Designer Georgette</h6>
                                <small>86 sold this month</small>
                                <div class="progress-bar-custom"><div class="bar" style="width: 60%;"></div></div>
                            </div>
                            <div class="fw-bold" style="color: var(--primary); font-size: 0.9rem;">₹4,299</div>
                        </div>

                        <div class="progress-item">
                            <img src="https://ui-avatars.com/api/?name=Chiffon&background=00ad5f&color=fff" class="progress-img" alt="Chiffon">
                            <div class="progress-info">
                                <h6>Elegant Chiffon</h6>
                                <small>64 sold this month</small>
                                <div class="progress-bar-custom"><div class="bar" style="width: 45%;"></div></div>
                            </div>
                            <div class="fw-bold" style="color: var(--primary); font-size: 0.9rem;">₹3,899</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="row">
                <div class="col-12">
                    <div class="table-card">
                        <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                            <h5 class="card-title mb-0">Recent Orders</h5>
                            <a href="#" class="btn btn-sm text-white" style="background: var(--primary); border-radius: 8px;"><i class="bi bi-eye me-1"></i> View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Product</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="fw-bold" style="color: var(--primary);">#ORD-7845</span></td>
                                        <td>
                                            <div class="product-cell">
                                                <img src="https://ui-avatars.com/api/?name=Banarasi&background=800020&color=fff" alt="">
                                                <div><strong>Royal Banarasi Silk</strong><br><small class="text-muted">Qty: 1</small></div>
                                            </div>
                                        </td>
                                        <td> Priya Sharma<br><small class="text-muted">priya@email.com</small></td>
                                        <td>18 May 2026</td>
                                        <td class="fw-bold">₹12,999</td>
                                        <td><span class="badge-status badge-delivered">Delivered</span></td>
                                        <td><button class="btn btn-sm btn-light"><i class="bi bi-three-dots-vertical"></i></button></td>
                                    </tr>
                                    <tr>
                                        <td><span class="fw-bold" style="color: var(--primary);">#ORD-7844</span></td>
                                        <td>
                                            <div class="product-cell">
                                                <img src="https://ui-avatars.com/api/?name=Kanjee&background=b8860b&color=fff" alt="">
                                                <div><strong>Pure Kanjeevaram</strong><br><small class="text-muted">Qty: 2</small></div>
                                            </div>
                                        </td>
                                        <td> Anjali Patel<br><small class="text-muted">anjali@email.com</small></td>
                                        <td>17 May 2026</td>
                                        <td class="fw-bold">₹30,998</td>
                                        <td><span class="badge-status badge-pending">Pending</span></td>
                                        <td><button class="btn btn-sm btn-light"><i class="bi bi-three-dots-vertical"></i></button></td>
                                    </tr>
                                    <tr>
                                        <td><span class="fw-bold" style="color: var(--primary);">#ORD-7843</span></td>
                                        <td>
                                            <div class="product-cell">
                                                <img src="https://ui-avatars.com/api/?name=Georgette&background=4272d7&color=fff" alt="">
                                                <div><strong>Designer Georgette</strong><br><small class="text-muted">Qty: 1</small></div>
                                            </div>
                                        </td>
                                        <td> Sunita Rao<br><small class="text-muted">sunita@email.com</small></td>
                                        <td>17 May 2026</td>
                                        <td class="fw-bold">₹4,299</td>
                                        <td><span class="badge-status badge-delivered">Delivered</span></td>
                                        <td><button class="btn btn-sm btn-light"><i class="bi bi-three-dots-vertical"></i></button></td>
                                    </tr>
                                    <tr>
                                        <td><span class="fw-bold" style="color: var(--primary);">#ORD-7842</span></td>
                                        <td>
                                            <div class="product-cell">
                                                <img src="https://ui-avatars.com/api/?name=Cotton&background=00ad5f&color=fff" alt="">
                                                <div><strong>Handloom Cotton Tant</strong><br><small class="text-muted">Qty: 3</small></div>
                                            </div>
                                        </td>
                                        <td> Meera Iyer<br><small class="text-muted">meera@email.com</small></td>
                                        <td>16 May 2026</td>
                                        <td class="fw-bold">₹7,497</td>
                                        <td><span class="badge-status badge-cancelled">Cancelled</span></td>
                                        <td><button class="btn btn-sm btn-light"><i class="bi bi-three-dots-vertical"></i></button></td>
                                    </tr>
                                    <tr>
                                        <td><span class="fw-bold" style="color: var(--primary);">#ORD-7841</span></td>
                                        <td>
                                            <div class="product-cell">
                                                <img src="https://ui-avatars.com/api/?name=Bandhani&background=fa4251&color=fff" alt="">
                                                <div><strong>Gujarat Bandhani</strong><br><small class="text-muted">Qty: 1</small></div>
                                            </div>
                                        </td>
                                        <td> Kavita Joshi<br><small class="text-muted">kavita@email.com</small></td>
                                        <td>15 May 2026</td>
                                        <td class="fw-bold">₹6,899</td>
                                        <td><span class="badge-status badge-delivered">Delivered</span></td>
                                        <td><button class="btn btn-sm btn-light"><i class="bi bi-three-dots-vertical"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="mt-5 text-center text-muted" style="font-size: 0.85rem;">
                 Saree Store Admin. All rights reserved.
            </footer>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
    </script>
</body>
</html>