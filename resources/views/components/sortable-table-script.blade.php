<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tables = document.querySelectorAll('.sortable-table');
        
        tables.forEach(table => {
            const headers = table.querySelectorAll('thead th.sortable');
            const tbody = table.querySelector('tbody');
            const tfoot = table.querySelector('tfoot');
            let currentSort = { column: null, direction: 'asc' };

            headers.forEach((header, index) => {
                header.addEventListener('click', function() {
                    const sortType = this.getAttribute('data-sort');
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    
                    // Determinar direção da ordenação
                    if (currentSort.column === index) {
                        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                    } else {
                        currentSort.column = index;
                        currentSort.direction = 'asc';
                    }

                    // Remover indicadores de todas as colunas
                    headers.forEach(h => {
                        const indicator = h.querySelector('.sort-indicator');
                        if (indicator) {
                            indicator.textContent = '';
                            indicator.classList.remove('text-indigo-600', 'dark:text-indigo-400');
                        }
                    });

                    // Adicionar indicador na coluna atual
                    const indicator = header.querySelector('.sort-indicator');
                    if (indicator) {
                        indicator.textContent = currentSort.direction === 'asc' ? '▲' : '▼';
                        indicator.classList.add('text-indigo-600', 'dark:text-indigo-400');
                    }

                    // Ordenar linhas
                    rows.sort((a, b) => {
                        const aCell = a.cells[index];
                        const bCell = b.cells[index];
                        
                        if (!aCell || !bCell) return 0;
                        
                        let aValue = aCell.textContent.trim();
                        let bValue = bCell.textContent.trim();

                        // Converter valores baseado no tipo
                        if (sortType === 'number' || sortType === 'currency') {
                            // Remover formatação (pontos, vírgulas, espaços, texto)
                            aValue = parseFloat(aValue.replace(/[^\d,.-]/g, '').replace(',', '.')) || 0;
                            bValue = parseFloat(bValue.replace(/[^\d,.-]/g, '').replace(',', '.')) || 0;
                        } else if (sortType === 'date') {
                            // Converter data DD/MM/YYYY para timestamp
                            const aParts = aValue.split('/');
                            const bParts = bValue.split('/');
                            if (aParts.length === 3 && bParts.length === 3) {
                                aValue = new Date(aParts[2], aParts[1] - 1, aParts[0]).getTime();
                                bValue = new Date(bParts[2], bParts[1] - 1, bParts[0]).getTime();
                            }
                        } else if (sortType === 'datetime') {
                            // Converter data/hora DD/MM/YYYY HH:MM para timestamp
                            const aParts = aValue.split(' ');
                            const bParts = bValue.split(' ');
                            if (aParts.length >= 1 && bParts.length >= 1) {
                                const aDateParts = aParts[0].split('/');
                                const bDateParts = bParts[0].split('/');
                                if (aDateParts.length === 3 && bDateParts.length === 3) {
                                    const aTime = aParts[1] ? aParts[1].split(':') : [0, 0];
                                    const bTime = bParts[1] ? bParts[1].split(':') : [0, 0];
                                    aValue = new Date(aDateParts[2], aDateParts[1] - 1, aDateParts[0], aTime[0] || 0, aTime[1] || 0).getTime();
                                    bValue = new Date(bDateParts[2], bDateParts[1] - 1, bDateParts[0], bTime[0] || 0, bTime[1] || 0).getTime();
                                }
                            }
                        } else {
                            // Texto - normalizar para comparação
                            aValue = aValue.toLowerCase();
                            bValue = bValue.toLowerCase();
                        }

                        // Comparar valores
                        let comparison = 0;
                        if (aValue < bValue) {
                            comparison = -1;
                        } else if (aValue > bValue) {
                            comparison = 1;
                        }

                        return currentSort.direction === 'asc' ? comparison : -comparison;
                    });

                    // Reordenar linhas no DOM
                    rows.forEach(row => tbody.appendChild(row));
                });
            });
        });
    });
</script>

