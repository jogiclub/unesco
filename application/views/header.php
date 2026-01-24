<?php
/**
 * 파일 위치: application/views/header.php
 * 역할: 공통 헤더 및 사이드바 메뉴 (사용자 권한에 따른 메뉴 필터링 포함)
 */
?>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<meta name="viewport" content="width=device-width, initial-scale=0.9">
<meta name="description" content="UNESCO 콘텐츠 관리시스템"/>
<meta name="keywords" content="UNESCO 콘텐츠 관리시스템"/>
<meta name="author" content="unesco.webhows.com"/>

<!-- Facebook and Twitter integration -->
<meta property="og:title" content="왔니"/>
<meta property="og:image" content=""/>
<meta property="og:url" content=""/>
<meta property="og:site_name" content="UNESCO 콘텐츠 관리시스템"/>
<meta property="og:description" content="UNESCO 콘텐츠 관리시스템"/>

<meta name="twitter:title" content="UNESCO 콘텐츠 관리시스템"/>
<meta name="twitter:image" content=""/>
<meta name="twitter:url" content="unesco.webhows.com"/>
<meta name="twitter:card" content="UNESCO 콘텐츠 관리시스템"/>

<title>UNESCO 콘텐츠 관리시스템</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.3/themes/base/jquery-ui.min.css" integrity="sha512-8PjjnSP8Bw/WNPxF6wkklW6qlQJdWJc/3w/ZQPvZ/1bjVDkrrSqLe9mfPYrMxtnzsXFPc434+u4FHLnLjXTSsg==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
<link rel="stylesheet" as="style" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" />
<link rel="stylesheet" href="/assets/css/common.css?<?php echo WB_VERSION; ?>">


<div class="container-fluid">
	<header>
		<div class="logo">
			UNESCO-LIST
		</div>
		<div class="sidebar border-right p-0 bg-body-tertiary">
			<div class="offcanvas-xl offcanvas-end bg-body-tertiary" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
				<div class="offcanvas-header">
					<button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
				</div>
				<div class="offcanvas-body d-xl-flex flex-column p-0 pt-xl-3 overflow-y-auto">


					<!-- OVERVIEW 섹션 -->
					<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-3 mb-1 text-body-secondary text-uppercase">
						OVERVIEW				</h6>
					<ul class="nav flex-column mb-auto">
						<li class="nav-item">
							<a class="nav-link d-flex align-items-center gap-1 menu-11" aria-current="page" href="https://wani.im/dashboard">
								<i class="bi bi-file-earmark-ruled"></i> 대시보드							</a>
						</li>
					</ul>

					<!-- MEMBER 섹션 -->
					<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-3 mb-1 text-body-secondary text-uppercase">
						MEMBER				</h6>
					<ul class="nav flex-column mb-auto">
						<li class="nav-item">
							<a class="nav-link d-flex align-items-center gap-1 menu-21" href="https://wani.im/member">
								<i class="bi bi-people"></i> 회원관리							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link d-flex align-items-center gap-1 menu-22" href="https://wani.im/attendance">
								<i class="bi bi-clipboard-check"></i> 출석관리							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link d-flex align-items-center gap-1 menu-23" href="https://wani.im/qrcheck">
								<i class="bi bi-qr-code-scan"></i> QR출석							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link d-flex align-items-center gap-1 menu-24" href="https://wani.im/timeline">
								<i class="bi bi-clock-history"></i> 타임라인관리							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link d-flex align-items-center gap-1 menu-25" href="https://wani.im/memos">
								<i class="bi bi-journal-bookmark"></i> 메모관리							</a>
						</li>
					</ul>

					<!-- HOMEPAGE 섹션 -->
					<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-3 mb-1 text-body-secondary text-uppercase">
						HOMEPAGE				</h6>
					<ul class="nav flex-column mb-auto">
						<li class="nav-item">
							<a class="nav-link d-flex align-items-center gap-1 menu-31" href="https://wani.im/homepage_setting">
								<i class="bi bi-house-gear"></i> 홈페이지 기본설정							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link d-flex align-items-center gap-1 menu-32" href="https://wani.im/homepage_menu">
								<i class="bi bi-view-stacked"></i> 홈페이지 메뉴설정							</a>
						</li>
					</ul>

					<!-- SETTING 섹션 -->
					<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-3 mb-1 text-body-secondary text-uppercase">
						SETTING				</h6>
					<ul class="nav flex-column mb-auto">
						<li class="nav-item">
							<a class="nav-link d-flex align-items-center gap-1 menu-41" href="https://wani.im/org">
								<i class="bi bi-building-gear"></i> 조직설정							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link d-flex align-items-center gap-1 menu-42" href="https://wani.im/group_setting">
								<i class="bi bi-diagram-3"></i> 그룹설정							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link d-flex align-items-center gap-1 menu-43" href="https://wani.im/detail_field">
								<i class="bi bi-input-cursor-text"></i> 상세필드설정							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link d-flex align-items-center gap-1 menu-44" href="https://wani.im/attendance_setting">
								<i class="bi bi-sliders2-vertical"></i> 출석설정							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link d-flex align-items-center gap-1 menu-45 active" href="https://wani.im/user_management">
								<i class="bi bi-person-video"></i> 사용자관리							</a>
						</li>
					</ul>

					<hr class="my-3">
					<ul class="nav flex-column mb-auto">
						<li class="nav-item">
							<a class="nav-link d-flex align-items-center gap-1 menu-logout" href="https://wani.im/login/logout">
								<i class="bi bi-box-arrow-right"></i> 로그아웃
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</header>
	<main>
