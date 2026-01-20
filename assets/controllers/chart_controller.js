import { Controller } from '@hotwired/stimulus';
import Highcharts from 'highcharts';

import { currentChartConfig, historicalChartConfig, baseDataLabelStyle, lastDaysChartConfig } from '../_chartConfig.js';

export default class extends Controller {
    static targets = ['chartLastDays', 'chartLastMonths', 'chartLastYears', 'loading', 'error'];

    static values = {
        unit: String,
        color: { type: String, default: '#2563eb' },
        seriesLastDays: { type: Array, default: [] },
        seriesLastYears: { type: Array, default: [] },
        seriesLastMonth: { type: Array, default: [] },
        seriesLastMonthYearBefore: { type: Array, default: [] },
    }

    async connect() {
        this.renderLastDaysSeries();
        this.renderLastMonthsSeries();
        this.renderLastYearsSeries();
    }

    disconnect() {
        // Clean up all chart instances
        this.destroyChart('chartLastDays');
        this.destroyChart('chartLastMonths');
        this.destroyChart('chartLastYears');
    }

    renderLastDaysSeries() {
        lastDaysChartConfig.series = [{
            borderWidth: 0,
            data: this.prepareCategoricalData(this.seriesLastDaysValue),
            color: this.colorValue,
            dataLabels: this.createDataLabel(1)
        }];

        this.chartLastDays = Highcharts.chart(this.chartLastDaysTarget, lastDaysChartConfig);
    }

    renderLastMonthsSeries() {
        const monthlyChartConfig = this.cloneConfig(historicalChartConfig);
        monthlyChartConfig.series = [{
            borderWidth: 0,
            data: this.prepareCategoricalData(this.seriesLastMonthValue),
            color: this.colorValue,
            dataLabels: this.createDataLabel(0)
        }, {
            borderWidth: 0,
            data: this.prepareCategoricalData(this.seriesLastMonthYearBeforeValue),
            color: this.colorValue,
            dashStyle: 'Dash',
            dataLabels: this.createDataLabel(0)
        }];

        this.chartLastMonths = Highcharts.chart(this.chartLastMonthsTarget, monthlyChartConfig);
    }

    renderLastYearsSeries() {
        const yearlyChartConfig = this.cloneConfig(historicalChartConfig);
        yearlyChartConfig.series = [{
            borderWidth: 0,
            data: this.prepareCategoricalData(this.seriesLastYearsValue),
            color: this.colorValue,
            dataLabels: this.createDataLabel(0)
        }];

        this.chartLastYears = Highcharts.chart(this.chartLastYearsTarget, yearlyChartConfig);
    }

    // Helper methods

    /**
     * Transform data into categorical format for Highcharts
     */
    prepareCategoricalData(seriesValue) {
        return seriesValue.map(item => ({
            name: item.weekday || item.monthname || item.year,
            y: parseFloat(item.consumption || item.yield)
        }));
    }

    /**
     * Create data label configuration with specified decimal places
     */
    createDataLabel(decimals = 0) {
        return [{
            enabled: true,
            formatter: function() {
                return Highcharts.numberFormat(this.y, decimals, ',', '.');
            },
            style: baseDataLabelStyle
        }];
    }

    /**
     * Deep clone chart config to avoid mutations
     */
    cloneConfig(config) {
        return structuredClone(config);
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