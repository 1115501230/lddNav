<?php
try {
    $db = new SQLite3('navigation.db');
    
    // 获取所有分类和链接数据
    $query = "
        SELECT 
            pc.id as primary_id,
            pc.name as primary_name,
            pc.svg_name as primary_svg_name,
            sc.id as secondary_id,
            sc.name as secondary_name,
            l.title,
            l.description,
            l.url,
            l.icon,
            l.is_active
        FROM primary_categories pc
        LEFT JOIN secondary_categories sc ON pc.id = sc.primary_category_id
        LEFT JOIN links l ON sc.id = l.secondary_category_id
        ORDER BY pc.id, sc.id, l.title
    ";
    
    $result = $db->query($query);
    
    // 组织数据结构
    $categories = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        if (!isset($categories[$row['primary_id']])) {
            $categories[$row['primary_id']] = [
                'name' => $row['primary_name'],
                'svg_name' => $row['primary_svg_name'] ?? 'default.svg',
                'subcategories' => []
            ];
        }
        
        if ($row['secondary_id'] && !isset($categories[$row['primary_id']]['subcategories'][$row['secondary_id']])) {
            $categories[$row['primary_id']]['subcategories'][$row['secondary_id']] = [
                'name' => $row['secondary_name'],
                'links' => []
            ];
        }
        
        if ($row['title']) {
            $categories[$row['primary_id']]['subcategories'][$row['secondary_id']]['links'][] = [
                'title' => $row['title'],
                'description' => $row['description'],
                'url' => $row['url'],
                'icon' => $row['icon'],
                'is_active' => $row['is_active']
            ];
        }
    }

    // 过滤掉没有链接的二级分类
    $filteredCategories = [];
    foreach ($categories as $primary_id => $primary_category) {
        $filteredSubcategories = [];
        foreach ($primary_category['subcategories'] as $secondary_id => $secondary_category) {
            if (!empty($secondary_category['links'])) {
                $filteredSubcategories[$secondary_id] = $secondary_category;
            }
        }
        
        if (!empty($filteredSubcategories)) {
            $filteredCategories[$primary_id] = $primary_category;
            $filteredCategories[$primary_id]['subcategories'] = $filteredSubcategories;
        }
    }

    // 使用过滤后的分类
    $categories = $filteredCategories;

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>小神龙导航.</title>
    <!-- CSS files -->
    <link href="./dist/css/tabler.min.css?1692870487" rel="stylesheet"/>
    <link href="./dist/css/tabler-flags.min.css?1692870487" rel="stylesheet"/>
    <link href="./dist/css/tabler-payments.min.css?1692870487" rel="stylesheet"/>
    <link href="./dist/css/tabler-vendors.min.css?1692870487" rel="stylesheet"/>
    <link href="./dist/css/demo.min.css?1692870487" rel="stylesheet"/>
    <style>
      @import url('https://rsms.me/inter/inter.css');
      :root {
      	--tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
      }
      body {
      	font-feature-settings: "cv03", "cv04", "cv11";
      }
      html {
        scroll-behavior: smooth;
      }
      /* 调整锚点定位偏移 */
      @media (max-width: 991.98px) {
        /* 移动端样式 */
        [id] {
          scroll-margin-top: 48px; /* 与移动端navbar高度一致 */
        }
        
        /* 标题固定样式 */
        .page-title {
          position: sticky;
          top: 48px; /* 与navbar高度一致 */
          margin: 0;
          padding: 1rem 0;
          z-index: 1020;
        }

        /* 调整标题上下间距 */
        .col-12:has(> .page-title) {
          margin: 0;
          padding: 0;
        }

        /* 内容区域顶部间距 */
        .page-body {
          padding-top: 0;
        }
      }

      /* 桌面端样式 */
      @media (min-width: 992px) {
        [id] {
          scroll-margin-top: 1.5rem;
        }
      }
      /* 卡片圆角样式 */
      .card.card-link {
        border-radius: 6px;
        overflow: hidden;
      }
      
      /* 卡片图标圆角 */
      .card-link .icon {
        border-radius: 8px;
        padding: 8px;
      }
      /* 补充截断样式（Tabler已内置，此处为保险起见） */
      .text-truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }
      /* 侧边栏固定定位样式 */
      @media (max-width: 991.98px) {
        .navbar-vertical.navbar {  /* 增加选择器优先级 */
          position: fixed;
          top: 0;
          left: 0;
          bottom: 0;
          z-index: 1030;
          width: 100%;
          max-height: 48px;
          background-color: var(--tblr-bg-surface) !important; /* 使用!important确保背景色生效 */
          border-right: 1px solid var(--tblr-border-color);
          transition: transform 0.3s ease-in-out;
          box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); /* 添加阴影效果 */
        }
        .container-fluid{
          background-color: var(--tblr-bg-surface) !important; /* 使用!important确保背景色生效 */
        }
        /* 侧边栏展开时的样式 */
        .navbar-vertical.show {
          transform: translateX(0);
          box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        /* 内容区域在侧边栏展开时的偏移 */
        .navbar-vertical.show ~ .page-wrapper {
          transform: translateX(280px);
        }

        /* 页面内容过渡效果 */
        .page-wrapper {
          transition: transform 0.3s ease-in-out;
        }

        /* 遮罩层样式 */
        .navbar-vertical::before {
          content: '';
          position: fixed;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background: rgba(0, 0, 0, 0.3);
          z-index: -1;
          opacity: 0;
          visibility: hidden;
          transition: all 0.3s ease-in-out;
        }

        /* 显示遮罩层 */
        .navbar-vertical.show::before {
          opacity: 1;
          visibility: visible;
        }

        .page-wrapper{
          margin-top: 48px;
        }
      }
    </style>
  </head>
  <body >
    <script src="./dist/js/demo-theme.min.js?1692870487"></script>
    <div class="page">
      <!-- Sidebar -->
      <aside class="navbar navbar-vertical navbar-expand-lg navbar-transparent">
        <div class="container-fluid">
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <h1 class="navbar-brand navbar-brand-autodark">
            <a href=".">
              <img src="./static/logo.svg" width="110" height="32" alt="Tabler" class="navbar-brand-image">
            </a>
          </h1>
          <div class="navbar-nav flex-row d-lg-none">
            <div class="nav-item d-none d-lg-flex me-3">
              <div class="btn-list">
                <a href="https://github.com/tabler/tabler" class="btn" target="_blank" rel="noreferrer">
                  <!-- Download SVG icon from http://tabler-icons.io/i/brand-github -->
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 19c-4.3 1.4 -4.3 -2.5 -6 -3m12 5v-3.5c0 -1 .1 -1.4 -.5 -2c2.8 -.3 5.5 -1.4 5.5 -6a4.6 4.6 0 0 0 -1.3 -3.2a4.2 4.2 0 0 0 -.1 -3.2s-1.1 -.3 -3.5 1.3a12.3 12.3 0 0 0 -6.2 0c-2.4 -1.6 -3.5 -1.3 -3.5 -1.3a4.2 4.2 0 0 0 -.1 3.2a4.6 4.6 0 0 0 -1.3 3.2c0 4.6 2.7 5.7 5.5 6c-.6 .6 -.6 1.2 -.5 2v3.5" /></svg>
                  Source code
                </a>
                <a href="https://github.com/sponsors/codecalm" class="btn" target="_blank" rel="noreferrer">
                  <!-- Download SVG icon from http://tabler-icons.io/i/heart -->
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon text-pink" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" /></svg>
                  Sponsor
                </a>
              </div>
            </div>
            <div class="d-none d-lg-flex">
              <a href="?theme=dark" class="nav-link px-0 hide-theme-dark" title="Enable dark mode" data-bs-toggle="tooltip"
		   data-bs-placement="bottom">
                <!-- Download SVG icon from http://tabler-icons.io/i/moon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" /></svg>
              </a>
              <a href="?theme=light" class="nav-link px-0 hide-theme-light" title="Enable light mode" data-bs-toggle="tooltip"
		   data-bs-placement="bottom">
                <!-- Download SVG icon from http://tabler-icons.io/i/sun -->
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" /></svg>
              </a>
              <div class="nav-item dropdown d-none d-md-flex me-3">
                <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Show notifications">
                  <!-- Download SVG icon from http://tabler-icons.io/i/bell -->
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /></svg>
                  <span class="badge bg-red"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-card">
                  <div class="card">
                    <div class="card-header">
                      <h3 class="card-title">Last updates</h3>
                    </div>
                    <div class="list-group list-group-flush list-group-hoverable">
                      <div class="list-group-item">
                        <div class="row align-items-center">
                          <div class="col-auto"><span class="status-dot status-dot-animated bg-red d-block"></span></div>
                          <div class="col text-truncate">
                            <a href="#" class="text-body d-block">Example 1</a>
                            <div class="d-block text-secondary text-truncate mt-n1">
                              Change deprecated html tags to text decoration classes (#29604)
                            </div>
                          </div>
                          <div class="col-auto">
                            <a href="#" class="list-group-item-actions">
                              <!-- Download SVG icon from http://tabler-icons.io/i/star -->
                              <svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" /></svg>
                            </a>
                          </div>
                        </div>
                      </div>
                      <div class="list-group-item">
                        <div class="row align-items-center">
                          <div class="col-auto"><span class="status-dot d-block"></span></div>
                          <div class="col text-truncate">
                            <a href="#" class="text-body d-block">Example 2</a>
                            <div class="d-block text-secondary text-truncate mt-n1">
                              justify-content:between ⇒ justify-content:space-between (#29734)
                            </div>
                          </div>
                          <div class="col-auto">
                            <a href="#" class="list-group-item-actions show">
                              <!-- Download SVG icon from http://tabler-icons.io/i/star -->
                              <svg xmlns="http://www.w3.org/2000/svg" class="icon text-yellow" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" /></svg>
                            </a>
                          </div>
                        </div>
                      </div>
                      <div class="list-group-item">
                        <div class="row align-items-center">
                          <div class="col-auto"><span class="status-dot d-block"></span></div>
                          <div class="col text-truncate">
                            <a href="#" class="text-body d-block">Example 3</a>
                            <div class="d-block text-secondary text-truncate mt-n1">
                              Update change-version.js (#29736)
                            </div>
                          </div>
                          <div class="col-auto">
                            <a href="#" class="list-group-item-actions">
                              <!-- Download SVG icon from http://tabler-icons.io/i/star -->
                              <svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" /></svg>
                            </a>
                          </div>
                        </div>
                      </div>
                      <div class="list-group-item">
                        <div class="row align-items-center">
                          <div class="col-auto"><span class="status-dot status-dot-animated bg-green d-block"></span></div>
                          <div class="col text-truncate">
                            <a href="#" class="text-body d-block">Example 4</a>
                            <div class="d-block text-secondary text-truncate mt-n1">
                              Regenerate package-lock.json (#29730)
                            </div>
                          </div>
                          <div class="col-auto">
                            <a href="#" class="list-group-item-actions">
                              <!-- Download SVG icon from http://tabler-icons.io/i/star -->
                              <svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" /></svg>
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="nav-item dropdown">
              <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                <div class="d-none d-xl-block ps-2">
                  <div>Paweł Kuna</div>
                  <div class="mt-1 small text-secondary">UI Designer</div>
                </div>
              </a>
              <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <a href="#" class="dropdown-item">Status</a>
                <a href="./profile.html" class="dropdown-item">Profile</a>
                <a href="#" class="dropdown-item">Feedback</a>
                <div class="dropdown-divider"></div>
                <a href="./settings.html" class="dropdown-item">Settings</a>
                <a href="./sign-in.html" class="dropdown-item">Logout</a>
              </div>
            </div>
          </div>
          <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
              <li class="nav-item">
                <a class="nav-link" href="./" >
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
                  </span>
                  <span class="nav-link-title">
                    Home
                  </span>
                </a>
              </li>
              <?php foreach ($categories as $primary_category): ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#navbar-<?php echo htmlspecialchars($primary_category['name']); ?>" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <img src="/dist/img/icons/<?php echo htmlspecialchars($primary_category['svg_name']); ?>" class="icon" width="24" height="24" style="opacity: 0.3;">
                  </span>
                  <span class="nav-link-title">
                    <?php echo htmlspecialchars($primary_category['name']); ?>
                  </span>
                </a>
                <div class="dropdown-menu">
                  <div class="dropdown-menu-columns">
                    <div class="dropdown-menu-column">
                      <?php foreach ($primary_category['subcategories'] as $secondary_category): ?>
                      <a class="dropdown-item" href="#<?php echo htmlspecialchars(str_replace(' ', '-', strtolower($secondary_category['name']))); ?>">
                        <?php echo htmlspecialchars($secondary_category['name']); ?>
                      </a>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </aside>
      <div class="page-wrapper">
        <!-- Page header
        <div class="page-header d-print-none">
          <div class="container-xl">
            <div class="row g-2 align-items-center">
              <div class="col">
                <div class="page-pretitle">
                  Applications
                </div>
                <h2 class="page-title">
                  System Navigation
                </h2>
              </div>
            </div>
          </div>
        </div> -->

        <!-- Page body -->
        <div class="page-body">
          <div class="container-xl">
            <div class="row row-cards">
              <!-- 在系统管理模块之前添加搜索卡片 -->
              <div class="col-12 mb-4">
                <div class="card" style="height: 130px; min-height: 120px;">
                  <div class="card-body d-flex flex-column">
                    <!-- 搜索引擎切换标签 -->
                    <div class="nav-tabs-alt">
                      <ul class="nav nav-tabs" data-bs-toggle="tabs">
                        <li class="nav-item">
                          <a href="#tabs-local" class="nav-link active" data-bs-toggle="tab">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path><path d="M21 21l-6 -6"></path></svg>
                            本地搜索
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="#tabs-baidu" class="nav-link" data-bs-toggle="tab">
                          <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-baidu"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 9.5m-1 0a1 1.5 0 1 0 2 0a1 1.5 0 1 0 -2 0" /><path d="M14.463 11.596c1.282 1.774 3.476 3.416 3.476 3.416s1.921 1.574 .593 3.636c-1.328 2.063 -4.892 1.152 -4.892 1.152s-1.416 -.44 -3.06 -.088c-1.644 .356 -3.06 .22 -3.06 .22s-2.055 -.22 -2.47 -2.304c-.416 -2.084 1.918 -3.638 2.102 -3.858c.182 -.222 1.409 -.966 2.284 -2.394c.875 -1.428 3.337 -2.287 5.027 .221z" /><path d="M9 4.5m-1 0a1 1.5 0 1 0 2 0a1 1.5 0 1 0 -2 0" /><path d="M15 4.5m-1 0a1 1.5 0 1 0 2 0a1 1.5 0 1 0 -2 0" /><path d="M19 9.5m-1 0a1 1.5 0 1 0 2 0a1 1.5 0 1 0 -2 0" /></svg>
                            百度
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="#tabs-google" class="nav-link" data-bs-toggle="tab">
                          <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-google"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20.945 11a9 9 0 1 1 -3.284 -5.997l-2.655 2.392a5.5 5.5 0 1 0 2.119 6.605h-4.125v-3h7.945z" /></svg>
                            Google
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="#tabs-bing" class="nav-link" data-bs-toggle="tab">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-bing me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M5 3l4 1.5v12l6 -2.5l-2 -1l-1 -4l7 2.5v4.5l-10 5l-4 -2z"></path></svg>
                            Bing
                          </a>
                        </li>
                      </ul>
                    </div>

                    <!-- 搜索内容区域 -->
                    <div class="tab-content flex-grow-1">
                      <!-- 本地搜索 -->
                      <div class="tab-pane active show" id="tabs-local">
                        <div class="mt-3">
                          <div class="input-icon">
                            <input type="text" class="form-control" placeholder="模糊搜索..." id="localSearchInput">
                            <span class="input-icon-addon">
                              <img src="/static/pictures/cat.svg" class="icon" width="24" height="24" style="opacity: 0.8;">
                            </span>
                          </div>
                          <div id="searchResults" class="mt-3 overflow-auto" style="max-height: calc(33vh - 150px);"></div>
                        </div>
                      </div>
                      
                      <!-- 百度搜索 -->
                      <div class="tab-pane" id="tabs-baidu">
                        <form action="https://www.baidu.com/s" method="GET" target="_blank" class="mt-3">
                          <div class="input-icon">
                            <input type="text" name="wd" class="form-control" placeholder="百度搜索...">
                            <span class="input-icon-addon">
                              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path><path d="M21 21l-6 -6"></path></svg>
                            </span>
                          </div>
                        </form>
                      </div>

                      <!-- Google搜索 -->
                      <div class="tab-pane" id="tabs-google">
                        <form action="https://www.google.com/search" method="GET" target="_blank" class="mt-3">
                          <div class="input-icon">
                            <input type="text" name="q" class="form-control" placeholder="Google搜索...">
                            <span class="input-icon-addon">
                              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path><path d="M21 21l-6 -6"></path></svg>
                            </span>
                          </div>
                        </form>
                      </div>

                      <!-- Bing搜索 -->
                      <div class="tab-pane" id="tabs-bing">
                        <form action="https://www.bing.com/search" method="GET" target="_blank" class="mt-3">
                          <div class="input-icon">
                            <input type="text" name="q" class="form-control" placeholder="Bing搜索...">
                            <span class="input-icon-addon">
                              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path><path d="M21 21l-6 -6"></path></svg>
                            </span>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- 系统管理 -->
              <?php foreach ($categories as $primary_category): ?>
              
              <?php foreach ($primary_category['subcategories'] as $secondary_category): ?>
              <div class="col-12">
                <h3 class="page-title mt-3" id="<?php echo htmlspecialchars(str_replace(' ', '-', strtolower($secondary_category['name']))); ?>">
                  <?php echo htmlspecialchars($secondary_category['name']); ?>
                </h3>
              </div>
              
              <?php foreach ($secondary_category['links'] as $link): ?>
              <div class="col-6 col-sm-4 col-lg-3">
                <a class="card card-link" href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" <?php echo $link['is_active'] ? '' : 'style="opacity: 0.5;"'; ?>>
                  <div class="card-body d-flex align-items-center p-1">
                    <div class="flex-shrink-0 me-1">
                      <img 
                        src="<?php echo htmlspecialchars($link['icon']); ?>" 
                        onerror="this.onerror=null; this.src='static/pictures/none.svg';"
                        class="icon icon-lg text-blue" 
                        width="24" 
                        height="24" 
                        loading="lazy"
                      />
                    </div>
                    <div class="flex-grow-1 text-left overflow-hidden">
                      <div class="fw-bold mb-1 text-truncate"><?php echo htmlspecialchars($link['title']); ?></div>
                      <div class="d-block text-secondary text-truncate mt-n1" style="min-width: 0; max-width: 100%;">
                        <?php echo htmlspecialchars($link['description']); ?>
                      </div>
                    </div>
                  </div>
                </a>
              </div>
              <?php endforeach; ?>
              <?php endforeach; ?>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <?php include 'admin/footer.php'; ?>
      </div>
    </div>
    <!-- Libs JS -->  
    <!-- Tabler Core -->
    <script src="./dist/js/tabler.min.js?1692870487" defer></script>
    <script src="./dist/js/demo.min.js?1692870487" defer></script>
    
    <!-- 在页面底部添加脚本，在现有脚本之前 -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      // 获取所有锚点链接，但排除带有dropdown-toggle类的链接
      const anchorLinks = document.querySelectorAll('a[href^="#"]:not(.dropdown-toggle)');
      // 获取导航菜单折叠元素
      const navbarCollapse = document.querySelector('.navbar-collapse');

      // 为每个锚点链接添加点击事件
      anchorLinks.forEach(link => {
        link.addEventListener('click', function() {
          // 如果导航菜单处于展开状态，则关闭它
          if (navbarCollapse.classList.contains('show')) {
            // 使用Bootstrap的collapse实例来隐藏菜单
            const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
            if (bsCollapse) {
              bsCollapse.hide();
            }
          }
        });
      });
    });
    </script>

    <!-- 修改本地搜索脚本 -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('localSearchInput');
      const cardContainers = document.querySelectorAll('.col-6.col-sm-4.col-lg-3');
      const sectionHeaders = document.querySelectorAll('.col-12:has(> .page-title)');
      
      // 搜索功能
      function performSearch(searchTerm) {
        // 如果搜索词为空，显示所有内容
        if (searchTerm === '') {
          // 显示所有卡片
          cardContainers.forEach(container => {
            container.classList.remove('d-none');
          });
          
          // 显示所有section标题
          sectionHeaders.forEach(header => {
            header.classList.remove('d-none');
          });
          
          return;
        }

        // 遍历所有卡片
        cardContainers.forEach(container => {
          const title = container.querySelector('.fw-bold')?.textContent || '';
          const desc = container.querySelector('.text-secondary')?.textContent || '';
          
          // 检查是否匹配搜索词
          const isMatch = title.toLowerCase().includes(searchTerm.toLowerCase()) ||
            desc.toLowerCase().includes(searchTerm.toLowerCase());
          
          // 根据匹配结果显示或隐藏卡片
          container.classList.toggle('d-none', !isMatch);
        });

        // 处理section标题的显示/隐藏
        sectionHeaders.forEach(header => {
          // 获取当前section后面的所有卡片，直到下一个section
          const nextHeader = header.nextElementSibling;
          if (nextHeader) {
            const hasVisibleCards = Array.from(nextHeader.querySelectorAll('.col-6.col-sm-4.col-lg-3'))
              .some(card => !card.classList.contains('d-none'));
            
            // 如果该section下没有可见的卡片，则隐藏section标题
            header.classList.toggle('d-none', !hasVisibleCards);
          }
        });
      }

      // 监听搜索输入
      let debounceTimer;
      searchInput.addEventListener('input', function(e) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
          performSearch(e.target.value.trim());
        }, 300);
      });
    });
    </script>
  </body>
</html>
