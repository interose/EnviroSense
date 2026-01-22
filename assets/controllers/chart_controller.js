import { Controller } from '@hotwired/stimulus';
import Highcharts from 'highcharts';
import { currentChartConfig, historicalChartConfig, baseDataLabelStyle, lastDaysChartConfig } from '../_chartConfig.js';

export default class extends Controller {
    static targets = ['chartCurrent', 'chartLastDays', 'chartLastMonths', 'chartLastYears', 'loadingOverlay'];

    static values = {
        seriesCurrentUrl: String,
        unit: String,
        color: { type: String, default: '#2563eb' },
        seriesLastDays: { type: Array, default: [] },
        seriesLastYears: { type: Array, default: [] },
        seriesLastMonth: { type: Array, default: [] },
        seriesLastMonthYearBefore: { type: Array, default: [] },
    }

    async connect() {
        if (this.hasSeriesCurrentUrlValue) {
            await this.loadFromApi();
        }

        this.renderLastDaysSeries();
        this.renderLastMonthsSeries();
        this.renderLastYearsSeries();
    }

    disconnect() {
        // Clean up all chart instances
        this.destroyChart('chartCurrent');
        this.destroyChart('chartLastDays');
        this.destroyChart('chartLastMonths');
        this.destroyChart('chartLastYears');
    }

    /**
     * Fetch chart data from API endpoint
     */
    async loadFromApi() {
        this.showLoading();

        try {
            const response = await fetch(this.seriesCurrentUrlValue);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            this.hideLoading();

            const series = this.prepareTimeseriesData(data.series);
            this.renderCurrentChart(series);

        } catch (error) {
            console.error('Chart API error:', error);
            this.hideLoading();
        }
    }

    renderCurrentChart(data) {
        currentChartConfig.series = [{
            type: 'area',
            data: data,
            color: this.colorValue
        }];

        currentChartConfig.tooltip = {
            formatter: function() {
                return Highcharts.time.dateFormat('%H:%M', this.x)+' Uhr | '+this.y+' '+this.unitValue;
            }
        };

        this.chartCurrent = Highcharts.chart(this.chartCurrentTarget, currentChartConfig);
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

    prepareTimeseriesData(seriesValue) {
        return seriesValue.map(item => [
            item.timestamp * 1000, // Convert to milliseconds
            parseFloat(item.value)
        ]);
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

    showLoading() {
        if (this.hasLoadingOverlayTarget) {
            this.loadingOverlayTarget.classList.remove('d-none');
        }
    }

    hideLoading() {
        if (this.hasLoadingOverlayTarget) {
            this.loadingOverlayTarget.classList.add('d-none');
        }
    }
}