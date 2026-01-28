<?php
/**
 * 파일 위치: application/views/login.php
 * 역할: 구글 로그인 화면
 */
$this->load->view('header');
?>

<div class="container">
	<div class="row justify-content-center align-items-center min-vh-100">
		<div class="col-12 col-sm-5 col-md-4">
			<h4 class="text-center mb-4">UNESCOS<br/>콘텐츠 관리시스템</h4>
			<div class="card shadow">
				<div class="card-body p-4">
					<h2 class="text-center mb-4">로그인</h2>
					<!-- 에러 메시지 표시 영역 -->
					<div id="login-message"></div>

					<div class="d-grid gap-2">
						<a href="<?php echo base_url('login/google_login'); ?>" class="btn btn-danger">
							<i class="bi bi-google me-2"></i>구글로 시작하기
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('footer'); ?>

<script>
	$(document).ready(function() {
		<?php if ($this->session->flashdata('error')): ?>
		showToast('<?php echo $this->session->flashdata('error'); ?>', 'error');
		<?php endif; ?>

		<?php if ($this->session->flashdata('success')): ?>
		showToast('<?php echo $this->session->flashdata('success'); ?>', 'success');
		<?php endif; ?>
	});
</script>
