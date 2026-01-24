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


<div class="container">
	<header class="d-flex justify-content-between align-items-center border-bottom py-3">
		<h1 class="logo fw-bold mb-0">
			<a href="/"><i class="bi bi-bookmark-star-fill"></i> UNESCOS</a>
		</h1>
		<button class="btn btn-menu" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions"><i class="bi bi-list"></i></button>
	</header>
</div>



<div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions" aria-labelledby="offcanvasWithBothOptionsLabel">
	<div class="offcanvas-header">
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body">
		<!-- OVERVIEW 섹션 -->
		<small class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-3 mb-1 text-body-secondary text-uppercase">OVERVIEW</small>
		<ul class="nav flex-column mb-auto">
			<li class="nav-item">
				<a class="nav-link d-flex align-items-center gap-1 menu-11" aria-current="page" href="/dashboard"><i class="bi bi-file-earmark-ruled"></i> 대시보드</a>
			</li>
		</ul>

		<!-- MEMBER 섹션 -->
		<small class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-3 mb-1 text-body-secondary text-uppercase">CONTENTS</small>
		<ul class="nav flex-column mb-auto">
			<li class="nav-item">
				<a class="nav-link d-flex align-items-center gap-1 menu-21" href="/member"><i class="bi bi-people"></i> 카테고리 설정</a>
			</li>
			<li class="nav-item">
				<a class="nav-link d-flex align-items-center gap-1 menu-22" href="/attendance"><i class="bi bi-clipboard-check"></i> AI 컨텍스트 설정</a>
			</li>
			<li class="nav-item">
				<a class="nav-link d-flex align-items-center gap-1 menu-23" href="/qrcheck"><i class="bi bi-qr-code-scan"></i> 컨텐츠 관리</a>
			</li>
		</ul>



		<ul class="nav flex-column mb-auto">
			<li class="nav-item">
				<a class="nav-link d-flex align-items-center gap-1 menu-logout" href="/logout">
					<i class="bi bi-box-arrow-right"></i> 로그아웃
				</a>
			</li>
		</ul>
	</div>
</div>

<main>


