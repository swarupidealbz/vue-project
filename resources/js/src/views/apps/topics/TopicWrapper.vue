<template>
    <div>
        <div class="d-flex justify-content-start breadcrumb-wrapper">
        <b-breadcrumb :items="bitems" />
        </div>
        <topic/>
    </div>
</template>
<script>
import store from '@/store'
import Topic from './Topic.vue'

import {
  BBreadcrumb
} from 'bootstrap-vue'

export default {
  components: {
    BBreadcrumb,
    Topic
  },
  computed: {
    bitems() {
      return [
        {
          text: 'Dashboard',
        },
        {
          text: 'Library',
        },
        {
          text: 'Data',
          active: true,
        },
      ]
    }
  },
}
</script>