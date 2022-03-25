<template>
  <b-card
    v-if="leaders"
    no-body
    class="card-company-table"
  >
    <b-table
      :items="leaders"
      responsive
      :fields="fields"
      class="mb-0"
    >
      <!-- company -->
      <template #cell(profile_image)="data">
        <div class="d-flex align-items-center">
          <b-avatar
            rounded
            size="32"
            variant="light-company"
             :src="data.item.profile_image"
          >
            </b-avatar>
            
          <div>
            <div class="font-weight-bolder">
              {{ data.item.full_name }}
            </div>
            <div class="font-small-2 text-muted">
              {{ data.item.email }}
            </div>
          </div>
        </div>
      </template>

      <!-- views -->
      <template #cell(level)="data">
        <div class="d-flex flex-column">
          <span class="font-weight-bolder mb-25">{{ data.item.level }}</span>
        </div>
      </template>

      <!-- revenue -->
      <template #cell(job_units)="data">
        {{ data.item.job_units }}
      </template>

    </b-table>
  </b-card>
</template>

<script>
import {
  BCard, BTable, BAvatar, BImg,
} from 'bootstrap-vue'

export default {
  components: {
    BCard,
    BTable,
    BAvatar,
    BImg,
  },
  props: {
    tableData: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      fields: [
        { key: 'profile_image', label: 'FULL NAME' },
        { key: 'level', label: 'LEVEL' },
        { key: 'job_units', label: 'JOBS COMPLETED' },
      ],
    }
  },
  computed: {
    leaders() {
      return this.$store.state.app.dashboardData.leaders || []
    },
  },
}
</script>

<style lang="scss" scoped>
@import '~@core/scss/base/bootstrap-extended/include';
@import '~@core/scss/base/components/variables-dark';

.card-company-table ::v-deep td .b-avatar.badge-light-company {
  .dark-layout & {
    background: $theme-dark-body-bg !important;
  }
}
</style>
