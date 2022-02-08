<template>
  <div class="email-app-details">

    <!-- Email Header -->
    <div class="email-detail-header">

      <!-- Header: Left -->
      <div class="email-header-left d-flex align-items-center">
        <span class="go-back mr-1">
          <feather-icon
            icon="XIcon"
            size="23"
            class="align-bottom"
            @click="$emit('close-topic-view')"
          />
        </span>
      </div>

      <!-- Header: Right -->
      <div class="email-header-right ml-2 pl-1">
        <span
          class="mx-50 bullet bullet-sm"
          :class="`bullet-${topicViewData.status == 'approved' ? 'success' : (topicViewData.status == 'rejected' ? 'danger' : 'warning')}`"
        />

        <!-- Mark Starred -->
        <feather-icon
          icon="StarIcon"
          size="17"
          class="cursor-pointer"
          :class="{ 'text-warning fill-current': topicViewData.is_favorite }"
          @click="$emit('toggle-topic-starred')"
        />

        
      </div>
    </div>

    <!-- Email Details -->
    <vue-perfect-scrollbar
      :settings="perfectScrollbarSettings"
      class="email-scroll-area scroll-area"
    >     
      <!-- Email Thread -->
      <b-row>
        <b-col cols="12 mt-2">
          <topic-message-card :message="topicViewData" 
          @accept-status="$emit('accept-status')"
          @reject-status="$emit('reject-status')"/>
        </b-col>
      </b-row>

      <!-- Email Actions: Reply or Forward -->
      <b-row>
        <b-col cols="12">
          <b-card>
            <div class="d-flex justify-content-between">
              <h5 class="mb-0">
                Click here to
                <b-link @click="$emit('accept-status')">Accept</b-link>
                or
                <b-link @click="$emit('reject-status')">Reject</b-link>
              </h5>
            </div>
          </b-card>
        </b-col>
      </b-row>
    </vue-perfect-scrollbar>
  </div>
</template>

<script>
import {
  BDropdown, BDropdownItem, BRow, BCol, BBadge, BCard, BLink,
} from 'bootstrap-vue'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import { ref, watch } from '@vue/composition-api'
import useEmail from './useEmail'
import TopicMessageCard from './TopicMessageCard.vue'

export default {
  components: {

    // BSV
    BDropdown,
    BDropdownItem,
    BRow,
    BCol,
    BBadge,
    BCard,
    BLink,

    // 3rd Party
    VuePerfectScrollbar,

    // SFC
    TopicMessageCard,
  },
  props: {
    topicViewData: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    const { resolveLabelColor } = useEmail()

    const perfectScrollbarSettings = {
      maxScrollbarLength: 150,
    }

    const showWholeThread = ref(false)

    // watch(() => props.emailViewData.id, () => {
    //   showWholeThread.value = false
    // })

    return {

      // UI
      perfectScrollbarSettings,
      showWholeThread,

      // useEmail
      resolveLabelColor,
    }
  },
}
</script>

<style>

</style>
