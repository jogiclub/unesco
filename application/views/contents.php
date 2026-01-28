<?php
/**
 * 역할: 컨텐츠 관리 화면 - PQGrid 목록 및 Offcanvas 수정 폼
 */
$this->load->view('header');
?>

<!-- PQGrid CSS -->
<link rel="stylesheet" href="/assets/css/custom/pqgrid.min.css?<?php echo WB_VERSION; ?>">

<style>
.pq-grid { font-size: 14px; }
.pq-grid-cell { padding: 8px 10px; }
.search-box { max-width: 300px; }
#contentsOffcanvas { width: 450px; }
@media (max-width: 576px) {
	#contentsOffcanvas { width: 100%; }
}
</style>

<div class="container py-3">
	<h3>컨텐츠 관리</h3>

	<nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="/">홈</a></li>
			<li class="breadcrumb-item"><a href="#">CONTENTS</a></li>
			<li class="breadcrumb-item active" aria-current="page">컨텐츠 관리</li>
		</ol>
	</nav>

	<!-- 검색 및 버튼 영역 -->
	<div class="d-flex justify-content-between align-items-center mb-3">
		<div class="d-flex gap-2">
			<div class="input-group input-group-sm search-box">
				<input type="text" class="form-control" id="searchKeyword" placeholder="검색어 입력">
				<button class="btn btn-outline-secondary" type="button" id="btnSearch">
					<i class="bi bi-search"></i> 검색
				</button>
			</div>
		</div>
		<div class="d-flex gap-2">
			<button type="button" class="btn btn-sm btn-primary" id="btnAdd">
				<i class="bi bi-plus-lg"></i> 등록
			</button>
			<button type="button" class="btn btn-sm btn-danger" id="btnDeleteSelected">
				<i class="bi bi-trash"></i> 선택삭제
			</button>
		</div>
	</div>

	<!-- PQGrid 컨테이너 -->
	<div id="contentsGrid"></div>
</div>

<!-- 컨텐츠 수정/등록 Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="contentsOffcanvas" aria-labelledby="contentsOffcanvasLabel">
	<div class="offcanvas-header">
		<h5 class="offcanvas-title" id="contentsOffcanvasLabel">컨텐츠 정보</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body">
		<form id="contentsForm">
			<input type="hidden" id="formIdx" name="idx">

			<div class="mb-3">
				<label for="formTitle" class="form-label">타이틀 <span class="text-danger">*</span></label>
				<input type="text" class="form-control" id="formTitle" name="title" required>
			</div>

			<div class="mb-3">
				<label for="formDescription" class="form-label">설명</label>
				<textarea class="form-control" id="formDescription" name="description" rows="4"></textarea>
			</div>

			<div class="mb-3">
				<label for="formCategoryId" class="form-label">분류</label>
				<select class="form-select" id="formCategoryId" name="category_id">
					<option value="">선택</option>
					<option value="1">분류1</option>
					<option value="2">분류2</option>
					<option value="3">분류3</option>
				</select>
			</div>

			<div class="mb-3">
				<label for="formNationId" class="form-label">국가</label>
				<select class="form-select" id="formNationId" name="nation_id">
					<option value="">선택</option>
					<option value="1">한국</option>
					<option value="2">미국</option>
					<option value="3">일본</option>
				</select>
			</div>

			<!-- 읽기 전용 정보 (수정 시에만 표시) -->
			<div id="readonlyInfo" style="display:none;">
				<hr>
				<div class="mb-3">
					<label class="form-label text-muted">등록일</label>
					<p id="infoRegiDate" class="form-control-plaintext"></p>
				</div>
				<div class="mb-3">
					<label class="form-label text-muted">등록자</label>
					<p id="infoRegiId" class="form-control-plaintext"></p>
				</div>
				<div class="mb-3">
					<label class="form-label text-muted">수정일</label>
					<p id="infoModiDate" class="form-control-plaintext"></p>
				</div>
				<div class="mb-3">
					<label class="form-label text-muted">수정자</label>
					<p id="infoModiId" class="form-control-plaintext"></p>
				</div>
			</div>

			<div class="d-grid gap-2 mt-4">
				<button type="submit" class="btn btn-primary">저장</button>
				<button type="button" class="btn btn-outline-danger" id="btnDelete" style="display:none;">삭제</button>
			</div>
		</form>
	</div>
</div>

<?php $this->load->view('footer'); ?>

<!-- PQGrid JS -->
<script src="/assets/js/custom/pqgrid.min.js"></script>
<script src="/assets/js/contents.js?<?php echo WB_VERSION; ?>"></script>
