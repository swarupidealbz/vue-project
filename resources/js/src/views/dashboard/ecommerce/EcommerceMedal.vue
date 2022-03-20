<template>
  <b-card
    v-if="data"
    class="card-congratulation-medal"
  >
    <h5>{{ data.name }}{{ $t('congratulation_card.welcome_dashboard') }}</h5>
    <b-card-text class="font-small-3">
      {{ $t('congratulation_card.level') }} {{ data.level }}
    </b-card-text>
    <h3 class="mb-75 mt-2 pt-50">
      <b-link>${{ kFormatter(cost_amount) }}</b-link>
    </h3>
    <small class="mt-1">{{ data.role == 'client' ? $t('congratulation_card.client_text') : $t('congratulation_card.writer_text') }}</small>
    <b-img
      :src="require('@/assets/images/illustration/badge.svg')"
      class="congratulation-medal"
      alt="Medal Pic"
    />
  </b-card>
</template>

<script>
import {
  BCard, BCardText, BLink, BButton, BImg,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { kFormatter } from '@core/utils/filter'

export default {
  components: {
    BCard,
    BCardText,
    BLink,
    BImg,
    BButton,
  },
  directives: {
    Ripple,
  },
  props: {
    data: {
      type: Object,
      default: () => {},
    },
  },
  computed: {
    stat() {
      return  this.$store.state.app.dashboardData.statistics || []
    },
    cost_amount() {
      console.log(this.stat.self_topics_count, this.data.cost)
      return (this.stat.self_topics_count || 0) * this.data.cost;
    }
  },
  methods: {
    kFormatter,
  },
}
</script>
