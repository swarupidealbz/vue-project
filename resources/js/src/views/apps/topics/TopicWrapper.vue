<template>
    <div class="d-flex justify-content-start breadcrumb-wrapper">
      <b-breadcrumb :items="bitems" />
    </div>
    <topic/>
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
}
</script>