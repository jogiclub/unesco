/**
 * 파일 위치: assets/js/custom/contents.js
 * 역할: 컨텐츠 관리 페이지 스크립트 - memos.js 방식으로 수정
 */
'use strict';

$(document).ready(function() {
	var grid = null;
	var offcanvas = null;

	// 초기화
	initPQGrid();
	bindEvents();
	offcanvas = new bootstrap.Offcanvas(document.getElementById('contentsOffcanvas'));

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
});
