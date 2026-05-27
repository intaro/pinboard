import Chart from 'chart.js/auto';
import 'chartjs-adapter-date-fns';

const defaultColors = [
    '#39A3B9',
    '#700193',
    '#C38630',
    '#729462',
    '#e24a14',
    '#b93939',
    '#2f6fed',
    '#6f42c1',
];

const chartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        mode: 'index',
        intersect: false,
    },
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                usePointStyle: true,
                pointStyle: 'line',
                boxWidth: 12,
            },
        },
        tooltip: {
            mode: 'index',
            intersect: false,
        },
    },
    scales: {
        x: {
            ticks: {
                maxRotation: 0,
                autoSkip: true,
            },
        },
        y: {
            beginAtZero: true,
        },
    },
};

function getCanvas(target) {
    return typeof target === 'string' ? document.getElementById(target) : target;
}

function hexToRgba(hex, alpha = 0.18) {
    const normalized = hex.replace('#', '');
    const value = normalized.length === 3
        ? normalized.split('').map((part) => part + part).join('')
        : normalized;

    const parsed = Number.parseInt(value, 16);
    const r = (parsed >> 16) & 255;
    const g = (parsed >> 8) & 255;
    const b = parsed & 255;

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function buildDatasets(series, { stacked = false, useTimeScale = false, chartType = 'line' } = {}) {
    return series.map((item, index) => {
        const color = item.color ?? defaultColors[index % defaultColors.length];
        const backgroundColor = item.backgroundColor ?? (
            chartType === 'line'
                ? hexToRgba(color, item.fill === false ? 0 : 0.16)
                : color
        );

        return {
            label: item.label,
            data: item.data,
            borderColor: color,
            backgroundColor,
            borderWidth: item.borderWidth ?? (chartType === 'line' ? 2 : 1),
            borderSkipped: chartType === 'line' ? undefined : false,
            pointRadius: item.pointRadius ?? 0,
            pointHoverRadius: item.pointHoverRadius ?? 4,
            tension: item.tension ?? 0.35,
            fill: item.fill ?? false,
            hidden: item.hidden ?? false,
            stepped: item.stepped ?? false,
            stack: stacked ? (item.stack ?? 'stack') : undefined,
            parsing: useTimeScale ? false : true,
        };
    });
}

function createChart(target, config) {
    const canvas = getCanvas(target);
    if (!canvas) {
        return null;
    }

    const context = canvas.getContext('2d');
    if (!context) {
        return null;
    }

    return new Chart(context, config);
}

function renderTimeSeriesChart(target, { series, yTitle = '', stacked = false, legend = true, interaction = {}, tooltip = {} }) {
    return createChart(target, {
        type: 'line',
        data: {
            datasets: buildDatasets(series, { stacked, useTimeScale: true, chartType: 'line' }),
        },
        options: {
            ...chartDefaults,
            interaction: {
                ...chartDefaults.interaction,
                ...interaction,
            },
            scales: {
                x: {
                    type: 'time',
                    time: {
                        displayFormats: {
                            hour: 'HH:mm',
                            minute: 'HH:mm',
                            second: 'HH:mm:ss',
                            day: 'dd MMM',
                            week: 'dd MMM',
                            month: 'MMM yyyy',
                            quarter: 'QQQ yyyy',
                            year: 'yyyy',
                        },
                        tooltipFormat: 'HH:mm, dd MMM yyyy',
                    },
                    ticks: {
                        maxRotation: 0,
                        autoSkip: true,
                    },
                },
                y: {
                    beginAtZero: true,
                    stacked,
                    title: yTitle ? {
                        display: true,
                        text: yTitle,
                    } : undefined,
                },
            },
            plugins: {
                ...chartDefaults.plugins,
                legend: {
                    ...chartDefaults.plugins.legend,
                    display: legend,
                },
                tooltip: {
                    ...chartDefaults.plugins.tooltip,
                    ...tooltip,
                },
            },
        },
    });
}

function renderCategoryChart(target, { labels, series, stacked = false, legend = true, yTitle = '', type = 'line', indexAxis = 'x', interaction = {}, tooltip = {} }) {
    const isHorizontal = indexAxis === 'y';
    return createChart(target, {
        type,
        data: {
            labels,
            datasets: buildDatasets(series, { stacked, useTimeScale: false, chartType: type }),
        },
        options: {
            ...chartDefaults,
            interaction: {
                ...chartDefaults.interaction,
                ...interaction,
            },
            indexAxis,
            scales: {
                x: isHorizontal ? {
                    beginAtZero: true,
                    stacked,
                    ticks: {
                        maxRotation: 0,
                        autoSkip: true,
                    },
                    title: yTitle ? {
                        display: true,
                        text: yTitle,
                    } : undefined,
                } : {
                    type: 'category',
                    ticks: {
                        maxRotation: 0,
                        autoSkip: true,
                    },
                },
                y: isHorizontal ? {
                    type: 'category',
                    stacked,
                } : {
                    beginAtZero: true,
                    stacked,
                    title: yTitle ? {
                        display: true,
                        text: yTitle,
                    } : undefined,
                },
            },
            plugins: {
                ...chartDefaults.plugins,
                legend: {
                    ...chartDefaults.plugins.legend,
                    display: legend,
                },
                tooltip: {
                    ...chartDefaults.plugins.tooltip,
                    ...tooltip,
                },
            },
        },
    });
}

window.PinboardCharts = {
    renderTimeSeriesChart,
    renderCategoryChart,
    hexToRgba,
};
