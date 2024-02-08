import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';

import Highcharts from 'highcharts';
import { currentChartConfig, historicalChartConfig, baseDataLabelStyle, lastDaysChartConfig } from './_chartConfig.js';

const dataLabel = [{
    enabled: true,
    formatter: function() {
        return Highcharts.numberFormat(this.y, 0, '', '.');
    },
    style: baseDataLabelStyle
}];

function renderChartCurrent(data, color, unit) {
    currentChartConfig.series = [{
        type: 'area',
        data: data,
        color: color
    }];

    currentChartConfig.tooltip = {
        formatter: function() {
            return Highcharts.time.dateFormat('%H:%M', this.x)+' Uhr | '+this.y+' '+unit;
        }
    };

    Highcharts.chart('chartCurrent', currentChartConfig);
}

function renderChartLastDays(data, color) {
    lastDaysChartConfig.series = [{
        borderWidth: 0,
        data: data,
        color: color,
        dataLabels: [{
            enabled: true,
            formatter: function() {
                return Highcharts.numberFormat(this.y, 1, ',', '.');
            },
            style: baseDataLabelStyle
        }]
    }];

    Highcharts.chart('chartLastDays', lastDaysChartConfig);
}

function renderChartYears(data, color) {
    const yearlyChartConfig = structuredClone(historicalChartConfig);
    yearlyChartConfig.series = [{
        borderWidth: 0,
        data: data,
        color: color,
        dataLabels: dataLabel
    }];

    Highcharts.chart('chartYearly', yearlyChartConfig);
}

function renderChartMonths(dataCurrent, dataPrevious, color) {
    const monthlyChartConfig = structuredClone(historicalChartConfig);
    monthlyChartConfig.series = [{
        borderWidth: 0,
        data: dataCurrent,
        color: color,
        dataLabels: dataLabel
    }, {
        borderWidth: 0,
        data: dataPrevious,
        color: color,
        dashStyle: 'Dash',
        dataLabels: dataLabel
    }];
    
    Highcharts.chart('chartMonthly', monthlyChartConfig);
}

(function(){
    renderChartCurrent(seriesCurrent, seriesColor, seriesCurrentUnit);
    renderChartLastDays(seriesLastDays, seriesColor);
    renderChartYears(seriesYearly, seriesColor);
    renderChartMonths(seriesLastMonths, seriesLastMonthsYearBefore, seriesColor);
})();