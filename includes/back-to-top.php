<button id="back-to-top" class="fixed bottom-8 right-8 bg-axeron-red text-white p-3 rounded-full shadow-[0_4px_14px_0_rgba(190,30,45,0.39)] hover:bg-red-700 hover:shadow-[0_6px_20px_rgba(190,30,45,0.23)] hover:-translate-y-1 transition-all duration-300 z-50 opacity-0 invisible flex items-center justify-center pointer-events-none" aria-label="Back to top">
    <span class="material-symbols-outlined text-[24px]">arrow_upward</span>
</button>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const backToTopBtn = document.getElementById('back-to-top');
    if (!backToTopBtn) return;

    window.addEventListener('scroll', function() {
        if (window.scrollY > 400) {
            backToTopBtn.classList.remove('opacity-0', 'invisible', 'pointer-events-none');
            backToTopBtn.classList.add('opacity-100', 'visible', 'pointer-events-auto');
        } else {
            backToTopBtn.classList.add('opacity-0', 'invisible', 'pointer-events-none');
            backToTopBtn.classList.remove('opacity-100', 'visible', 'pointer-events-auto');
        }
    });

    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
</script>
