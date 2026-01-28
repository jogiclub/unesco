
<?php $this->load->view('header'); ?>

<div class="container py-3">
	<h3>AI 컨텍스트 설정</h3>

	<nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="/">홈</a></li>
			<li class="breadcrumb-item"><a href="#">CONTENTS</a></li>
			<li class="breadcrumb-item active" aria-current="page">AI 컨텍스트 설정</li>
		</ol>
	</nav>

	<div class="card">
		<div class="card-body">
			<form id="contextForm">
				<div class="mb-3">
					<label for="formTitle" class="form-label">제목 <span class="text-danger">*</span></label>
					<input type="text" class="form-control" id="formTitle" name="title" maxlength="200" required>
				</div>

				<div class="mb-3">
					<label for="formContextJson" class="form-label">내용</label>
					<textarea class="form-control" id="formContextJson" name="context_json" rows="15" placeholder="AI에게 전달할 컨텍스트 내용을 입력하세요."></textarea>
				</div>

				<div class="d-grid">
					<button type="submit" class="btn btn-primary">저장</button>
				</div>
			</form>
		</div>
	</div>
</div>

<?php $this->load->view('footer'); ?>

<script>
	$(document).ready(function() {
		// 페이지 로드 시 기존 데이터 조회
		loadContextData();

		// 폼 제출
		$('#contextForm').on('submit', function(e) {
			e.preventDefault();
			saveContext();
		});
	});

	/**
	 * 컨텍스트 데이터 조회
	 */
	function loadContextData() {
		$.ajax({
			url: '/context/get_data',
			type: 'GET',
			dataType: 'json',
			success: function(response) {
				if (response.success && response.data) {
					$('#formTitle').val(response.data.title);
					$('#formContextJson').val(response.data.context_json);
				}
			},
			error: function() {
				showToast('데이터 조회 중 오류가 발생했습니다.', 'error');
			}
		});
	}

	/**
	 * 컨텍스트 저장
	 */
	function saveContext() {
		var title = $('#formTitle').val().trim();

		if (!title) {
			showToast('타이틀을 입력해주세요.', 'warning');
			$('#formTitle').focus();
			return;
		}

		$.ajax({
			url: '/context/save',
			type: 'POST',
			data: $('#contextForm').serialize(),
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					showToast(response.message, 'success');
				} else {
					showToast(response.message, 'error');
				}
			},
			error: function() {
				showToast('저장 중 오류가 발생했습니다.', 'error');
			}
		});
	}
</script>
