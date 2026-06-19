<script>
    // Global Dark Mode Init
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
</script>
<style>
    /* Dark Mode Global Overrides */
    html.dark body { background-color: #202124 !important; color: #e8eaed !important; }
    html.dark .bg-surface, html.dark .bg-white { background-color: #2D2E30 !important; color: #e8eaed !important; border-color: #5f6368 !important; }
    html.dark .text-on-surface, html.dark .text-gray-900, html.dark .text-gray-800, html.dark .text-gray-700, html.dark .text-gray-600, html.dark .text-gray-500 { color: #e8eaed !important; }
    html.dark .text-on-surface-variant, html.dark .text-text-dark, html.dark .text-on-background, html.dark .text-\[\#1b1c1c\], html.dark .text-\[\#5b403f\], html.dark .text-\[\#4a4a4a\], html.dark .text-\[\#333\], html.dark .text-\[\#222\], html.dark .text-\[\#6B7280\], html.dark .text-\[\#8f6f6e\] { color: #e8eaed !important; }
    html.dark .border-outline-variant, html.dark .border-gray-200, html.dark .border-gray-100, html.dark .border-gray-300, html.dark .border-\[\#e5e2e1\], html.dark .border-\[\#e3bebb\] { border-color: #5f6368 !important; }
    html.dark .bg-surface-container-lowest, html.dark .bg-surface-lowest, html.dark .bg-gray-50, html.dark .bg-surface-container, html.dark .bg-surface-container-high, html.dark .bg-surface-container-low, html.dark .bg-surface-container-lowest\/80, html.dark .bg-\[\#fcf9f8\], html.dark .bg-\[\#F5F5F5\], html.dark .bg-\[\#ffffff\] { background-color: #2D2E30 !important; }
    html.dark footer, html.dark .bg-inverse-surface, html.dark .bg-black { background-color: #202124 !important; border-color: #303134 !important; }
    html.dark .hover\:bg-surface-container:hover, html.dark .hover\:bg-surface-container-low:hover, html.dark .hover\:bg-gray-50:hover, html.dark .bg-surface-variant { background-color: #4a4d51 !important; }
    html.dark input, html.dark textarea, html.dark select { background-color: #2D2E30 !important; color: #e8eaed !important; border-color: #5f6368 !important; }
    html.dark .bg-red-50, html.dark .bg-green-50, html.dark .bg-blue-50 { background-color: rgba(255,255,255,0.05) !important; border-color: #5f6368 !important; color: #e8eaed !important; }
    html.dark .text-red-700, html.dark .text-green-700, html.dark .text-blue-700, html.dark .text-gray-800 { color: #e8eaed !important; }
    html.dark .border-red-200, html.dark .border-green-200, html.dark .border-blue-200 { border-color: #5f6368 !important; }
    
    /* Mega Menu Dark Mode */
    html.dark .mega-panel { background: #2D2E30 !important; border-top: 2px solid #BE1E2D; }
    html.dark .mega-column { border-right-color: #5f6368 !important; }
    html.dark .mega-col-header, html.dark .mega-col-link { color: #e8eaed !important; }
    html.dark .mega-col-link:hover, html.dark .mega-col-link.is-active { color: #BE1E2D !important; }
    html.dark .mega-view-all { color: #BE1E2D !important; }
    
    /* Mobile Menu Dark Mode */
    html.dark .mobile-menu-panel { background: #2D2E30 !important; color: #e8eaed !important; }
    html.dark .mobile-menu-panel .bg-gray-50\/50, html.dark .mobile-menu-panel .bg-gray-50 { background-color: #202124 !important; border-color: #5f6368 !important; }
    
    /* Badges & Status Colors in Dark Mode */
    html.dark .bg-blue-100, html.dark .bg-\[\#e8f0fe\] { background-color: rgba(41, 121, 255, 0.2) !important; color: #b0c6ff !important; }
    html.dark .bg-green-100 { background-color: rgba(34, 197, 94, 0.2) !important; color: #86efac !important; }
    html.dark .bg-yellow-100 { background-color: rgba(255, 214, 0, 0.2) !important; color: #ffe57f !important; }
    html.dark .bg-red-100, html.dark .bg-\[\#ffdad6\] { background-color: rgba(255, 82, 82, 0.2) !important; color: #ff8a80 !important; }
    html.dark .bg-gray-100 { background-color: rgba(158, 158, 158, 0.2) !important; color: #e0e0e0 !important; }
    html.dark .bg-blue-500 { background-color: #1565C0 !important; color: #ffffff !important; }
    html.dark .bg-green-500 { background-color: #2E7D32 !important; color: #ffffff !important; }
    html.dark .bg-yellow-500 { background-color: #F57F17 !important; color: #ffffff !important; }
    html.dark .bg-red-500 { background-color: #C62828 !important; color: #ffffff !important; }
    html.dark .bg-gray-500 { background-color: #616161 !important; color: #ffffff !important; }
    html.dark .text-\[\#2979FF\] { color: #b0c6ff !important; }
    html.dark .text-\[\#93000a\] { color: #ff8a80 !important; }
</style>
