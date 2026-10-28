{{-- Hộp thoại xác nhận dùng chung (Bootstrap Modal) cho mọi form có thuộc tính data-confirm --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalTitle">Xác nhận thao tác</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body"><p class="mb-0" data-confirm-text>Bạn có chắc chắn?</p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-bad" data-confirm-ok>Đồng ý</button>
            </div>
        </div>
    </div>
</div>
