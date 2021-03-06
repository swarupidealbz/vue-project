<template>
  <b-card
    v-if="data"
    no-body
  >
    <b-card-header>
      <h4 class="mb-0">
        Goal Overview
      </h4>
      <b-card-text class="font-medium-5 mb-0">
        <!-- <feather-icon
          icon="HelpCircleIcon"
          size="21"
          class="text-muted cursor-pointer"
        /> -->
      </b-card-text>
    </b-card-header>

    <!-- apex chart -->
    <vue-apex-charts
      type="radialBar"
      height="245"
      class="my-2"
      :options="goalOverviewRadialBar"
      :series="series"
    />
    <b-row class="text-center mx-0">
      <b-col
        cols="6"
        class="border-top border-right d-flex align-items-between flex-column py-1"
      >
        <b-card-text class="text-muted mb-0">
          Monthly Goal
        </b-card-text>
        <h3 class="font-weight-bolder mb-0">
          {{ monthlyGoal }}
        </h3>
      </b-col>

      <b-col
        cols="6"
        class="border-top d-flex align-items-between flex-column py-1"
      >
        <b-card-text class="text-muted mb-0">
          {{ isClient ? 'Created' : 'Completed' }}
        </b-card-text>
        <h3 class="font-weight-bolder mb-0">
          {{ completed }}
        </h3>
      </b-col>
    </b-row>
  </b-card>
</template>

<script>
import {
  BCard, BCardHeader, BRow, BCol, BCardText,
} from 'bootstrap-vue'
import VueApexCharts from 'vue-apexcharts'
import { $themeColors } from '@themeConfig'

const $strokeColor = '#ebe9f1'
const $textHeadingColor = '#5e5873'
const $goalStrokeColor2 = '#51e5a8'
export default {
  components: {
    VueApexCharts,
    BCard,
    BCardHeader,
    BRow,
    BCardText,
    BCol,
  },
  props: {
    data: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      goalOverviewRadialBar: {
        chart: {
          height: 245,
          type: 'radialBar',
          sparkline: {
            enabled: true,
          },
          dropShadow: {
            enabled: true,
            blur: 3,
            left: 1,
            top: 1,
            opacity: 0.1,
          },
        },
        colors: [$goalStrokeColor2],
        plotOptions: {
          radialBar: {
            offsetY: -10,
            startAngle: -150,
            endAngle: 150,
            hollow: {
              size: '77%',
            },
            track: {
              background: $strokeColor,
              strokeWidth: '50%',
            },
            dataLabels: {
              name: {
                show: false,
              },
              value: {
                color: $textHeadingColor,
                fontSize: '2.86rem',
                fontWeight: '600',
              },
            },
          },
        },
        fill: {
          type: 'gradient',
          gradient: {
            shade: 'dark',
            type: 'horizontal',
            shadeIntensity: 0.5,
            gradientToColors: [$themeColors.success],
            inverseColors: true,
            opacityFrom: 1,
            opacityTo: 1,
            stops: [0, 100],
          },
        },
        stroke: {
          lineCap: 'round',
        },
        grid: {
          padding: {
            bottom: 30,
          },
        },
      },
    }
  },
  computed: {
    stat() {
      return this.$store.state.app.dashboardData.statistics || []
    },
    percentage() {
      let number = ((this.completed / this.monthlyGoal) *100);
      return number ? number.toFixed(0) : number;
    },
    monthlyGoal() {
      return this.stat.monthly_goal || 0;
    },
    completed() {
      return this.stat.self_topics_count || 0
    },
    series() {
      return [
        this.percentage || 0
      ]
    },
    isClient() {
      return this.data.role == 'client'
    }
  },
}
</script>
