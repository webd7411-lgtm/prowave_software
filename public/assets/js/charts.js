/**
 * Prowave ERP — Charts (Chart.js)
 */
(function (window, document) {
    'use strict';

    window.Prowave = window.Prowave || {};

    let charts = [];
    let themeObserverAttached = false;

    function isDarkTheme() {
        return document.documentElement.getAttribute('data-theme') === 'dark';
    }

    function destroyChart(canvasId) {
        charts = charts.filter(({ id, chart }) => {
            if (id === canvasId) {
                chart.destroy();
                return false;
            }
            return true;
        });
    }

    function createChart(canvasId, config) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || typeof window.Chart === 'undefined') {
            return null;
        }

        destroyChart(canvasId);

        const opts = config.options || {};
        const merged = {
            responsive: true,
            maintainAspectRatio: false,
            ...config,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                ...opts,
                plugins: {
                    ...(opts.plugins || {}),
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
                        ...(opts.plugins?.legend || {}),
                    },
                    tooltip: {
                        backgroundColor: isDarkTheme() ? '#1a2740' : '#0f172a',
                        titleColor: '#e2e8f0',
                        bodyColor: '#cbd5e1',
                        padding: 12,
                        cornerRadius: 10,
                        displayColors: true,
                        ...(opts.plugins?.tooltip || {}),
                    },
                },
                scales: {
                    ...(opts.scales || {}),
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: isDarkTheme() ? '#64748b' : '#94a3b8',
                            font: { size: 11, family: 'Inter, system-ui, sans-serif' },
                        },
                        border: { display: false },
                        ...(opts.scales?.x || {}),
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
                        ...(opts.scales?.y || {}),
                    },
                },
                animation: {
                    duration: 900,
                    easing: 'easeInOutQuart',
                    ...(opts.animation || {}),
                },
            },
            data: config.data,
        };

        const chart = new window.Chart(canvas, merged);
        charts.push({ id: canvasId, chart });

        return chart;
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

    function initCharts() {
        document.querySelectorAll('[data-chart]').forEach((element) => {
            const type = element.dataset.chart;
            const labels = JSON.parse(element.dataset.chartLabels || '[]');
            const datasets = JSON.parse(element.dataset.chartDatasets || '[]');

            createChart(element.id, {
                type,
                data: { labels, datasets },
            });
        });

        if (!themeObserverAttached) {
            themeObserverAttached = true;
            const observer = new MutationObserver(() => applyThemeToCharts());
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
            window.addEventListener('pw:theme-changed', applyThemeToCharts);
        }
    }

    window.createChart = createChart;
    window.destroyChart = destroyChart;
    window.Prowave.createChart = createChart;
    window.Prowave.destroyChart = destroyChart;
    window.Prowave.initCharts = initCharts;
})(window, document);
