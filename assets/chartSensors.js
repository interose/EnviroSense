import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';
import Highcharts from 'highcharts';
import { baseChartConfig, chartBackgroundColor, chartLineColor  } from './_chartConfig.js';

function renderChart(humiditySeries, dewPointInside, dewPointOutside) {
    const chartConfig = {
        xAxis: {
            type: 'datetime',
            gridLineColor: chartLineColor,
            gridLineWidth: 1,
            tickColor: chartLineColor,
            lineColor: chartLineColor
        },
        chart: {
            type: 'spline',
            zoomType: 'x',
            backgroundColor: chartBackgroundColor
        },
        yAxis: {
            title: {
                text: ''
            },
            gridLineColor: chartLineColor,
        },
        tooltip: {
            formatter: function() {
                return Highcharts.time.dateFormat('%H:%M', this.x)+' Uhr - '+this.y+' %';
            }
        },
        plotOptions: {
            spline: {
                marker: {
                    enabled: false
                }
            }
        }
    };

    Highcharts.chart('chartLastHours', {...baseChartConfig, ...chartConfig, ...{series: humiditySeries}});

    Highcharts.chart('chartDewPointPastHours', {...baseChartConfig, ...chartConfig, ...{series: [dewPointInside, dewPointOutside]}});
}

(function(){
    renderChart(series, dewPointInside, dewPointOutside);
})();