    <script>
        document.querySelectorAll('.vendor-page .file-drop input[type="file"]').forEach((input) => {
            input.addEventListener('change', () => {
                const name = input.closest('.file-drop')?.querySelector('.file-name');
                if (name) name.textContent = Array.from(input.files || []).map((file) => file.name).join(', ') || 'Belum ada file dipilih';
            });
        });
    </script>
