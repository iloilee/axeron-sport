<?php
/**
 * Admin - Articles/News Management
 */
?>

<div class="mb-6 flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
    <!-- Filters -->
    <div class="flex gap-3 flex-wrap">
        <select id="article-category-filter" onchange="loadArticles()"
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Tất cả loại</option>
            <option value="news">Tin tức</option>
            <option value="blog">Blog</option>
            <option value="promotion">Khuyến mãi</option>
            <option value="announcement">Thông báo</option>
            <option value="guide">Hướng dẫn</option>
        </select>
        <select id="article-status-filter" onchange="loadArticles()"
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Tất cả trạng thái</option>
            <option value="published">Đã xuất bản</option>
            <option value="draft">Bản nháp</option>
        </select>
    </div>
    <a href="javascript:void(0)" onclick="openArticleModal()"
       class="px-4 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
        <span class="material-symbols-outlined text-xl">add</span>
        Viết Bài Mới
    </a>
</div>

<!-- Articles Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Bài viết</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Loại</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ngày đăng</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody id="articles-table-body" class="divide-y divide-gray-100">
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                        <span class="material-symbols-outlined text-4xl">article</span>
                        <p class="mt-2">Đang tải...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <div id="articles-pagination" class="p-4 border-t border-gray-100 flex justify-between items-center">
        <!-- Will be populated by JS -->
    </div>
</div>

<!-- Article Modal -->
<div id="article-modal" class="hidden fixed inset-0 z-50">
    <div class="fixed inset-0 bg-black/50" onclick="closeArticleModal()"></div>
    <div class="fixed inset-4 md:inset-8 lg:inset-y-4 lg:inset-x-1/4 bg-white rounded-xl shadow-2xl overflow-hidden flex flex-col">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-xl font-bold" id="article-modal-title">Viết Bài Mới</h3>
            <button onclick="closeArticleModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="article-form" class="p-6 overflow-y-auto flex-1">
            <input type="hidden" name="action" id="article-action" value="create_article">
            <input type="hidden" name="article_id" id="article-id" value="">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề *</label>
                        <input type="text" name="title" id="article-title" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none"
                               placeholder="Nhập tiêu đề bài viết">
                    </div>

                    <!-- Slug -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug URL</label>
                        <input type="text" name="slug" id="article-slug"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none"
                               placeholder="auto-generate-neu-trong">
                        <p class="text-xs text-gray-400 mt-1">Để trống để tự động tạo từ tiêu đề</p>
                    </div>

                    <!-- Excerpt -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tóm tắt</label>
                        <textarea name="excerpt" id="article-excerpt" rows="2"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none"
                                  placeholder="Tóm tắt ngắn cho danh sách bài viết"></textarea>
                    </div>

                    <!-- Content -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung</label>
                        <textarea name="content" id="article-content" rows="15"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none font-mono text-sm"
                                  placeholder="Nội dung HTML bài viết..."></textarea>
                        <p class="text-xs text-gray-400 mt-1">Hỗ trợ HTML tags: &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;...</p>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-4">
                    <!-- Status -->
                    <div class="bg-gray-50 p-4 rounded-lg space-y-3">
                        <h4 class="font-semibold text-gray-800">Trạng thái</h4>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_published" id="article-published" value="1" checked
                                   class="w-4 h-4 rounded text-axeron-red">
                            <label for="article-published" class="text-sm">Xuất bản ngay</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_featured" id="article-featured" value="1"
                                   class="w-4 h-4 rounded text-axeron-red">
                            <label for="article-featured" class="text-sm">Bài nổi bật</label>
                        </div>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Loại bài viết</label>
                        <select name="category" id="article-category" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="blog">Blog</option>
                            <option value="news">Tin tức</option>
                            <option value="promotion">Khuyến mãi</option>
                            <option value="announcement">Thông báo</option>
                            <option value="guide">Hướng dẫn</option>
                        </select>
                    </div>

                    <!-- Featured Image -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh đại diện</label>
                        <div id="article-dropzone" class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-axeron-red transition-colors cursor-pointer bg-gray-50">
                            <input type="file" id="article-image-input" accept="image/*" class="hidden">
                            <div id="article-upload-placeholder">
                                <span class="material-symbols-outlined text-3xl text-gray-400">add_photo_alternate</span>
                                <p class="text-xs text-gray-600 mt-1">Click để chọn ảnh</p>
                            </div>
                            <img id="article-image-preview" src="" alt="" class="max-h-32 mx-auto hidden">
                        </div>
                        <input type="hidden" name="featured_image" id="article-image-url" value="">
                    </div>

                    <!-- Tags -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                        <input type="text" name="tags" id="article-tags"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none"
                               placeholder="tag1, tag2, tag3">
                        <p class="text-xs text-gray-400 mt-1">Phân cách bằng dấu phẩy</p>
                    </div>

                    <!-- SEO -->
                    <div class="bg-gray-50 p-4 rounded-lg space-y-3">
                        <h4 class="font-semibold text-gray-800">SEO</h4>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Meta Title</label>
                            <input type="text" name="meta_title" id="article-meta-title"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-axeron-red outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Meta Description</label>
                            <textarea name="meta_description" id="article-meta-description" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-axeron-red outline-none"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeArticleModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Hủy</button>
                <button type="submit" class="px-6 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700">Lưu Bài Viết</button>
            </div>
        </form>
    </div>
</div>

<script>
let articles = [];
let currentPage = 1;
let totalPages = 1;

// Category labels
const categoryLabels = {
    'blog': { text: 'Blog', color: 'bg-blue-100 text-blue-700' },
    'news': { text: 'Tin tức', color: 'bg-green-100 text-green-700' },
    'promotion': { text: 'Khuyến mãi', color: 'bg-red-100 text-red-700' },
    'announcement': { text: 'Thông báo', color: 'bg-yellow-100 text-yellow-700' },
    'guide': { text: 'Hướng dẫn', color: 'bg-purple-100 text-purple-700' }
};

// Load articles
async function loadArticles(page = 1) {
    currentPage = page;
    const category = document.getElementById('article-category-filter').value;
    const status = document.getElementById('article-status-filter').value;

    try {
        let url = '<?= BASE_URL ?>/api/cms-api.php?action=articles&page=' + page;
        if (category) url += '&category=' + category;
        if (status === 'published') url += '&is_published=1';
        if (status === 'draft') url += '&is_published=0';

        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            articles = result.data.articles;
            totalPages = result.data.total_pages;
            renderArticles();
            renderPagination();
        }
    } catch (error) {
        console.error('Error loading articles:', error);
    }
}

// Render articles table
function renderArticles() {
    const tbody = document.getElementById('articles-table-body');

    if (articles.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                    <span class="material-symbols-outlined text-4xl">article</span>
                    <p class="mt-2">Chưa có bài viết nào</p>
                    <p class="text-sm">Click "Viết Bài Mới" để tạo bài viết đầu tiên</p>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = articles.map(article => {
        const cat = categoryLabels[article.category] || { text: article.category, color: 'bg-gray-100 text-gray-700' };
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        ${article.featured_image ? `
                            <img src="${getImageUrl(article.featured_image)}" alt="" class="w-12 h-12 object-cover rounded-lg bg-gray-100">
                        ` : `
                            <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-gray-400">image</span>
                            </div>
                        `}
                        <div>
                            <p class="font-medium text-gray-800">${article.title}</p>
                            <p class="text-xs text-gray-500 truncate max-w-md">${article.excerpt || 'Không có tóm tắt'}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium inline-block whitespace-nowrap ${cat.color}">${cat.text}</span>
                    ${article.is_featured ? '<span class="ml-1 px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium inline-block whitespace-nowrap">Nổi bật</span>' : ''}
                </td>
                <td class="px-4 py-3">
                    ${article.is_published ? `
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium inline-block whitespace-nowrap">Đã đăng</span>
                    ` : `
                        <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium inline-block whitespace-nowrap">Nháp</span>
                    `}
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">
                    ${article.published_at ? new Date(article.published_at).toLocaleDateString('vi-VN') : '-'}
                </td>
                <td class="px-4 py-3">
                    <div class="flex gap-1">
                        <a href="<?= BASE_URL ?>/blog/news.php?slug=${article.slug}" target="_blank"
                           class="p-2 hover:bg-gray-100 rounded-lg" title="Xem">
                            <span class="material-symbols-outlined text-gray-600 text-sm">visibility</span>
                        </a>
                        <button onclick="editArticle(${article.article_id})" class="p-2 hover:bg-gray-100 rounded-lg" title="Sửa">
                            <span class="material-symbols-outlined text-gray-600 text-sm">edit</span>
                        </button>
                        <button onclick="deleteArticle(${article.article_id})" class="p-2 hover:bg-red-50 rounded-lg" title="Xóa">
                            <span class="material-symbols-outlined text-red-500 text-sm">delete</span>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Render pagination
function renderPagination() {
    const container = document.getElementById('articles-pagination');

    if (totalPages <= 1) {
        container.innerHTML = `<span class="text-sm text-gray-500">${articles.length} bài viết</span><div></div>`;
        return;
    }

    let pages = [];
    for (let i = 1; i <= totalPages; i++) {
        pages.push(i);
    }

    container.innerHTML = `
        <span class="text-sm text-gray-500">Trang ${currentPage} / ${totalPages}</span>
        <div class="flex gap-1">
            <button onclick="loadArticles(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}
                    class="px-3 py-1 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="material-symbols-outlined text-sm">chevron_left</span>
            </button>
            ${pages.map(p => `
                <button onclick="loadArticles(${p})"
                        class="px-3 py-1 rounded ${p === currentPage ? 'bg-axeron-red text-white' : 'hover:bg-gray-100'}">
                    ${p}
                </button>
            `).join('')}
            <button onclick="loadArticles(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}
                    class="px-3 py-1 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="material-symbols-outlined text-sm">chevron_right</span>
            </button>
        </div>
    `;
}

// Open modal for new article
function openArticleModal() {
    document.getElementById('article-modal-title').textContent = 'Viết Bài Mới';
    document.getElementById('article-action').value = 'create_article';
    document.getElementById('article-form').reset();
    document.getElementById('article-id').value = '';
    document.getElementById('article-image-url').value = '';
    document.getElementById('article-image-preview').classList.add('hidden');
    document.getElementById('article-upload-placeholder').classList.remove('hidden');
    document.getElementById('article-published').checked = false;
    document.getElementById('article-modal').classList.remove('hidden');
}

// Edit existing article
async function editArticle(articleId) {
    try {
        const response = await fetch('<?= BASE_URL ?>/api/cms-api.php?action=article&id=' + articleId);
        const result = await response.json();

        if (result.success && result.data.article) {
            const article = result.data.article;

            document.getElementById('article-modal-title').textContent = 'Sửa Bài Viết';
            document.getElementById('article-action').value = 'update_article';
            document.getElementById('article-id').value = articleId;
            document.getElementById('article-title').value = article.title || '';
            document.getElementById('article-slug').value = article.slug || '';
            document.getElementById('article-excerpt').value = article.excerpt || '';
            document.getElementById('article-content').value = article.content || '';
            document.getElementById('article-category').value = article.category || 'blog';
            document.getElementById('article-tags').value = article.tags || '';
            document.getElementById('article-meta-title').value = article.meta_title || '';
            document.getElementById('article-meta-description').value = article.meta_description || '';
            document.getElementById('article-published').checked = article.is_published == 1;
            document.getElementById('article-featured').checked = article.is_featured == 1;

            if (article.featured_image) {
                document.getElementById('article-image-url').value = article.featured_image;
                document.getElementById('article-image-preview').src = getImageUrl(article.featured_image);
                document.getElementById('article-image-preview').classList.remove('hidden');
                document.getElementById('article-upload-placeholder').classList.add('hidden');
            }

            document.getElementById('article-modal').classList.remove('hidden');
        }
    } catch (error) {
        showToast('Không thể tải bài viết!', 'error');
    }
}

// Close modal
function closeArticleModal() {
    document.getElementById('article-modal').classList.add('hidden');
}

// Handle form submit
document.getElementById('article-form').addEventListener('submit', async function(e) {
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
            closeArticleModal();
            loadArticles(currentPage);
        } else {
            showToast(result.message || 'Có lỗi xảy ra!', 'error');
        }
    } catch (error) {
        showToast('Có lỗi xảy ra!', 'error');
    }
});

// Delete article
function deleteArticle(articleId) { 
    showConfirm('Bạn có chắc muốn xóa bài viết này?', async () => { 
        try {
            const formData = new FormData();
            formData.append('action', 'delete_article');
            formData.append('article_id', articleId);

            const response = await fetch('<?= BASE_URL ?>/api/cms-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                showToast(result.message, 'success');
                loadArticles(currentPage);
            } else {
                showToast(result.message || 'Có lỗi xảy ra!', 'error');
            }
        } catch (error) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    });
}

// Image upload handling
const articleDropzone = document.getElementById('article-dropzone');
const articleImageInput = document.getElementById('article-image-input');
const articleImagePreview = document.getElementById('article-image-preview');
const articleUploadPlaceholder = document.getElementById('article-upload-placeholder');
const articleImageUrl = document.getElementById('article-image-url');

articleDropzone.addEventListener('click', () => articleImageInput.click());

articleDropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    articleDropzone.classList.add('border-axeron-red', 'bg-red-50');
});

articleDropzone.addEventListener('dragleave', () => {
    articleDropzone.classList.remove('border-axeron-red', 'bg-red-50');
});

articleDropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    articleDropzone.classList.remove('border-axeron-red', 'bg-red-50');
    if (e.dataTransfer.files.length > 0) {
        handleArticleImageUpload(e.dataTransfer.files[0]);
    }
});

articleImageInput.addEventListener('change', () => {
    if (articleImageInput.files.length > 0) {
        handleArticleImageUpload(articleImageInput.files[0]);
    }
});

async function handleArticleImageUpload(file) {
    if (!file.type.startsWith('image/')) {
        showToast('Vui lòng chọn file ảnh!', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'upload_article_image');
    formData.append('image', file);

    try {
        const response = await fetch('<?= BASE_URL ?>/api/cms-api.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            articleImageUrl.value = result.data.url;
            articleImagePreview.src = getImageUrl(result.data.url);
            articleImagePreview.classList.remove('hidden');
            articleUploadPlaceholder.classList.add('hidden');
            showToast('Upload ảnh thành công!');
        } else {
            showToast(result.message || 'Upload thất bại!', 'error');
        }
    } catch (error) {
        showToast('Upload thất bại!', 'error');
    }
}

// Auto-generate slug from title
document.getElementById('article-title').addEventListener('blur', function() {
    const slugInput = document.getElementById('article-slug');
    if (!slugInput.value) {
        slugInput.value = this.value
            .toLowerCase()
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }
});

// Load articles on page load
loadArticles();
</script>
