<?php $this->load->view('header'); ?>

<div class="col-12 col-sm-5 col-md-4 col-lg-3 mx-auto">
	<h2 class="text-center">로그인</h2>
	<div class="d-grid pb-5">
		<a href="<?php echo base_url('login/google_login'); ?>" class="btn btn-danger mt-2"><i class="bi bi-google"></i> 구글로 시작하기</a>
	</div>
</div>

<?php $this->load->view('footer'); ?>
<script src="/assets/js/login.js?<?php echo WB_VERSION; ?>"></script>
