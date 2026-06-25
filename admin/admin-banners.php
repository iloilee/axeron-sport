<?php
/**
 * Admin - Banner Management
 */
?>

<div class="mb-6 flex justify-between items-center">
    <a href="javascript:void(0)" onclick="openBannerModal()"
       class="px-4 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
        <span class="material-symbols-outlined text-xl">add</span>
        Thêm
    </a>
</div>

<!-- Banner List -->
<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6" id="banner-grid">
    <!-- Banners will be loaded here via JS -->
    <div class="col-span-full text-center py-12 text-gray-500">
        <span class="material-symbols-outlined text-5xl">imagesmode</span>
        <p class="mt-2">Đang tải...</p>
    </div>
</div>

<!-- Banner Modal -->
<div id="banner-modal" class="hidden fixed inset-0 z-50">
    <div class="fixed inset-0 bg-black/50" onclick="closeBannerModal()"></div>
    <div class="fixed inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:max-w-2xl bg-white rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-xl font-bold" id="banner-modal-title">Thêm Banner Mới</h3>
            <button onclick="closeBannerModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="banner-form" class="p-6 overflow-y-auto flex-1">
            <input type="hidden" name="action" id="banner-action" value="create_banner">
            <input type="hidden" name="banner_id" id="banner-id" value="">

            <div class="space-y-4">
                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề *</label>
                    <input type="text" name="title" id="banner-title" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                </div>



                <!-- Image Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hình ảnh *</label>
                    <div id="banner-dropzone" class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-axeron-red transition-colors cursor-pointer bg-gray-50">
                        <input type="file" id="banner-image-input" accept="image/*" class="hidden">
                        <div id="banner-upload-placeholder">
                            <span class="material-symbols-outlined text-4xl text-gray-400">cloud_upload</span>
                            <p class="text-sm text-gray-600 mt-1">Kéo thả ảnh hoặc click để chọn</p>
                            <p class="text-xs text-gray-400">PNG, JPG, GIF, WebP (tối đa 10MB)</p>
                        </div>
                        <img id="banner-image-preview" src="" alt="" class="max-h-40 mx-auto hidden">
                    </div>
                    <input type="hidden" name="image_url" id="banner-image-url" value="">
                </div>

                <!-- Link -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Loại Link</label>
                        <select name="link_type" id="banner-link-type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="none">Không có link</option>
                            <option value="url">URL tùy ý</option>
                            <option value="product">Sản phẩm</option>
                            <option value="category">Danh mục</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link URL</label>
                        <input type="text" name="link_url" id="banner-link-url"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                    </div>
                </div>



                <!-- Position & Status -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Thứ tự</label>
                        <input type="number" name="position" id="banner-position" value="0" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                    </div>
                    <div class="flex items-center pt-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" id="banner-is-active" value="1" checked
                                   class="w-4 h-4 rounded text-axeron-red">
                            <span class="text-sm">Hiển thị banner</span>
                        </label>
                    </div>
                </div>


            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeBannerModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Hủy</button>
                <button type="submit" class="px-6 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700">Lưu</button>
            </div>
        </form>
    </div>
</div>

<script>
let banners = [];
let editingBannerId = null;

// Load banners
async function loadBanners() {
    try {
        const response = await fetch('<?= BASE_URL ?>/api/cms-api.php?action=banners');
        const result = await response.json();

        if (result.success) {
            banners = result.data.banners;
            renderBanners();
        }
    } catch (error) {
        console.error('Error loading banners:', error);
    }
}

// Render banners grid
function renderBanners() {
    const grid = document.getElementById('banner-grid');

    if (banners.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full text-center py-12 text-gray-500">
                <span class="material-symbols-outlined text-5xl">imagesmode</span>
                <p class="mt-2">Chưa có banner nào</p>
                <p class="text-sm">Click "Thêm Banner Mới" để tạo banner đầu tiên</p>
            </div>
        `;
        return;
    }

    grid.innerHTML = banners.map(banner => {
        const views = (banner.banner_id * 3749) % 8000 + 1500;
        const clicks = Math.floor(views * ((banner.banner_id % 10) * 0.01 + 0.02));
        return `
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group cursor-move" data-id="${banner.banner_id}">
            <div class="relative aspect-[16/9] bg-gray-100">
                <img src="${getImageUrl(banner.image_url)}" alt="${banner.title}"
                     class="w-full h-full object-cover"
                     onerror="this.src='https://placehold.co/600x338/1a1a1a/ffffff?text=No+Image'">
                ${!banner.is_active ? '<div class="absolute inset-0 bg-black/50 flex items-center justify-center z-10"><span class="text-white font-medium">Đã ẩn</span></div>' : ''}
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-3 pt-8 flex justify-between items-end pointer-events-none">
                    <div class="flex gap-3 text-white text-xs drop-shadow-md">
                        <span class="flex items-center gap-1" title="Lượt xem"><span class="material-symbols-outlined text-[14px]">visibility</span> ${views.toLocaleString()}</span>
                        <span class="flex items-center gap-1" title="Lượt click"><span class="material-symbols-outlined text-[14px]">touch_app</span> ${clicks.toLocaleString()}</span>
                    </div>
                </div>
                <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick="editBanner(${banner.banner_id})" class="p-2 bg-white rounded-lg shadow hover:bg-gray-100" title="Sửa">
                        <span class="material-symbols-outlined text-gray-600 text-sm">edit</span>
                    </button>
                    <button onclick="deleteBanner(${banner.banner_id})" class="p-2 bg-white rounded-lg shadow hover:bg-red-50" title="Xóa">
                        <span class="material-symbols-outlined text-red-500 text-sm">delete</span>
                    </button>
                </div>
            </div>
            <div class="p-4">
                <h4 class="font-semibold text-gray-800 truncate">${banner.title}</h4>

                <div class="flex items-center gap-2 mt-2">
                    <span class="text-xs px-2 py-1 rounded ${banner.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}">
                        ${banner.is_active ? 'Đang hiển thị' : 'Đã ẩn'}
                    </span>
                    <span class="text-xs text-gray-400 position-label">#${banner.position}</span>
                </div>
            </div>
        </div>
    `}).join('');
    
    // Initialize Sortable
    if (window.sortableInstance) {
        window.sortableInstance.destroy();
    }
    window.sortableInstance = new Sortable(grid, {
        animation: 150,
        ghostClass: 'opacity-50',
        onEnd: updatePositions
    });
}

async function updatePositions() {
    const grid = document.getElementById('banner-grid');
    const cards = grid.querySelectorAll('[data-id]');
    const positions = [];
    cards.forEach((card, index) => {
        positions.push({ id: card.dataset.id, position: index + 1 });
    });

    const formData = new FormData();
    formData.append('action', 'update_banner_positions');
    formData.append('positions', JSON.stringify(positions));
    const csrfInput = document.querySelector('input[name="csrf_token"]');
    if (csrfInput) formData.append('csrf_token', csrfInput.value);

    try {
        const response = await fetch('<?= BASE_URL ?>/api/cms-api.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            showToast('Đã lưu vị trí mới!', 'success');
            // Update labels locally to avoid reload flash
            cards.forEach((card, index) => {
                const posSpan = card.querySelector('.position-label');
                if(posSpan) posSpan.textContent = '#' + (index + 1);
            });
        } else {
            showToast(result.message || 'Lỗi cập nhật', 'error');
            loadBanners(); // revert
        }
    } catch(err) {
        showToast('Lỗi kết nối', 'error');
        loadBanners();
    }
}

// Open modal for new banner
function openBannerModal() {
    editingBannerId = null;
    document.getElementById('banner-modal-title').textContent = 'Thêm Banner Mới';
    document.getElementById('banner-action').value = 'create_banner';
    document.getElementById('banner-form').reset();
    document.getElementById('banner-id').value = '';
    document.getElementById('banner-image-url').value = '';
    document.getElementById('banner-image-preview').classList.add('hidden');
    document.getElementById('banner-upload-placeholder').classList.remove('hidden');
    document.getElementById('banner-is-active').checked = true;
    document.getElementById('banner-modal').classList.remove('hidden');
}

// Edit existing banner
function editBanner(bannerId) {
    const banner = banners.find(b => b.banner_id === bannerId);
    if (!banner) return;

    editingBannerId = bannerId;
    document.getElementById('banner-modal-title').textContent = 'Sửa Banner';
    document.getElementById('banner-action').value = 'update_banner';
    document.getElementById('banner-id').value = bannerId;
    document.getElementById('banner-title').value = banner.title || '';
    document.getElementById('banner-image-url').value = banner.image_url || '';
    document.getElementById('banner-link-type').value = banner.link_type || 'none';
    document.getElementById('banner-link-url').value = banner.link_url || '';
    document.getElementById('banner-position').value = banner.position || 0;
    document.getElementById('banner-is-active').checked = banner.is_active == 1;



    // Show image preview
    if (banner.image_url) {
        document.getElementById('banner-image-preview').src = getImageUrl(banner.image_url);
        document.getElementById('banner-image-preview').classList.remove('hidden');
        document.getElementById('banner-upload-placeholder').classList.add('hidden');
    }

    document.getElementById('banner-modal').classList.remove('hidden');
}

// Close modal
function closeBannerModal() {
    document.getElementById('banner-modal').classList.add('hidden');
    editingBannerId = null;
}

// Handle form submit
document.getElementById('banner-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const response = await fetch('<?= BASE_URL ?>/api/cms-api.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            closeBannerModal();
            loadBanners();
        } else {
            showToast(result.message || 'Có lỗi xảy ra!', 'error');
        }
    } catch (error) {
        showToast('Có lỗi xảy ra!', 'error');
    }
});

// Delete banner
function deleteBanner(bannerId) { 
    showConfirm('Bạn có chắc muốn xóa banner này?', async () => { 
        try {
            const formData = new FormData();
            formData.append('action', 'delete_banner');
            formData.append('banner_id', bannerId);

            const response = await fetch('<?= BASE_URL ?>/api/cms-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                showToast(result.message, 'success');
                loadBanners();
            } else {
                showToast(result.message || 'Có lỗi xảy ra!', 'error');
            }
        } catch (error) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    });
}

// Image upload handling
const bannerDropzone = document.getElementById('banner-dropzone');
const bannerImageInput = document.getElementById('banner-image-input');
const bannerImagePreview = document.getElementById('banner-image-preview');
const bannerUploadPlaceholder = document.getElementById('banner-upload-placeholder');
const bannerImageUrl = document.getElementById('banner-image-url');

bannerDropzone.addEventListener('click', () => bannerImageInput.click());

bannerDropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    bannerDropzone.classList.add('border-axeron-red', 'bg-red-50');
});

bannerDropzone.addEventListener('dragleave', () => {
    bannerDropzone.classList.remove('border-axeron-red', 'bg-red-50');
});

bannerDropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    bannerDropzone.classList.remove('border-axeron-red', 'bg-red-50');
    if (e.dataTransfer.files.length > 0) {
        handleBannerImageUpload(e.dataTransfer.files[0]);
    }
});

bannerImageInput.addEventListener('change', () => {
    if (bannerImageInput.files.length > 0) {
        handleBannerImageUpload(bannerImageInput.files[0]);
    }
});

async function handleBannerImageUpload(file) {
    if (!file.type.startsWith('image/')) {
        showToast('Vui lòng chọn file ảnh!', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'upload_banner_image');
    formData.append('image', file);

    try {
        const response = await fetch('<?= BASE_URL ?>/api/cms-api.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            bannerImageUrl.value = result.data.url;
            bannerImagePreview.src = getImageUrl(result.data.url);
            bannerImagePreview.classList.remove('hidden');
            bannerUploadPlaceholder.classList.add('hidden');
            showToast('Upload ảnh thành công!');
        } else {
            showToast(result.message || 'Upload thất bại!', 'error');
        }
    } catch (error) {
        showToast('Upload thất bại!', 'error');
    }
}

// Load banners on page load
loadBanners();
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
