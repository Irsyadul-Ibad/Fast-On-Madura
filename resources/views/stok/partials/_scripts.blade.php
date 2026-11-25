    <script>
        (() => {
            const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(value);
            const updateStockSummary = () => {
                let totalTr = 0;
                let totalTm11 = 0;
                let totalTm13 = 0;

                document.querySelectorAll('.stok-tr-input').forEach((input) => {
                    totalTr += Number(input.value) || 0;
                });
                document.querySelectorAll('.stok-tm-11-input').forEach((input) => {
                    totalTm11 += Number(input.value) || 0;
                });
                document.querySelectorAll('.stok-tm-13-input').forEach((input) => {
                    totalTm13 += Number(input.value) || 0;
                });

                document.getElementById('totalStokTr').textContent = formatNumber(totalTr);
                document.getElementById('totalStokTm11').textContent = formatNumber(totalTm11);
                document.getElementById('totalStokTm13').textContent = formatNumber(totalTm13);
            };

            document.querySelectorAll('.stok-input').forEach((input) => {
                input.addEventListener('input', (event) => {
                    const row = event.target.closest('tr');
                    const tr = Number(row.querySelector('.stok-tr-input')?.value) || 0;
                    const tm11 = Number(row.querySelector('.stok-tm-11-input')?.value) || 0;
                    const tm13 = Number(row.querySelector('.stok-tm-13-input')?.value) || 0;
                    row.querySelector('.stok-row-total strong').textContent = formatNumber(tr + tm11 + tm13);
                    updateStockSummary();
                });
            });
        })();
    </script>
