'use strict';

/**
 * 파일 위치: assets/js/common.js
 * 역할: 공통 JavaScript 함수 (Toast, Modal, AJAX 헬퍼 등)
 */

/**
 * Toast 메시지 표시
 * @param {string} message - 표시할 메시지
 * @param {string} type - 메시지 타입 (success, error, warning, info)
 */
function showToast(message, type) {
	type = type || 'info';

	var toastEl = document.getElementById('liveToast');
	var toastBody = toastEl.querySelector('.toast-body');
	var toastHeader = toastEl.querySelector('.toast-header');

	// 기존 클래스 제거
	toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
	toastHeader.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'text-white');

	// 타입에 따른 스타일 적용
	switch (type) {
		case 'success':
			toastHeader.classList.add('bg-success', 'text-white');
			break;
		case 'error':
			toastHeader.classList.add('bg-danger', 'text-white');
			break;
		case 'warning':
			toastHeader.classList.add('bg-warning');
			break;
		case 'info':
		default:
			toastHeader.classList.add('bg-info', 'text-white');
			break;
	}

	toastBody.textContent = message;

	var toast = new bootstrap.Toast(toastEl, {
		autohide: true,
		delay: 3000
	});
	toast.show();
}

/**
 * 확인 모달 표시
 * @param {string} title - 모달 제목
 * @param {string} message - 모달 메시지
 * @param {function} onConfirm - 확인 버튼 클릭 시 콜백
 * @param {function} onCancel - 취소 버튼 클릭 시 콜백 (선택)
 */
function showConfirmModal(title, message, onConfirm, onCancel) {
	var modalEl = document.getElementById('confirmModal');
	var modal = new bootstrap.Modal(modalEl);

	modalEl.querySelector('.modal-title').textContent = title || '확인';
	modalEl.querySelector('.modal-body').textContent = message || '확인하시겠습니까?';

	var confirmBtn = modalEl.querySelector('.modal-footer .btn-primary');
	var cancelBtn = modalEl.querySelector('.modal-footer .btn-secondary');

	// 기존 이벤트 제거
	var newConfirmBtn = confirmBtn.cloneNode(true);
	var newCancelBtn = cancelBtn.cloneNode(true);
	confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
	cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);

	// 확인 버튼 이벤트
	newConfirmBtn.addEventListener('click', function() {
		modal.hide();
		if (typeof onConfirm === 'function') {
			onConfirm();
		}
	});

	// 취소 버튼 이벤트
	newCancelBtn.addEventListener('click', function() {
		modal.hide();
		if (typeof onCancel === 'function') {
			onCancel();
		}
	});

	modal.show();
}

/**
 * AJAX 요청 헬퍼
 * @param {object} options - AJAX 옵션
 */
function ajaxRequest(options) {
	var defaults = {
		type: 'POST',
		dataType: 'json',
		beforeSend: function() {
			// 로딩 표시 등
		},
		error: function(xhr, status, error) {
			showToast('요청 처리 중 오류가 발생했습니다.', 'error');
			console.error('AJAX Error:', error);
		},
		complete: function() {
			// 로딩 숨김 등
		}
	};

	var settings = $.extend({}, defaults, options);
	return $.ajax(settings);
}

/**
 * 폼 데이터를 객체로 변환
 * @param {string} formSelector - 폼 셀렉터
 * @returns {object} 폼 데이터 객체
 */
function getFormData(formSelector) {
	var formData = {};
	$(formSelector).serializeArray().forEach(function(item) {
		formData[item.name] = item.value;
	});
	return formData;
}

/**
 * 날짜 포맷 함수
 * @param {Date|string} date - 날짜
 * @param {string} format - 포맷 (기본: YYYY-MM-DD)
 * @returns {string} 포맷된 날짜 문자열
 */
function formatDate(date, format) {
	if (!date) return '';

	var d = new Date(date);
	format = format || 'YYYY-MM-DD';

	var year = d.getFullYear();
	var month = ('0' + (d.getMonth() + 1)).slice(-2);
	var day = ('0' + d.getDate()).slice(-2);
	var hours = ('0' + d.getHours()).slice(-2);
	var minutes = ('0' + d.getMinutes()).slice(-2);
	var seconds = ('0' + d.getSeconds()).slice(-2);

	return format
		.replace('YYYY', year)
		.replace('MM', month)
		.replace('DD', day)
		.replace('HH', hours)
		.replace('mm', minutes)
		.replace('ss', seconds);
}

/**
 * XSS 방지를 위한 HTML 이스케이프
 * @param {string} str - 이스케이프할 문자열
 * @returns {string} 이스케이프된 문자열
 */
function escapeHtml(str) {
	if (!str) return '';
	var div = document.createElement('div');
	div.appendChild(document.createTextNode(str));
	return div.innerHTML;
}
