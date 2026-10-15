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

const HOUR = 60 * 60 * 1000;
const DAY = 24 * HOUR;

// Spans up to this length keep hourly ticks (1 day, 3 days); longer ones switch to days.
const HOURLY_TICKS_MAX_SPAN = 4 * DAY;
// Spans up to this length use even 2-hour ticks so the labels stay evenly spaced.
const TWO_HOUR_TICKS_MAX_SPAN = 1.5 * DAY;

function toTimestamp(value) {
    if (typeof value === 'number') {
        return value;
    }

    // An ISO date-time without an offset is parsed as local time, so the axis shows the server wall clock.
    return new Date(value).getTime();
}

function buildTimeAxis(datasets) {
    let min = Infinity;
    let max = -Infinity;
    datasets.forEach((dataset) => {
        dataset.data.forEach(({ x }) => {
            min = Math.min(min, x);
            max = Math.max(max, x);
        });
    });

    const span = max > min ? max - min : 0;
    const hourly = span <= HOURLY_TICKS_MAX_SPAN;

    return {
        type: 'time',
        time: {
            unit: hourly ? 'hour' : 'day',
            displayFormats: {
                hour: 'HH:mm',
                day: 'dd.MM',
            },
            tooltipFormat: 'HH:mm, dd MMM yyyy',
        },
        ticks: {
            maxRotation: 0,
            autoSkip: true,
            autoSkipPadding: 12,
            stepSize: span <= TWO_HOUR_TICKS_MAX_SPAN ? 2 : 1,
            // On hourly axes midnight ticks become "major" and show the date instead of 00:00.
            major: {
                enabled: hourly,
            },
            font: (context) => (context.tick?.major ? { weight: 'bold' } : undefined),
        },
    };
}

function renderTimeSeriesChart(target, { series, yTitle = '', stacked = false, legend = true, interaction = {}, tooltip = {} }) {
    const datasets = buildDatasets(
        series.map((item) => ({
            ...item,
            data: item.data.map(({ x, y }) => ({ x: toTimestamp(x), y })),
        })),
        { stacked, useTimeScale: true, chartType: 'line' },
    );

    return createChart(target, {
        type: 'line',
        data: {
            datasets,
        },
        options: {
            ...chartDefaults,
            interaction: {
                ...chartDefaults.interaction,
                ...interaction,
            },
            scales: {
                x: buildTimeAxis(datasets),
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
