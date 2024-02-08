import Highcharts from 'highcharts';

Highcharts.setOptions({
    colors: ['#0d233a', '#2f7ed8', '#8bbc21', '#910000', '#1aadce', '#492970', '#f28f43', '#77a1e5', '#c42525', '#a6c96a'],
    plotOptions: {
        line: {
            marker: {
                symbol: 'circle'
            }
        }
    },
    time: {
        useUTC: false
    },
});

const chartBackgroundColor = 'rgb(27,27,28)';
const chartLineColor = 'rgb(48,48,48)';
const dataLabelFontColor = '#666666';

export const baseDataLabelStyle = {
    textOutline: 0,
    color: dataLabelFontColor,
    fontWeight: 'normal'
};

export const currentChartConfig = {
    chart: {
        zoomType: 'x',
        backgroundColor: chartBackgroundColor
    },
    title: { text: '' },
    tooltip: { enabled: false },
    credits: { enabled: false },
    legend: { enabled: false },
    xAxis: {
        type: 'datetime',
        gridLineColor: chartLineColor,
        tickColor: chartLineColor,
        lineColor: chartLineColor,
        labels: {
            style: {
                color: dataLabelFontColor,
                fontSize: '0.7em'
            }
        }
    },
    series: [],
    plotOptions: {
        series: {
            marker: {
                enabled: false
            }
        }
    },
    yAxis: {
        title: { text: '' },
        labels: {
            enabled: true,
            style: {
                color: dataLabelFontColor,
                fontSize: '0.7em'
            }
        },
        gridLineColor: chartLineColor,
    }
};

export const historicalChartConfig = {
    chart: {
        type: 'line',
        backgroundColor: chartBackgroundColor
    },
    title: { text: '' },
    tooltip: { enabled: false },
    credits: { enabled: false },
    legend: { enabled: false },
    xAxis: {
        type: 'category',
        gridLineColor: chartLineColor,
        tickColor: chartLineColor,
        lineColor: chartLineColor,
        labels: {
            style: {
                color: dataLabelFontColor,
                fontSize: '0.7em'
            }
        }
    },
    series: [],
    yAxis: {
        title: {
            text: ''
        },
        labels: {
            enabled: false
        },
        gridLineColor: chartLineColor,
    }
};

export const lastDaysChartConfig = {
    chart: {
        type: 'column',
        backgroundColor: chartBackgroundColor
    },
    title: { text: '' },
    tooltip: { enabled: false },
    credits: { enabled: false },
    legend: { enabled: false },
    xAxis: {
        type: 'category',
        gridLineColor: chartLineColor,
        tickColor: chartLineColor,
        lineColor: chartLineColor,
        labels: {
            style: {
                color: dataLabelFontColor,
                fontSize: '0.7em'
            }
        }
    },
    series: [],
    yAxis: {
        title: {
            text: ''
        },
        labels: {
            enabled: false
        },
        gridLineColor: chartLineColor,
    }
};