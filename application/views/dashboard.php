<?php
/**
 * 파일 위치: application/views/login.php
 * 역할: 구글 로그인 화면
 */
$this->load->view('header');
?>



<div class="container py-3">

	<h3>대시보드</h3>

	<nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="#">홈</a></li>
			<li class="breadcrumb-item"><a href="#">OVERVIEW</a></li>
			<li class="breadcrumb-item active" aria-current="page">대시보드</li>
		</ol>
	</nav>

	<div class="alert alert-warning alert-dismissible fade show" role="alert">
		<strong>대시보드</strong> 콘텐츠 관리 통계 제공
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	</div>
</div>

<?php $this->load->view('footer'); ?>
