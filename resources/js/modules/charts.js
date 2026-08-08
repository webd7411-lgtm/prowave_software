/**
 * Prowave ERP — Charts (Chart.js)
 */
import Chart from 'chart.js/auto';

let charts = [];

function isDarkTheme() {
    return document.documentElement.getAttribute('data-theme') === 'dark';
}

export function createChart(canvasId, config) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        return null;
    }

    destroyChart(canvasId);

    // Merge responsive-friendly defaults unless explicitly overridden
    const merged = {
        responsive: true,
        maintainAspectRatio: false,
        ...config,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            ...(config.options || {}),
            plugins: {
                ...(config.options?.plugins || {}),
                legend: {
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 16,
                        color: isDarkTheme() ? '#94a3b8' : '#64748b',
                        font: { size: 12, family: 'Inter, system-ui, sans-serif' },
                    },
                    ...(config.options?.plugins?.legend || {}),
                },
                tooltip: {
                    backgroundColor: isDarkTheme() ? '#1a2740' : '#0f172a',
                    titleColor: '#e2e8f0',
                    bodyColor: '#cbd5e1',
                    padding: 12,
                    cornerRadius: 10,
                    displayColors: true,
                    ...(config.options?.plugins?.tooltip || {}),
                },
            },
            scales: {
                ...(config.options?.scales || {}),
                x: {
                    grid: { display: false },
                    ticks: {
                        color: isDarkTheme() ? '#64748b' : '#94a3b8',
                        font: { size: 11, family: 'Inter, system-ui, sans-serif' },
                    },
                    border: { display: false },
                    ...(config.options?.scales?.x || {}),
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: isDarkTheme() ? 'rgba(148,163,184,0.08)' : 'rgba(15,23,42,0.05)',
                    },
                    ticks: {
                        color: isDarkTheme() ? '#64748b' : '#94a3b8',
                        font: { size: 11, family: 'Inter, system-ui, sans-serif' },
                        callback: (value) => value.toLocaleString(),
                    },
                    border: { display: false },
                    ...(config.options?.scales?.y || {}),
                },
            },
            animation: {
                duration: 900,
                easing: 'easeInOutQuart',
                ...(config.options?.animation || {}),
            },
        },
        data: config.data,
    };

    const chart = new Chart(canvas, merged);
    charts.push({ id: canvasId, chart });

    return chart;
}

export function destroyChart(canvasId) {
    charts = charts.filter(({ id, chart }) => {
        if (id === canvasId) {
            chart.destroy();
            return false;
        }
        return true;
    });
}

function applyThemeToCharts() {
    charts.forEach(({ chart }) => {
        const dark = isDarkTheme();
        const legendColor = dark ? '#94a3b8' : '#64748b';
        const tickColor = dark ? '#64748b' : '#94a3b8';
        const gridColor = dark ? 'rgba(148,163,184,0.08)' : 'rgba(15,23,42,0.05)';

        if (chart.options.plugins?.legend?.labels) {
            chart.options.plugins.legend.labels.color = legendColor;
        }
        if (chart.options.plugins?.tooltip) {
            chart.options.plugins.tooltip.backgroundColor = dark ? '#1a2740' : '#0f172a';
        }
        if (chart.options.scales?.x?.ticks) {
            chart.options.scales.x.ticks.color = tickColor;
        }
        if (chart.options.scales?.y?.ticks) {
            chart.options.scales.y.ticks.color = tickColor;
        }
        if (chart.options.scales?.y?.grid) {
            chart.options.scales.y.grid.color = gridColor;
        }
        chart.update();
    });
}

export function initCharts() {
    document.querySelectorAll('[data-chart]').forEach((element) => {
        const type = element.dataset.chart;
        const labels = JSON.parse(element.dataset.chartLabels || '[]');
        const datasets = JSON.parse(element.dataset.chartDatasets || '[]');

        createChart(element.id, {
            type,
            data: { labels, datasets },
        });
    });

    // Re-theme charts when dark mode toggles
    const observer = new MutationObserver(() => applyThemeToCharts());
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
    window.addEventListener('pw:theme-changed', applyThemeToCharts);
}
