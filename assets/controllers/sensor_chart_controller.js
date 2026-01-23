import { Controller } from '@hotwired/stimulus';
import Highcharts from 'highcharts';
import {baseChartConfig, chartBackgroundColor, chartLineColor} from "../_chartConfig.js";

export default class extends Controller {
    static targets = ['chartDewPoint', 'chartLastHours'];

    connect() {
        // Load data from script tag
        const data = this.loadChartData();

        if (!data) {
            console.error('Chart data not found');
            return;
        }

        this.renderDewPointChart(data);
    }

    disconnect() {
        this.destroyChart('chartDewPoint');
        this.destroyChart('chartLastHour');
    }

    loadChartData() {
        const scriptTag = document.getElementById('chart-data');
        if (!scriptTag) return null;

        try {
            return JSON.parse(scriptTag.textContent);
        } catch (error) {
            console.error('Failed to parse chart data:', error);
            return null;
        }
    }

    renderDewPointChart(data) {
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

        this.chartDewPoint = Highcharts.chart(this.chartDewPointTarget, {...baseChartConfig, ...chartConfig, ...{series: [data.dewPointInside, data.dewPointOutside]}});
        this.chartLastHour = Highcharts.chart(this.chartLastHoursTarget, {...baseChartConfig, ...chartConfig, ...{series: data.humiditySeries}});
    }

    /**
     * Safely destroy a chart instance
     */
    destroyChart(chartProperty) {
        if (this[chartProperty]) {
            this[chartProperty].destroy();
            this[chartProperty] = null;
        }
    }
}