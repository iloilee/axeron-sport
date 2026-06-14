<?php
/**
 * Admin - Site Settings Management
 */
?>

<!-- Settings Tabs -->
<div class="mb-6">
    <div class="border-b border-gray-200">
        <nav class="flex gap-4 -mb-px">
            <button onclick="switchSettingsTab('general')" id="tab-general"
                    class="settings-tab px-4 py-2 border-b-2 border-axeron-red text-axeron-red font-medium">
                Cài đặt chung
            </button>
            <button onclick="switchSettingsTab('contact')" id="tab-contact"
                    class="settings-tab px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                Liên hệ
            </button>
            <button onclick="switchSettingsTab('social')" id="tab-social"
                    class="settings-tab px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                Mạng xã hội
            </button>
            <button onclick="switchSettingsTab('footer')" id="tab-footer"
                    class="settings-tab px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                Footer
            </button>
            <button onclick="switchSettingsTab('sales')" id="tab-sales"
                    class="settings-tab px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                Kinh doanh
            </button>
        </nav>
    </div>
</div>

<!-- Settings Forms -->
<div id="settings-content">
    <!-- General Settings -->
    <div id="settings-general" class="settings-panel">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">settings</span>
                Cài đặt chung
            </h3>
            <div class="space-y-4" id="general-settings-fields">
                <!-- Loaded via JS -->
            </div>
        </div>
    </div>

    <!-- Contact Settings -->
    <div id="settings-contact" class="settings-panel hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">contact_phone</span>
                Thông tin liên hệ
            </h3>
            <div class="space-y-4" id="contact-settings-fields">
                <!-- Loaded via JS -->
            </div>
        </div>
    </div>

    <!-- Social Settings -->
    <div id="settings-social" class="settings-panel hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">share</span>
                Mạng xã hội
            </h3>
            <div class="space-y-4" id="social-settings-fields">
                <!-- Loaded via JS -->
            </div>
        </div>
    </div>

    <!-- Footer Settings -->
    <div id="settings-footer" class="settings-panel hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">sticky_note_2</span>
                Nội dung Footer
            </h3>
            <div class="space-y-4" id="footer-settings-fields">
                <!-- Loaded via JS -->
            </div>
        </div>
    </div>

    <!-- Sales Settings -->
    <div id="settings-sales" class="settings-panel hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">storefront</span>
                Kinh doanh & Bán hàng
            </h3>
            <div class="space-y-4" id="sales-settings-fields">
                <!-- Loaded via JS -->
            </div>
        </div>
    </div>
</div>

<!-- Save Button -->
<div class="mt-6 flex justify-end">
    <button onclick="saveSettings()" class="px-6 py-3 bg-axeron-red text-white rounded-lg hover:bg-red-700 flex items-center gap-2">
        <span class="material-symbols-outlined">save</span>
        Lưu thay đổi
    </button>
</div>

<!-- Image Upload Modal -->
<div id="image-upload-modal" class="hidden fixed inset-0 z-50">
    <div class="fixed inset-0 bg-black/50" onclick="closeImageUpload()"></div>
    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-xl shadow-2xl overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold">Upload Hình ảnh</h3>
            <button onclick="closeImageUpload()" class="p-2 hover:bg-gray-100 rounded-lg">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="p-6">
            <div id="settings-dropzone" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-axeron-red transition-colors cursor-pointer bg-gray-50">
                <input type="file" id="settings-image-input" accept="image/*" class="hidden">
                <div id="settings-upload-placeholder">
                    <span class="material-symbols-outlined text-5xl text-gray-400">cloud_upload</span>
                    <p class="text-sm text-gray-600 mt-2">Kéo thả ảnh hoặc click để chọn</p>
                    <p class="text-xs text-gray-400">PNG, JPG, GIF, WebP (tối đa 10MB)</p>
                </div>
                <img id="settings-image-preview" src="" alt="" class="max-h-48 mx-auto hidden">
            </div>
            <div id="settings-upload-progress" class="hidden mt-4">
                <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg">
                    <div class="animate-spin">
                        <span class="material-symbols-outlined text-blue-500">progress_activity</span>
                    </div>
                    <span class="text-sm text-blue-700">Đang upload...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let settingsData = {};
let currentImageField = null;

// Load all settings
async function loadSettings() {
    try {
        const response = await fetch('<?= BASE_URL ?>/api/cms-api.php?action=settings');
        const result = await response.json();

        if (result.success) {
            settingsData = result.data.settings;
            renderAllSettings();
        }
    } catch (error) {
        console.error('Error loading settings:', error);
        showToast('Không thể tải cài đặt!', 'error');
    }
}

// Render all settings groups
function renderAllSettings() {
    renderSettingsGroup('general', document.getElementById('general-settings-fields'));
    renderSettingsGroup('contact', document.getElementById('contact-settings-fields'));
    renderSettingsGroup('social', document.getElementById('social-settings-fields'));
    renderSettingsGroup('footer', document.getElementById('footer-settings-fields'));
    renderSettingsGroup('sales', document.getElementById('sales-settings-fields'));
}

// Render settings for a group
function renderSettingsGroup(group, container) {
    const groupSettings = settingsData.filter(s => s.group_name === group);

    if (groupSettings.length === 0) {
        container.innerHTML = '<p class="text-gray-500">Chưa có cài đặt nào</p>';
        return;
    }

    container.innerHTML = groupSettings.map(setting => {
        const value = setting.setting_value || '';
        const id = 'setting_' + setting.setting_key;

        let inputHtml = '';

        switch (setting.setting_type) {
            case 'textarea':
                inputHtml = `
                    <textarea id="${id}" name="${setting.setting_key}" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none"
                              placeholder="${setting.description || ''}">${value}</textarea>
                `;
                break;
            case 'image':
                inputHtml = `
                    <div class="flex gap-3">
                        <div class="flex-1">
                            <input type="text" id="${id}" name="${setting.setting_key}" value="${value}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none"
                                   placeholder="URL hoặc upload ảnh"
                                   onchange="const p = document.getElementById('preview_${setting.setting_key}'); if(this.value) { p.src = getImageUrl(this.value); p.classList.remove('hidden'); } else { p.classList.add('hidden'); }">
                        </div>
                        <button type="button" onclick="openImageUpload('${setting.setting_key}')"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg border border-gray-300">
                            <span class="material-symbols-outlined text-gray-600">upload</span>
                        </button>
                    </div>
                    <img id="preview_${setting.setting_key}" src="${value ? getImageUrl(value) : ''}" alt="" class="mt-2 max-h-24 rounded-lg border border-gray-200 ${value ? '' : 'hidden'}">
                `;
                break;
            case 'boolean':
                inputHtml = `
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="${id}" name="${setting.setting_key}" value="1" ${value ? 'checked' : ''}
                               class="w-4 h-4 rounded text-axeron-red">
                        <span class="text-sm text-gray-600">${setting.description || 'Bật/Tắt'}</span>
                    </label>
                `;
                break;
            default:
                inputHtml = `
                    <input type="${setting.setting_type === 'number' ? 'number' : 'text'}"
                           id="${id}" name="${setting.setting_key}" value="${value}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none"
                           placeholder="${setting.description || ''}">
                `;
        }

        return `
            <div class="setting-field">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    ${setting.display_name || setting.setting_key}
                </label>
                ${inputHtml}
                ${setting.description && setting.setting_type !== 'image' ? `
                    <p class="text-xs text-gray-400 mt-1">${setting.description}</p>
                ` : ''}
            </div>
        `;
    }).join('');
}

// Switch tabs
function switchSettingsTab(tab) {
    // Update tab styles
    document.querySelectorAll('.settings-tab').forEach(t => {
        t.classList.remove('border-axeron-red', 'text-axeron-red');
        t.classList.add('border-transparent', 'text-gray-500');
    });
    document.getElementById('tab-' + tab).classList.remove('border-transparent', 'text-gray-500');
    document.getElementById('tab-' + tab).classList.add('border-axeron-red', 'text-axeron-red');

    // Show/hide panels
    document.querySelectorAll('.settings-panel').forEach(p => p.classList.add('hidden'));
    document.getElementById('settings-' + tab).classList.remove('hidden');
}

// Save settings
async function saveSettings() {
    const formData = new FormData();
    formData.append('action', 'update_settings');

    // Collect all settings values
    const settings = {};

    settingsData.forEach(setting => {
        const input = document.getElementById('setting_' + setting.setting_key);
        if (input) {
            if (input.type === 'checkbox') {
                settings[setting.setting_key] = input.checked ? '1' : '0';
            } else {
                settings[setting.setting_key] = input.value || '';
            }
        }
    });

    formData.append('settings', JSON.stringify(settings));

    try {
        const response = await fetch('<?= BASE_URL ?>/api/cms-api.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showToast('Lưu cài đặt thành công!', 'success');
        } else {
            showToast(result.message || 'Có lỗi xảy ra!', 'error');
        }
    } catch (error) {
        showToast('Có lỗi xảy ra!', 'error');
    }
}

// Image upload
function openImageUpload(fieldKey) {
    currentImageField = fieldKey;
    document.getElementById('settings-image-input').value = '';
    document.getElementById('settings-image-preview').classList.add('hidden');
    document.getElementById('settings-upload-placeholder').classList.remove('hidden');
    document.getElementById('settings-upload-progress').classList.add('hidden');
    document.getElementById('image-upload-modal').classList.remove('hidden');
}

function closeImageUpload() {
    document.getElementById('image-upload-modal').classList.add('hidden');
    currentImageField = null;
}

// Settings image upload handling
const settingsDropzone = document.getElementById('settings-dropzone');
const settingsImageInput = document.getElementById('settings-image-input');
const settingsImagePreview = document.getElementById('settings-image-preview');
const settingsUploadPlaceholder = document.getElementById('settings-upload-placeholder');
const settingsUploadProgress = document.getElementById('settings-upload-progress');

settingsDropzone.addEventListener('click', () => settingsImageInput.click());

settingsDropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    settingsDropzone.classList.add('border-axeron-red', 'bg-red-50');
});

settingsDropzone.addEventListener('dragleave', () => {
    settingsDropzone.classList.remove('border-axeron-red', 'bg-red-50');
});

settingsDropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    settingsDropzone.classList.remove('border-axeron-red', 'bg-red-50');
    if (e.dataTransfer.files.length > 0) {
        uploadSettingsImage(e.dataTransfer.files[0]);
    }
});

settingsImageInput.addEventListener('change', () => {
    if (settingsImageInput.files.length > 0) {
        uploadSettingsImage(settingsImageInput.files[0]);
    }
});

async function uploadSettingsImage(file) {
    if (!file.type.startsWith('image/')) {
        showToast('Vui lòng chọn file ảnh!', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'upload_banner_image');
    formData.append('image', file);

    settingsUploadProgress.classList.remove('hidden');

    try {
        const response = await fetch('<?= BASE_URL ?>/api/cms-api.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            // Update the input field
            const input = document.getElementById('setting_' + currentImageField);
            if (input) {
                input.value = result.data.url;
                
                // Update the form preview image
                const formPreview = document.getElementById('preview_' + currentImageField);
                if (formPreview) {
                    formPreview.src = getImageUrl(result.data.url);
                    formPreview.classList.remove('hidden');
                }
            }

            // Update modal preview
            settingsImagePreview.src = getImageUrl(result.data.url);
            settingsImagePreview.classList.remove('hidden');
            settingsUploadPlaceholder.classList.add('hidden');

            showToast('Upload ảnh thành công!');
            setTimeout(closeImageUpload, 1000);
        } else {
            showToast(result.message || 'Upload thất bại!', 'error');
        }
    } catch (error) {
        showToast('Upload thất bại!', 'error');
    } finally {
        settingsUploadProgress.classList.add('hidden');
    }
}

// Load settings on page load
loadSettings();
</script>
