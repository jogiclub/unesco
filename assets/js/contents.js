/**
 * 파일 위치: assets/js/custom/contents.js
 * 역할: 컨텐츠 관리 페이지 스크립트 - memos.js 방식으로 수정
 */
'use strict';

$(document).ready(function() {
	var grid = null;
	var offcanvas = null;
	var collectorOffcanvas = null;



	// 초기화
	initPQGrid();
	bindEvents();
	offcanvas = new bootstrap.Offcanvas(document.getElementById('contentsOffcanvas'));
	collectorOffcanvas = new bootstrap.Offcanvas(document.getElementById('collectorOffcanvas'));

	/**
	 * PQGrid 초기화
	 */
	function initPQGrid() {
		var colModel = [
			{
				title: '<input type="checkbox" id="selectAllCheckbox" />',
				dataIndx: 'pq_selected',
				width: 50,
				align: 'center',
				resizable: false,
				sortable: false,
				editable: false,
				menuIcon: false,
				render: function(ui) {
					var checkboxId = 'content-checkbox-' + ui.rowData.idx;
					return '<input type="checkbox" class="content-checkbox" id="' + checkboxId + '" data-idx="' + ui.rowData.idx + '" />';
				}
			},
			{
				title: '분류',
				dataIndx: 'category_id',
				dataType: 'integer',
				width: 80,
				align: 'center',
				render: function(ui) {
					var categoryMap = { '1': '분류1', '2': '분류2', '3': '분류3' };
					return categoryMap[ui.cellData] || '-';
				}
			},
			{
				title: '국가',
				dataIndx: 'nation_id',
				dataType: 'integer',
				width: 80,
				align: 'center',
				render: function(ui) {
					var categoryMap = { '1': '분류1', '2': '분류2', '3': '분류3' };
					return categoryMap[ui.cellData] || '-';
				}
			},
			{
				title: '타이틀',
				dataIndx: 'title',
				dataType: 'string',
				width: 200,
				minWidth: 150,
				align: 'center'
			},
			{
				title: '설명',
				dataIndx: 'description',
				dataType: 'string',
				width: 250,
				minWidth: 150,
				align: 'center'
			},

			{
				title: '등록일',
				dataIndx: 'regi_date',
				dataType: 'string',
				width: 180,
				align: 'center'
			},
			{
				title: '등록자',
				dataIndx: 'regi_id',
				dataType: 'string',
				width: 180,
				align: 'center'
			},
			{
				title: '수정일',
				dataIndx: 'modi_date',
				dataType: 'string',
				width: 180,
				align: 'center'
			},
			{
				title: '수정자',
				dataIndx: 'modi_id',
				dataType: 'string',
				width: 180,
				align: 'center'
			}
		];

		var dataModel = {
			location: 'remote',
			dataType: 'JSON',
			method: 'GET',
			url: '/contents/get_list',
			postData: function() {
				return {
					keyword: $('#searchKeyword').val()
				};
			},
			getData: function(response) {
				return {
					curPage: response.curPage,
					totalRecords: response.totalRecords,
					data: response.data
				};
			}
		};

		var gridOptions = {
			width: '100%',
			height: 700,
			freezeCols: 4,
			colModel: colModel,
			dataModel: dataModel,
			editable: false,
			pageModel: { type: null },
			selectionModel: { type: 'row', mode: 'block' },
			numberCell: { show: false },
			showTitle: false,
			showToolbar: false,
			showBottom: false,
			showTop: false,
			showHeader: true,
			scrollModel: { autoFit: false },
			strNoRows: '컨텐츠 정보가 없습니다.',
			stripeRows: true,
			columnBorders: true,
			hoverMode: 'row',
			cellClick: function(event, ui) {
				if (ui.dataIndx === 'pq_selected') {
					handleCheckboxColumnClick(event, ui.rowData.idx);
				} else {
					openDetail(ui.rowData.idx);
				}
			}
		};

		grid = pq.grid('#contentsGrid', gridOptions);
	}

	/**
	 * 체크박스 컬럼 클릭 핸들러
	 */
	function handleCheckboxColumnClick(event, idx) {
		var isDirectCheckboxClick = $(event.target).hasClass('content-checkbox') ||
			$(event.originalEvent && event.originalEvent.target).hasClass('content-checkbox');

		if (!isDirectCheckboxClick) {
			var checkbox = $('.content-checkbox[data-idx="' + idx + '"]').first();
			if (checkbox.length > 0) {
				var isCurrentlyChecked = checkbox.is(':checked');
				checkbox.prop('checked', !isCurrentlyChecked);
			}
		}

		updateSelectAllCheckbox();
	}

	/**
	 * 전체 선택 체크박스 상태 업데이트
	 */
	function updateSelectAllCheckbox() {
		var totalCheckboxes = $('.content-checkbox').length;
		var checkedCheckboxes = $('.content-checkbox:checked').length;

		if (totalCheckboxes === 0) {
			$('#selectAllCheckbox').prop('checked', false);
			$('#selectAllCheckbox').prop('indeterminate', false);
		} else if (checkedCheckboxes === 0) {
			$('#selectAllCheckbox').prop('checked', false);
			$('#selectAllCheckbox').prop('indeterminate', false);
		} else if (checkedCheckboxes === totalCheckboxes) {
			$('#selectAllCheckbox').prop('checked', true);
			$('#selectAllCheckbox').prop('indeterminate', false);
		} else {
			$('#selectAllCheckbox').prop('checked', false);
			$('#selectAllCheckbox').prop('indeterminate', true);
		}
	}

	/**
	 * 그리드 새로고침
	 */
	function refreshGrid() {
		grid.refreshDataAndView();
	}

	/**
	 * 상세/등록 오프캔버스 열기
	 */
	function openDetail(idx) {
		resetForm();

		if (idx) {
			// 수정 모드
			$('#contentsOffcanvasLabel').text('컨텐츠 수정');
			$('#btnDelete').show();
			$('#readonlyInfo').show();

			ajaxRequest({
				url: '/contents/get_detail',
				type: 'GET',
				data: { idx: idx },
				success: function(res) {
					if (res.success) {
						var data = res.data;
						$('#formIdx').val(data.idx);
						$('#formTitle').val(data.title);
						$('#formDescription').val(data.description);
						$('#formCategoryId').val(data.category_id);
						$('#formNationId').val(data.nation_id);
						$('#infoRegiDate').text(data.regi_date || '-');
						$('#infoRegiId').text(data.regi_id || '-');
						$('#infoModiDate').text(data.modi_date || '-');
						$('#infoModiId').text(data.modi_id || '-');
					} else {
						showToast(res.message, 'error');
					}
				}
			});
		} else {
			// 등록 모드
			$('#contentsOffcanvasLabel').text('컨텐츠 등록');
			$('#btnDelete').hide();
			$('#readonlyInfo').hide();
		}

		offcanvas.show();
	}

	/**
	 * 폼 초기화
	 */
	function resetForm() {
		$('#contentsForm')[0].reset();
		$('#formIdx').val('');
		$('#infoRegiDate, #infoRegiId, #infoModiDate, #infoModiId').text('');
	}

	/**
	 * 폼 저장
	 */
	function saveForm() {
		var formData = getFormData('#contentsForm');

		if (!formData.title || !formData.title.trim()) {
			showToast('타이틀을 입력해주세요.', 'warning');
			$('#formTitle').focus();
			return;
		}

		ajaxRequest({
			url: '/contents/save',
			data: formData,
			success: function(res) {
				if (res.success) {
					showToast(res.message, 'success');
					offcanvas.hide();
					refreshGrid();
				} else {
					showToast(res.message, 'error');
				}
			}
		});
	}

	/**
	 * 단건 삭제
	 */
	function deleteItem() {
		var idx = $('#formIdx').val();
		if (!idx) return;

		showConfirmModal('삭제 확인', '해당 컨텐츠를 삭제하시겠습니까?', function() {
			ajaxRequest({
				url: '/contents/delete',
				data: { idx: [idx] },
				success: function(res) {
					if (res.success) {
						showToast(res.message, 'success');
						offcanvas.hide();
						refreshGrid();
					} else {
						showToast(res.message, 'error');
					}
				}
			});
		});
	}

	/**
	 * 선택 삭제
	 */
	function deleteSelected() {
		var selectedIdxs = [];
		$('.content-checkbox:checked').each(function() {
			selectedIdxs.push($(this).data('idx'));
		});

		if (selectedIdxs.length === 0) {
			showToast('삭제할 항목을 선택해주세요.', 'warning');
			return;
		}

		showConfirmModal('삭제 확인', '선택한 ' + selectedIdxs.length + '건을 삭제하시겠습니까?', function() {
			ajaxRequest({
				url: '/contents/delete',
				data: { idx: selectedIdxs },
				success: function(res) {
					if (res.success) {
						showToast(res.message, 'success');
						refreshGrid();
					} else {
						showToast(res.message, 'error');
					}
				}
			});
		});
	}

	/**
	 * 이벤트 바인딩
	 */
	function bindEvents() {
		// 검색 버튼
		$('#btnSearch').on('click', refreshGrid);

		// 검색어 엔터키
		$('#searchKeyword').on('keypress', function(e) {
			if (e.which === 13) {
				refreshGrid();
			}
		});

		// 등록 버튼
		$('#btnAdd').on('click', function() {
			openDetail(null);
		});

		// 선택 삭제 버튼
		$('#btnDeleteSelected').on('click', deleteSelected);

		// 폼 저장
		$('#contentsForm').on('submit', function(e) {
			e.preventDefault();
			saveForm();
		});



		// 단건 삭제 버튼
		$('#btnDelete').on('click', deleteItem);

		// 컨텐츠수집기 버튼
		$('#btnCollector').on('click', function() {
			openCollector();
		});

		// 수집 시작 버튼
		$('#btnStartCollect').on('click', function() {
			startCollect();
		});

		// 전체 선택 체크박스
		$(document).on('change', '#selectAllCheckbox', function() {
			var isChecked = $(this).prop('checked');
			$('.content-checkbox').prop('checked', isChecked);
		});

		// 개별 체크박스
		$(document).on('change', '.content-checkbox', function() {
			updateSelectAllCheckbox();
		});

	}


	/**
	 * 수집기 Offcanvas 열기
	 */
	function openCollector() {
		$('#collectorForm')[0].reset();
		$('#collectorProgress').addClass('d-none');
		$('#collectorLog').empty();
		$('.progress-bar').css('width', '0%');
		$('#btnStartCollect').prop('disabled', false).find('.spinner-border').addClass('d-none');
		collectorOffcanvas.show();
	}


	/**
	 * 파일 위치: assets/js/contents.js
	 * 역할: URL 순차 처리 함수 수정 - 요청 간격 증가
	 * 수정 내용: 다음 URL 처리 전 대기 시간을 2초로 증가
	 */

	/**
	 * URL 순차 처리
	 */
	function processUrls(urlList, title, context, index) {
		if (index >= urlList.length) {
			// 모든 URL 처리 완료
			addCollectorLog('모든 URL 수집이 완료되었습니다.', 'success');
			$('#btnStartCollect').prop('disabled', false).find('.spinner-border').addClass('d-none');
			showToast('수집이 완료되었습니다.', 'success');
			refreshGrid();
			return;
		}

		var url = urlList[index].trim();
		var progress = Math.round(((index + 1) / urlList.length) * 100);
		$('.progress-bar').css('width', progress + '%').text(progress + '%');

		addCollectorLog('[' + (index + 1) + '/' + urlList.length + '] 수집 중: ' + url);

		ajaxRequest({
			url: '/contents/collect',
			type: 'POST',
			timeout: 120000, // 타임아웃 2분으로 증가
			data: {
				url: url,
				title: title,
				context: context
			},
			success: function(res) {
				if (res.success) {
					addCollectorLog('성공: ' + (res.data.title || url), 'success');
				} else {
					addCollectorLog('실패: ' + res.message, 'error');
				}
				// 다음 URL 처리 (API Rate Limit 고려하여 2초 대기)
				setTimeout(function() {
					processUrls(urlList, title, context, index + 1);
				}, 2000);
			},
			error: function(xhr, status, error) {
				var errorMsg = '서버 통신 실패';
				if (status === 'timeout') {
					errorMsg = '요청 시간 초과';
				}
				addCollectorLog('오류: ' + errorMsg + ' - ' + url, 'error');
				// 오류 발생 시 3초 대기 후 다음 URL
				setTimeout(function() {
					processUrls(urlList, title, context, index + 1);
				}, 3000);
			}
		});
	}

	/**
	 * 파일 위치: assets/js/contents.js
	 * 역할: 컨텐츠 수집기 - 배치 처리 방식으로 변경
	 * 수정 내용: 모든 URL 스크래핑 완료 후 Gemini 1회 호출
	 */

	/**
	 * Gemini로 종합 처리 후 DB 저장 (1건)
	 */
	function processWithGemini(scrapedData, title, context) {
		ajaxRequest({
			url: '/contents/process_collected',
			type: 'POST',
			timeout: 180000,
			data: {
				scraped_data: scrapedData,
				title: title,
				context: context
			},
			success: function(res) {
				$('.progress-bar').css('width', '100%').text('100%');

				if (res.success) {
					addCollectorLog('저장 완료: ' + res.data.title, 'success');
					addCollectorLog('(' + res.data.source_count + '개 URL 종합)', 'success');

					showToast(res.message, 'success');
					refreshGrid();
				} else {
					addCollectorLog('처리 실패: ' + res.message, 'error');
					showToast(res.message, 'error');
				}

				$('#btnStartCollect').prop('disabled', false).find('.spinner-border').addClass('d-none');
			},
			error: function(xhr, status) {
				var errorMsg = status === 'timeout' ? '요청 시간 초과' : '서버 통신 실패';
				addCollectorLog('Gemini 처리 오류: ' + errorMsg, 'error');
				showToast(errorMsg, 'error');
				$('#btnStartCollect').prop('disabled', false).find('.spinner-border').addClass('d-none');
			}
		});
	}

	/**
	 * URL 순차 스크래핑
	 */
	function scrapeUrls(urlList, scrapedData, failedUrls, index, title, context) {
		if (index >= urlList.length) {
			addCollectorLog('스크래핑 완료: 성공 ' + scrapedData.length + '건, 실패 ' + failedUrls.length + '건', 'success');

			if (scrapedData.length === 0) {
				addCollectorLog('수집된 데이터가 없습니다.', 'error');
				$('#btnStartCollect').prop('disabled', false).find('.spinner-border').addClass('d-none');
				showToast('수집된 데이터가 없습니다.', 'error');
				return;
			}

			addCollectorLog('Gemini 종합 분석 요청 중...');
			$('.progress-bar').css('width', '80%').text('80%');

			processWithGemini(scrapedData, title, context);
			return;
		}

		var url = urlList[index].trim();
		var progress = Math.round(((index + 1) / urlList.length) * 70);
		$('.progress-bar').css('width', progress + '%').text(progress + '%');

		addCollectorLog('[' + (index + 1) + '/' + urlList.length + '] 스크래핑: ' + truncateUrl(url));

		ajaxRequest({
			url: '/contents/scrape_url',
			type: 'POST',
			data: { url: url },
			success: function(res) {
				if (res.success) {
					scrapedData.push(res.data);
					addCollectorLog('  -> 성공: ' + (res.data.title || url).substring(0, 50), 'success');
				} else {
					failedUrls.push({ url: url, error: res.message });
					addCollectorLog('  -> 실패: ' + res.message, 'error');
				}
				setTimeout(function() {
					scrapeUrls(urlList, scrapedData, failedUrls, index + 1, title, context);
				}, 300);
			},
			error: function() {
				failedUrls.push({ url: url, error: '서버 통신 실패' });
				addCollectorLog('  -> 오류: 서버 통신 실패', 'error');
				setTimeout(function() {
					scrapeUrls(urlList, scrapedData, failedUrls, index + 1, title, context);
				}, 300);
			}
		});
	}

	/**
	 * 수집 시작
	 */
	function startCollect() {
		var title = $('#collectorTitle').val().trim();
		var urls = $('#collectorUrls').val().trim();
		var context = $('#collectorContext').val().trim();

		if (!urls) {
			showToast('URL을 입력해주세요.', 'warning');
			$('#collectorUrls').focus();
			return;
		}

		if (!context) {
			showToast('컨텍스트를 입력해주세요.', 'warning');
			$('#collectorContext').focus();
			return;
		}

		var urlList = urls.split('\n').filter(function(url) {
			return url.trim() !== '';
		});

		if (urlList.length === 0) {
			showToast('유효한 URL이 없습니다.', 'warning');
			return;
		}

		$('#btnStartCollect').prop('disabled', true).find('.spinner-border').removeClass('d-none');
		$('#collectorProgress').removeClass('d-none');
		$('#collectorLog').empty();
		$('.progress-bar').css('width', '0%').text('0%');

		addCollectorLog('URL 스크래핑 시작 (' + urlList.length + '건)');

		var scrapedData = [];
		var failedUrls = [];

		scrapeUrls(urlList, scrapedData, failedUrls, 0, title, context);
	}


	/**
	 * URL 길이 자르기
	 */
	function truncateUrl(url) {
		if (url.length > 60) {
			return url.substring(0, 57) + '...';
		}
		return url;
	}



	/**
	 * 수집 로그 추가
	 */
	function addCollectorLog(message, type) {
		var colorClass = '';
		switch (type) {
			case 'success':
				colorClass = 'text-success';
				break;
			case 'error':
				colorClass = 'text-danger';
				break;
			default:
				colorClass = 'text-muted';
		}

		var time = new Date().toLocaleTimeString();
		var logHtml = '<div class="' + colorClass + '">[' + time + '] ' + escapeHtml(message) + '</div>';
		$('#collectorLog').append(logHtml);
		$('#collectorLog').scrollTop($('#collectorLog')[0].scrollHeight);
	}

});
