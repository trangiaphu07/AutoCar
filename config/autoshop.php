<?php

return [
    // Đặt cọc: % trên tổng giá xe, tối thiểu min_deposit (VND)
    'deposit_percent' => (int) env('DEPOSIT_PERCENT', 10),
    'min_deposit' => (int) env('MIN_DEPOSIT', 10_000_000),

    // Chi phí lăn bánh ước tính: lệ phí trước bạ (%) + phí biển số & đăng kiểm (mỗi xe)
    'registration_rate_new' => 10,   // xe mới: 10%
    'registration_rate_used' => 2,   // xe đã qua sử dụng: 2%
    'plate_fee' => 20_000_000,       // biển số + đăng kiểm + bảo hiểm TNDS (ước tính / xe)

    // Mỗi mẫu xe chỉ được đặt tối đa bấy nhiêu chiếc trong 1 đơn
    'max_qty_per_car' => 3,

    // Giả định lãi suất trả góp mặc định (%/năm) cho công cụ tính
    'loan_rate' => 8.5,

    'showrooms' => [
        'AutoShop Quận 1 – 12 Lê Duẩn, Q.1, TP.HCM',
        'AutoShop Thủ Đức – 220 Võ Văn Ngân, TP. Thủ Đức',
        'AutoShop Tân Phú – 140 Lê Trọng Tấn, Q. Tân Phú',
        'AutoShop Hà Nội – 88 Phạm Văn Đồng, Q. Bắc Từ Liêm',
    ],

    'hotline' => '1900 6868',

    'vnpay' => [
        'tmn_code' => env('VNP_TMN_CODE', ''),
        'hash_secret' => env('VNP_HASH_SECRET', ''),
        'url' => env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'version' => '2.1.0',
    ],

    // Tài khoản nhận chuyển khoản. bank_id là mã ngân hàng theo VietQR (VCB, TCB, MB, ACB, BIDV... hoặc mã BIN 6 số)
    'bank' => [
        'bank_id' => env('BANK_ID', 'VCB'),
        'bank_name' => env('BANK_NAME', 'Vietcombank'),
        'account_no' => env('BANK_ACCOUNT_NO', '0123456789'),
        'account_name' => env('BANK_ACCOUNT_NAME', 'CONG TY TNHH AUTOSHOP'),
        'qr_template' => env('BANK_QR_TEMPLATE', 'compact2'),
    ],

    // Đơn VNPay chưa cọc sau bấy nhiêu phút sẽ bị hủy, trả lại xe vào kho
    'payment_expire_minutes' => 15,
];
