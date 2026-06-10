<?php
/**
 * Admin Orders Management
 */

// Load orders
$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (o.order_id LIKE ? OR o.recipient_name LIKE ? OR o.recipient_phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($statusFilter) {
    $where .= " AND o.order_status = ?";
    $params[] = $statusFilter;
}

$orders = $db->select("
    SELECT o.*, u.full_name, u.email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    $where
    ORDER BY o.created_at DESC
    LIMIT 100
", $params);

$statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];
?>

<div class="mb-6 flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
    <form method="GET" class="flex gap-3 flex-wrap">
        <input type="hidden" name="action" value="orders">
        <input type="text" name="search" placeholder="Tìm mã đơn, tên, SĐT..." value="<?= htmlspecialchars($search) ?>"
               class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red focus:border-transparent outline-none">
        <select name="status" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">Tất cả trạng thái</option>
            <?php foreach ($statuses as $status): ?>
            <option value="<?= $status ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
                <?= match($status) {
                    'pending' => 'Chờ xử lý',
                    'confirmed' => 'Đã xác nhận',
                    'processing' => 'Đang xử lý',
                    'shipped' => 'Đang giao',
                    'delivered' => 'Đã giao',
                    'cancelled' => 'Đã hủy',
                    'returned' => 'Trả hàng',
                    default => ucfirst($status)
                } ?>
            </option>
            <?php endforeach; ?>
        </select>
    </form>
    <button onclick="exportSelectedOrders()"
       class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
        <span class="material-symbols-outlined text-xl">download</span>
        Xuất Excel (<span id="selected-count">0</span>)
    </button>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                        <input type="checkbox" id="select-all-orders" onchange="toggleSelectAllOrders()" class="w-4 h-4 rounded border-gray-300 cursor-pointer">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Mã ĐH</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Khách hàng</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tổng tiền</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thanh toán</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ngày đặt</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($orders as $order): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <input type="checkbox" name="order_ids[]" value="<?= $order['order_id'] ?>" class="order-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer">
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-medium">#<?= $order['order_id'] ?></span>
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($order['recipient_name']) ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($order['recipient_phone']) ?></p>
                    </td>
                    <td class="px-4 py-3 text-sm font-medium text-axeron-red"><?= formatPrice($order['total_amount']) ?></td>
                    <td class="px-4 py-3">
                        <?php
                        $paymentClass = match($order['payment_status']) {
                            'paid' => 'bg-green-100 text-green-800',
                            'unpaid' => 'bg-yellow-100 text-yellow-800',
                            'refunded' => 'bg-gray-100 text-gray-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                        $paymentText = match($order['payment_status']) {
                            'paid' => 'Đã thanh toán',
                            'unpaid' => 'Chưa thanh toán',
                            'refunded' => 'Đã hoàn tiền',
                            default => $order['payment_status']
                        };
                        ?>
                        <div class="relative inline-block">
                            <button onclick="togglePaymentDropdown(event, <?= $order['order_id'] ?>)"
                                    data-order-id="<?= $order['order_id'] ?>"
                                    class="payment-dropdown-btn px-2.5 py-0.5 rounded-full text-xs font-medium <?= $paymentClass ?> hover:opacity-80 transition-opacity flex items-center gap-1 cursor-pointer whitespace-nowrap">
                                <span class="payment-text"><?= $paymentText ?></span>
                                <span class="material-symbols-outlined text-xs">expand_more</span>
                            </button>
                            <div id="payment-dropdown-<?= $order['order_id'] ?>" class="payment-dropdown absolute right-0 mt-1 bg-white rounded-lg shadow-lg border z-50 hidden min-w-[160px]">
                                <button onclick="updatePaymentStatus(<?= $order['order_id'] ?>, 'unpaid')"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 <?= $order['payment_status'] === 'unpaid' ? 'bg-gray-50 font-medium' : '' ?>">
                                    <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                    Chưa thanh toán
                                </button>
                                <button onclick="updatePaymentStatus(<?= $order['order_id'] ?>, 'paid')"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 <?= $order['payment_status'] === 'paid' ? 'bg-gray-50 font-medium' : '' ?>">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    Đã thanh toán
                                </button>
                                <button onclick="updatePaymentStatus(<?= $order['order_id'] ?>, 'refunded')"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 <?= $order['payment_status'] === 'refunded' ? 'bg-gray-50 font-medium' : '' ?>">
                                    <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                                    Đã hoàn tiền
                                </button>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <?php
                        $statusClass = match($order['order_status']) {
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'confirmed' => 'bg-blue-100 text-blue-800',
                            'processing' => 'bg-purple-100 text-purple-800',
                            'shipped' => 'bg-indigo-100 text-indigo-800',
                            'delivered' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                            'returned' => 'bg-gray-100 text-gray-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                        $statusText = match($order['order_status']) {
                            'pending' => 'Chờ xử lý',
                            'confirmed' => 'Đã xác nhận',
                            'processing' => 'Đang xử lý',
                            'shipped' => 'Đang giao',
                            'delivered' => 'Đã giao',
                            'cancelled' => 'Đã hủy',
                            'returned' => 'Trả hàng',
                            default => $order['order_status']
                        };
                        $statusDot = match($order['order_status']) {
                            'pending' => 'bg-yellow-500',
                            'confirmed' => 'bg-blue-500',
                            'processing' => 'bg-purple-500',
                            'shipped' => 'bg-indigo-500',
                            'delivered' => 'bg-green-500',
                            'cancelled' => 'bg-red-500',
                            'returned' => 'bg-gray-500',
                            default => 'bg-gray-500'
                        };
                        ?>
                        <div class="relative inline-block">
                            <button onclick="toggleStatusDropdown(event, <?= $order['order_id'] ?>)"
                                    data-order-id="<?= $order['order_id'] ?>"
                                    class="status-dropdown-btn px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?> hover:opacity-80 transition-opacity flex items-center gap-1 cursor-pointer whitespace-nowrap">
                                <span class="status-dot w-2 h-2 rounded-full <?= $statusDot ?>"></span>
                                <span class="status-text"><?= $statusText ?></span>
                                <span class="material-symbols-outlined text-xs">expand_more</span>
                            </button>
                            <div id="status-dropdown-<?= $order['order_id'] ?>" class="status-dropdown absolute left-0 mt-1 bg-white rounded-lg shadow-lg border z-50 hidden min-w-[180px]">
                                <button onclick="updateOrderStatusFromDropdown(<?= $order['order_id'] ?>, 'pending')"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 <?= $order['order_status'] === 'pending' ? 'bg-gray-50 font-medium' : '' ?>">
                                    <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                    Chờ xử lý
                                </button>
                                <button onclick="updateOrderStatusFromDropdown(<?= $order['order_id'] ?>, 'confirmed')"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 <?= $order['order_status'] === 'confirmed' ? 'bg-gray-50 font-medium' : '' ?>">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    Đã xác nhận
                                </button>
                                <button onclick="updateOrderStatusFromDropdown(<?= $order['order_id'] ?>, 'processing')"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 <?= $order['order_status'] === 'processing' ? 'bg-gray-50 font-medium' : '' ?>">
                                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                    Đang xử lý
                                </button>
                                <button onclick="updateOrderStatusFromDropdown(<?= $order['order_id'] ?>, 'shipped')"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 <?= $order['order_status'] === 'shipped' ? 'bg-gray-50 font-medium' : '' ?>">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                    Đang giao
                                </button>
                                <button onclick="updateOrderStatusFromDropdown(<?= $order['order_id'] ?>, 'delivered')"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 <?= $order['order_status'] === 'delivered' ? 'bg-gray-50 font-medium' : '' ?>">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    Đã giao
                                </button>
                                <button onclick="updateOrderStatusFromDropdown(<?= $order['order_id'] ?>, 'cancelled')"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 <?= $order['order_status'] === 'cancelled' ? 'bg-gray-50 font-medium' : '' ?>">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    Đã hủy
                                </button>
                                <button onclick="updateOrderStatusFromDropdown(<?= $order['order_id'] ?>, 'returned')"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 <?= $order['order_status'] === 'returned' ? 'bg-gray-50 font-medium' : '' ?>">
                                    <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                                    Trả hàng
                                </button>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                    <td class="px-4 py-3">
                        <a href="javascript:void(0)" onclick="viewOrder(<?= $order['order_id'] ?>)"
                           class="p-2 hover:bg-gray-100 rounded-lg transition-colors" title="Chi tiết">
                            <span class="material-symbols-outlined text-gray-600">visibility</span>
                        </a>
                        <a href="javascript:void(0)" onclick="printInvoice(<?= $order['order_id'] ?>)"
                           class="p-2 hover:bg-gray-100 rounded-lg transition-colors" title="In hóa đơn">
                            <span class="material-symbols-outlined text-gray-600">print</span>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">Không tìm thấy đơn hàng nào</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
async function viewOrder(orderId) {
    try {
        const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php?action=get_order&id=' + orderId);
        const result = await response.json();

        if (result.success && result.order) {
            const o = result.order;
            let itemsHtml = '';
            if (result.items) {
                result.items.forEach(item => {
                    itemsHtml += `
                        <tr>
                            <td class="px-4 py-2">${item.product_name}</td>
                            <td class="px-4 py-2">${item.variant_info || '-'}</td>
                            <td class="px-4 py-2">${item.quantity}</td>
                            <td class="px-4 py-2">${formatCurrency(item.unit_price)}</td>
                            <td class="px-4 py-2 font-medium">${formatCurrency(item.subtotal)}</td>
                        </tr>
                    `;
                });
            }

            const modalContent = `
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold">Chi Tiết Đơn Hàng #${o.order_id}</h2>
                        <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-500">Khách hàng</p>
                            <p class="font-medium">${o.full_name || o.recipient_name}</p>
                            <p class="text-sm text-gray-600">${o.email || ''}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Ngày đặt</p>
                            <p class="font-medium">${new Date(o.created_at).toLocaleString('vi-VN')}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-500">Địa chỉ giao hàng</p>
                            <p class="font-medium">${o.recipient_name} - ${o.recipient_phone}</p>
                            <p class="text-sm text-gray-600">${o.shipping_address}</p>
                        </div>
                    </div>

                    <div class="border rounded-lg overflow-hidden mb-6">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Sản phẩm</th>
                                    <th class="px-4 py-2 text-left">Phân loại</th>
                                    <th class="px-4 py-2 text-left">SL</th>
                                    <th class="px-4 py-2 text-left">Đơn giá</th>
                                    <th class="px-4 py-2 text-left">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                ${itemsHtml}
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-right">Tạm tính:</td>
                                    <td class="px-4 py-2">${formatCurrency(o.subtotal)}</td>
                                </tr>
                                ${parseFloat(o.discount_amount) > 0 ? `
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-right">Giảm giá:</td>
                                    <td class="px-4 py-2 text-green-600">-${formatCurrency(o.discount_amount)}</td>
                                </tr>
                                ` : ''}
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-right">Phí ship:</td>
                                    <td class="px-4 py-2">${formatCurrency(o.shipping_fee)}</td>
                                </tr>
                                <tr class="font-bold">
                                    <td colspan="4" class="px-4 py-2 text-right">Tổng cộng:</td>
                                    <td class="px-4 py-2 text-axeron-red">${formatCurrency(o.total_amount)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button onclick="printInvoiceFromModal()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                            <span class="material-symbols-outlined">print</span>
                            In hóa đơn
                        </button>
                        <button onclick="closeModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Đóng</button>
                    </div>
                </div>
            `;

            openModal(modalContent);
            // Store current order ID for printing
            window.currentOrderId = orderId;
        }
    } catch (err) {
        showToast('Không thể tải thông tin đơn hàng!', 'error');
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount) + '₫';
}

// Select all checkbox
function toggleSelectAllOrders() {
    const selectAll = document.getElementById('select-all-orders');
    const checkboxes = document.querySelectorAll('.order-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
    updateSelectedCount();
}

// Update selected count display
function updateSelectedCount() {
    const checked = document.querySelectorAll('.order-checkbox:checked');
    document.getElementById('selected-count').textContent = checked.length;
}

// Listen for checkbox changes
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.order-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
});

// Export selected orders to Excel
function exportSelectedOrders() {
    const checked = document.querySelectorAll('.order-checkbox:checked');
    if (checked.length === 0) {
        showToast('Vui lòng chọn ít nhất một đơn hàng để xuất!', 'error');
        return;
    }

    showConfirm(`Bạn có chắc muốn xuất ${checked.length} đơn hàng đã chọn ra file Excel?`, async () => {
        const orderIds = Array.from(checked).map(cb => cb.value);
        showToast(`Đang chuẩn bị xuất ${orderIds.length} đơn hàng...`);
        console.log('Exporting orders:', orderIds);

        try {
            const formData = new FormData();
            formData.append('ajax_action', 'export_orders');
            formData.append('order_ids', JSON.stringify(orderIds));

            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success && result.data) {
                // Use SheetJS to create Excel file
                if (typeof XLSX !== 'undefined') {
                    exportToExcelWithSheetJS(result.data);
                } else {
                    // Fallback to CSV
                    exportToCSV(result.data);
                }
            } else {
                showToast(result.message || 'Có lỗi xảy ra!', 'error');
            }
        } catch (err) {
            console.error('Export error:', err);
            showToast('Có lỗi xảy ra khi xuất dữ liệu!', 'error');
        }
    });
}

// Export using SheetJS library
function exportToExcelWithSheetJS(orders) {
    // Prepare worksheet data
    const wsData = [
        ['Mã ĐH', 'Khách hàng', 'SĐT', 'Địa chỉ giao hàng', 'Tổng tiền', 'Thanh toán', 'Trạng thái', 'Ngày đặt', 'Sản phẩm']
    ];

    orders.forEach(function(order) {
        var products = order.items ? order.items.map(function(i) { return i.product_name; }).join(', ') : '';
        wsData.push([
            '#' + order.order_id,
            order.recipient_name,
            order.recipient_phone,
            order.shipping_address || '',
            order.total_amount,
            order.payment_status_text,
            order.order_status_text,
            order.created_at,
            products
        ]);
    });

    const ws = XLSX.utils.aoa_to_sheet(wsData);

    // Set column widths
    ws['!cols'] = [
        { wch: 10 }, // Mã ĐH
        { wch: 25 }, // Khách hàng
        { wch: 15 }, // SĐT
        { wch: 40 }, // Địa chỉ giao hàng
        { wch: 15 }, // Tổng tiền
        { wch: 15 }, // Thanh toán
        { wch: 15 }, // Trạng thái
        { wch: 18 }, // Ngày đặt
        { wch: 40 }  // Sản phẩm
    ];

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Danh sách đơn hàng');

    // Generate filename with date
    const date = new Date().toISOString().slice(0, 10);
    XLSX.writeFile(wb, 'DonHang_' + date + '.xlsx');

    showToast('Xuất Excel thành công!', 'success');
}

// CSV export fallback
function exportToCSV(orders) {
    let csv = '﻿'; // BOM for UTF-8
    csv += 'Mã ĐH,Khách hàng,SĐT,Địa chỉ giao hàng,Tổng tiền,Thanh toán,Trạng thái,Ngày đặt,Sản phẩm\n';

    orders.forEach(order => {
        const products = order.items ? order.items.map(i => i.product_name).join('; ') : '';
        const address = (order.shipping_address || '').replace(/"/g, '""');
        csv += `"#${order.order_id}","${order.recipient_name}","${order.recipient_phone}","${address}",${order.total_amount},"${order.payment_status_text}","${order.order_status_text}","${order.created_at}","${products}"\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'DonHang_' + new Date().toISOString().slice(0, 10) + '.csv';
    link.click();

    showToast('Xuất CSV thành công!', 'success');
}

// Print invoice for single order
async function printInvoice(orderId) {
    console.log('printInvoice called with orderId:', orderId);
    try {
        const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php?action=get_order&id=' + orderId);
        console.log('Response status:', response.status);
        const result = await response.json();
        console.log('Order result:', result);

        if (result.success && result.order) {
            generateAndPrintInvoice(result.order, result.items);
        } else {
            console.log('Order not found, result:', result);
            showToast('Khong tim thay don hang!', 'error');
        }
    } catch (err) {
        console.error('Print error:', err);
        showToast('Co loi xay ra khi tai hoa don!', 'error');
    }
}

// Print invoice from modal (uses stored order ID)
async function printInvoiceFromModal() {
    if (window.currentOrderId) {
        await printInvoice(window.currentOrderId);
    } else {
        showToast('Khong co thong tin don hang!', 'error');
    }
}

// Generate and print invoice HTML
function generateAndPrintInvoice(o, items) {
    // Helper function for currency (inline to work in new window)
    function fc(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + '₫';
    }

    let itemsHtml = '';
    if (items && items.length > 0) {
        for (var i = 0; i < items.length; i++) {
            itemsHtml += '<tr><td style="padding:8px;border:1px solid #ddd;">' + (i + 1) + '</td><td style="padding:8px;border:1px solid #ddd;">' + (items[i].product_name || 'N/A') + '</td><td style="padding:8px;border:1px solid #ddd;">' + (items[i].variant_info || '-') + '</td><td style="padding:8px;border:1px solid #ddd;text-align:center;">' + items[i].quantity + '</td><td style="padding:8px;border:1px solid #ddd;text-align:right;">' + fc(items[i].unit_price) + '</td><td style="padding:8px;border:1px solid #ddd;text-align:right;">' + fc(items[i].subtotal) + '</td></tr>';
        }
    } else {
        itemsHtml = '<tr><td colspan="6" style="padding:8px;border:1px solid #ddd;text-align:center;">Không có sản phẩm</td></tr>';
    }

    var discountLine = '';
    if (parseFloat(o.discount_amount) > 0) {
        discountLine = '<p>Giảm giá: <span style="color:#16a34a;">-' + fc(o.discount_amount) + '</span></p>';
    }

    var dateStr = new Date(o.created_at).toLocaleString('vi-VN');

    var invoiceHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Hóa đơn #' + o.order_id + '</title></head><body style="font-family:Arial,sans-serif;padding:20px;max-width:800px;margin:0 auto;"><style>@media print{body{margin:0;padding:0;}@page{margin:10mm;}}</style><div style="text-align:center;margin-bottom:30px;border-bottom:2px solid #dc2626;padding-bottom:20px;"><h1 style="color:#dc2626;margin:0;">AXERON SPORTS</h1><p style="margin:5px 0;">Địa chỉ: 123 Đường Thể thao, Quận 1, TP.HCM</p><p style="margin:5px 0;">Hotline: 0901 234 567</p></div><h2 style="text-align:center;margin:20px 0;color:#333;">HÓA ĐƠN BÁN HÀNG</h2><p style="text-align:center;margin-bottom:20px;">Mã đơn: <strong>#' + o.order_id + '</strong> | Ngày: ' + dateStr + '</p><div style="margin-bottom:20px;"><p><strong>Khách hàng:</strong> ' + o.recipient_name + '</p><p><strong>SĐT:</strong> ' + o.recipient_phone + '</p><p><strong>Địa chỉ giao hàng:</strong> ' + o.shipping_address + '</p></div><table style="width:100%;border-collapse:collapse;margin-bottom:20px;"><thead><tr style="background:#f3f4f6;"><th style="padding:8px;border:1px solid #ddd;text-align:left;">STT</th><th style="padding:8px;border:1px solid #ddd;text-align:left;">Sản phẩm</th><th style="padding:8px;border:1px solid #ddd;text-align:left;">Phân loại</th><th style="padding:8px;border:1px solid #ddd;text-align:center;">SL</th><th style="padding:8px;border:1px solid #ddd;text-align:right;">Đơn giá</th><th style="padding:8px;border:1px solid #ddd;text-align:right;">Thành tiền</th></tr></thead><tbody>' + itemsHtml + '</tbody></table><div style="text-align:right;"><p>Tạm tính: <strong>' + fc(o.subtotal) + '</strong></p>' + discountLine + '<p>Phí ship: ' + fc(o.shipping_fee) + '</p><p style="font-size:18px;"><strong>TỔNG CỘNG: ' + fc(o.total_amount) + '</strong></p></div><div style="margin-top:30px;text-align:center;"><button onclick="window.print()" style="padding:10px 30px;font-size:16px;background:#dc2626;color:white;border:none;border-radius:5px;cursor:pointer;">In hóa đơn</button> <button onclick="window.close()" style="padding:10px 30px;font-size:16px;background:#6b7280;color:white;border:none;border-radius:5px;cursor:pointer;">Đóng</button></div></body></html>';

    const w = 800;
    const h = 600;
    const left = (window.screen.width / 2) - (w / 2);
    const top = (window.screen.height / 2) - (h / 2);
    var printWindow = window.open('', '_blank', `width=${w},height=${h},top=${top},left=${left}`);
    if (!printWindow) {
        showToast('Vui lòng cho phép popup để in hóa đơn!', 'error');
        return;
    }
    printWindow.document.write(invoiceHtml);
    printWindow.document.close();
}

// Backward compatibility wrapper - uses dropdown version with instant update
async function updateOrderStatus(orderId, newStatus) {
    await updateOrderStatusFromDropdown(orderId, newStatus);
}

// Order status dropdown functions
function toggleStatusDropdown(e, orderId) {
    if (e) e.stopPropagation();

    // Close all other dropdowns first (both payment and status)
    document.querySelectorAll('.status-dropdown, .payment-dropdown').forEach(d => {
        if (d.id !== 'status-dropdown-' + orderId && d.id !== 'payment-dropdown-' + orderId) {
            d.classList.add('hidden');
        }
    });

    const dropdown = document.getElementById('status-dropdown-' + orderId);
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

// Close status dropdowns on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('.status-dropdown-btn') && !e.target.closest('.status-dropdown')) {
        document.querySelectorAll('.status-dropdown').forEach(d => {
            d.classList.add('hidden');
        });
    }
});

function updateOrderStatusFromDropdown(orderId, newStatus) {
    const statusLabels = {
        'pending': 'Chờ xử lý',
        'confirmed': 'Đã xác nhận',
        'processing': 'Đang xử lý',
        'shipped': 'Đang giao',
        'delivered': 'Đã giao',
        'cancelled': 'Đã hủy',
        'returned': 'Trả hàng'
    };

    // Close dropdown first
    document.getElementById('status-dropdown-' + orderId)?.classList.add('hidden');

    showConfirm(`Cập nhật đơn hàng #${orderId} sang trạng thái "${statusLabels[newStatus]}"?`, async () => {
        const formData = new FormData();
        formData.append('ajax_action', 'update_order_status');
        formData.append('order_id', orderId);
        formData.append('new_status', newStatus);

        try {
            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            showToast(result.message, result.success ? 'success' : 'error');

            if (result.success) {
                const btn = document.querySelector(`.status-dropdown-btn[data-order-id="${orderId}"]`);
                const dropdown = document.getElementById('status-dropdown-' + orderId);

                if (btn && dropdown) {
                    const statusClasses = {
                        'pending': 'bg-yellow-100 text-yellow-800',
                        'confirmed': 'bg-blue-100 text-blue-800',
                        'processing': 'bg-purple-100 text-purple-800',
                        'shipped': 'bg-indigo-100 text-indigo-800',
                        'delivered': 'bg-green-100 text-green-800',
                        'cancelled': 'bg-red-100 text-red-800',
                        'returned': 'bg-gray-100 text-gray-800'
                    };

                    const statusDots = {
                        'pending': 'bg-yellow-500',
                        'confirmed': 'bg-blue-500',
                        'processing': 'bg-purple-500',
                        'shipped': 'bg-indigo-500',
                        'delivered': 'bg-green-500',
                        'cancelled': 'bg-red-500',
                        'returned': 'bg-gray-500'
                    };

                    btn.className = `status-dropdown-btn px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClasses[newStatus]} hover:opacity-80 transition-opacity flex items-center gap-1 cursor-pointer whitespace-nowrap`;

                    const dot = btn.querySelector('.status-dot');
                    if (dot) dot.className = `status-dot w-2 h-2 rounded-full ${statusDots[newStatus]}`;

                    const textSpan = btn.querySelector('.status-text');
                    if (textSpan) textSpan.textContent = statusLabels[newStatus];

                    dropdown.querySelectorAll('button').forEach(b => {
                        b.classList.remove('bg-gray-50', 'font-medium');
                    });
                    
                    const statusOrder = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];
                    const currentIndex = statusOrder.indexOf(newStatus);
                    const buttons = dropdown.querySelectorAll('button');
                    if (buttons[currentIndex]) {
                        buttons[currentIndex].classList.add('bg-gray-50', 'font-medium');
                    }
                }
            }
        } catch (err) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    });
}

// Payment status dropdown functions
function togglePaymentDropdown(e, orderId) {
    if (e) e.stopPropagation();

    // Close all other dropdowns first
    document.querySelectorAll('.payment-dropdown').forEach(d => {
        if (d.id !== 'payment-dropdown-' + orderId) {
            d.classList.add('hidden');
        }
    });

    const dropdown = document.getElementById('payment-dropdown-' + orderId);
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

// Close all dropdowns on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('.payment-dropdown-btn') && !e.target.closest('.payment-dropdown')) {
        document.querySelectorAll('.payment-dropdown').forEach(d => {
            d.classList.add('hidden');
        });
    }
});

function updatePaymentStatus(orderId, newStatus) {
    const statusLabels = {
        'paid': 'Đã thanh toán',
        'unpaid': 'Chưa thanh toán',
        'refunded': 'Đã hoàn tiền'
    };

    // Close dropdown first
    document.getElementById('payment-dropdown-' + orderId)?.classList.add('hidden');

    showConfirm(`Cập nhật trạng thái thanh toán đơn hàng #${orderId} sang "${statusLabels[newStatus]}"?`, async () => {
        const formData = new FormData();
        formData.append('ajax_action', 'update_payment_status');
        formData.append('order_id', orderId);
        formData.append('new_payment_status', newStatus);

        try {
            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            showToast(result.message, result.success ? 'success' : 'error');

            if (result.success && result.new_status) {
                const btn = document.querySelector(`.payment-dropdown-btn[data-order-id="${orderId}"]`);
                const dropdown = document.getElementById('payment-dropdown-' + orderId);

                if (btn && dropdown) {
                    const statusClasses = {
                        'paid': 'bg-green-100 text-green-800',
                        'unpaid': 'bg-yellow-100 text-yellow-800',
                        'refunded': 'bg-gray-100 text-gray-800'
                    };

                    btn.className = `payment-dropdown-btn px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClasses[newStatus]} hover:opacity-80 transition-opacity flex items-center gap-1 cursor-pointer whitespace-nowrap`;

                    const textSpan = btn.querySelector('.payment-text');
                    if (textSpan) textSpan.textContent = statusLabels[newStatus];

                    dropdown.querySelectorAll('button').forEach(b => {
                        b.classList.remove('bg-gray-50', 'font-medium');
                    });
                    
                    const buttons = dropdown.querySelectorAll('button');
                    const statusIndex = { 'unpaid': 0, 'paid': 1, 'refunded': 2 };
                    if (buttons[statusIndex[newStatus]]) {
                        buttons[statusIndex[newStatus]].classList.add('bg-gray-50', 'font-medium');
                    }
                }
            }
        } catch (err) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    });
}
</script>
