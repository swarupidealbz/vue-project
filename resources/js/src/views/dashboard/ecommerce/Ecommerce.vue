<template>
  <section id="dashboard-ecommerce">
    <b-row class="match-height">
      <b-col
        xl="4"
        md="6"
      >
        <ecommerce-medal :data="data.congratulations" />
      </b-col>
      <b-col
        xl="8"
        md="6"
      >
        <ecommerce-statistics :data="stat" />
      </b-col>
    </b-row>

    <b-row class="match-height">
      <b-col lg="4">
        <b-row class="match-height">
          <!-- Bar Chart - Orders -->
          <!-- Goal Overview Card -->
          <b-col
            lg="12"
            md="6"
          >
            <ecommerce-goal-overview :data="data.goalOverview" />
          </b-col>
          <!--/ Goal Overview Card -->
        </b-row>
      </b-col>

      <!-- Company Table Card -->
      <b-col lg="8">
        <ecommerce-company-table :table-data="data.companyTable" />
      </b-col>
      <!--/ Company Table Card -->
    </b-row>

    
  </section>
</template>

<script>
import { BRow, BCol } from 'bootstrap-vue'

import { getUserData } from '@/auth/utils'
import EcommerceMedal from './EcommerceMedal.vue'
import EcommerceStatistics from './EcommerceStatistics.vue'
import EcommerceRevenueReport from './EcommerceRevenueReport.vue'
import EcommerceOrderChart from './EcommerceOrderChart.vue'
import EcommerceProfitChart from './EcommerceProfitChart.vue'
import EcommerceEarningsChart from './EcommerceEarningsChart.vue'
import EcommerceCompanyTable from './EcommerceCompanyTable.vue'
import EcommerceMeetup from './EcommerceMeetup.vue'
import EcommerceBrowserStates from './EcommerceBrowserStates.vue'
import EcommerceGoalOverview from './EcommerceGoalOverview.vue'
import EcommerceTransactions from './EcommerceTransactions.vue'

export default {
  components: {
    BRow,
    BCol,

    EcommerceMedal,
    EcommerceStatistics,
    EcommerceRevenueReport,
    EcommerceOrderChart,
    EcommerceProfitChart,
    EcommerceEarningsChart,
    EcommerceCompanyTable,
    EcommerceMeetup,
    EcommerceBrowserStates,
    EcommerceGoalOverview,
    EcommerceTransactions,
  },
  data() {
    return {
      data: {
      },
    }
  },
  computed: {
    stat() {
      return Object.values(this.$store.state.app.dashboardData.statistics)
    },
  },
  created() {
    // data
    this.$http.get('/ecommerce/data').then(response => {
      this.data = response.data

      this.data.statistics = this.stat
      // ? Your API will return name of logged in user or you might just directly get name of logged in user
      // ? This is just for demo purpose
      const userData = getUserData()
      this.data.congratulations.name = userData.fullName.split(' ')[0] || userData.username
      this.data.congratulations.role = userData.role
      this.data.congratulations.level = this.getLevel(userData.job_units)
      this.data.congratulations.cost = userData.unit_cost
      this.data.goalOverview.monthly_goal = userData.monthly_goal
      this.data.goalOverview.role = userData.role
    })
  },
  methods: {
    getLevel(units) {
      if(units <= 100) {
        return 1;
      }
      else if(units > 100 && units <= 300) {
        return 2;
      }
      else if(units > 300 && units <= 500) {
        return 3;
      }
    }
  }
}
</script>

<style lang="scss">
@import '~@core/scss/vue/pages/dashboard-ecommerce.scss';
@import '~@core/scss/vue/libs/chart-apex.scss';
</style>
