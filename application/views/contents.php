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
			<button type="button" class="btn btn-sm btn-success" id="btnCollector">
				<i class="bi bi-cloud-download"></i> 컨텐츠수집기
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


<!-- 컨텐츠 수집기 Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="collectorOffcanvas" aria-labelledby="collectorOffcanvasLabel">
	<div class="offcanvas-header border-bottom">
		<h5 class="offcanvas-title" id="collectorOffcanvasLabel">콘텐츠 수집기</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body">
		<form id="collectorForm">
			<div class="mb-3">
				<label for="collectorTitle" class="form-label">제목</label>
				<input type="text" class="form-control" id="collectorTitle" name="title" placeholder="수집할 콘텐츠 제목">
			</div>

			<div class="mb-3">
				<label for="collectorUrls" class="form-label">URL</label>
				<textarea class="form-control" id="collectorUrls" name="urls" rows="5" placeholder="한 줄에 하나의 URL 입력"></textarea>
				<div class="form-text">여러 URL을 수집하려면 줄바꿈으로 구분하세요.</div>
			</div>

			<div class="mb-3">
				<label for="collectorContext" class="form-label">컨텍스트</label>
				<textarea class="form-control" id="collectorContext" name="context" rows="4" placeholder="Gemini에게 전달할 정리 지침을 입력하세요.&#10;예: 제목, 요약, 핵심 키워드, 카테고리를 추출해줘"></textarea>
			</div>

			<div id="collectorProgress" class="mb-3 d-none">
				<label class="form-label">수집 진행 상황</label>
				<div class="progress mb-2">
					<div class="progress-bar" role="progressbar" style="width: 0%"></div>
				</div>
				<div id="collectorLog" class="border rounded p-2 bg-light" style="height: 150px; overflow-y: auto; font-size: 12px;"></div>
			</div>
		</form>
	</div>
	<div class="offcanvas-footer border-top p-3">
		<div class="d-flex justify-content-end gap-2">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">취소</button>
			<button type="button" class="btn btn-primary" id="btnStartCollect">
				<span class="spinner-border spinner-border-sm d-none" role="status"></span>
				수집시작
			</button>
		</div>
	</div>
</div>

<?php $this->load->view('footer'); ?>

<!-- PQGrid JS -->
<script src="/assets/js/custom/pqgrid.min.js"></script>
<script src="/assets/js/contents.js?<?php echo WB_VERSION; ?>"></script>
