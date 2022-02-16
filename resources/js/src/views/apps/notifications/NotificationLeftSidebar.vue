<template>
  <div class="sidebar-left">
    <div class="sidebar">
      <div class="sidebar-content email-app-sidebar">
        <div class="email-app-menu">          
            <!-- Filters -->
            <b-list-group class="list-group-messages">
              <b-list-group-item
                key="all-topic"
                :active="activeAll"
                @click="showAll"
                class="cursor-pointer"
              >
                <span class="align-text-bottom line-height-1">All</span>                
              </b-list-group-item>
              <b-list-group-item
                key="all-read"
                :active="isActive == 'read'"
                @click="filter('read')"
                class="cursor-pointer"
              >
                <span class="align-text-bottom line-height-1">Read</span>                
              </b-list-group-item>
              <b-list-group-item
                key="all-unread"
                :active="isActive == 'unread'"
                @click="filter('unread')"
                class="cursor-pointer"
              >
                <span class="align-text-bottom line-height-1">Unread</span>                
              </b-list-group-item>
            </b-list-group>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import {
  BButton, BListGroup, BListGroupItem, BBadge,
} from 'bootstrap-vue'
import { isDynamicRouteActive } from '@core/utils/utils'
import Ripple from 'vue-ripple-directive'

export default {
  directives: {
    Ripple,
  },
  components: {

    // BSV
    BButton,
    BListGroup,
    BListGroupItem,
    BBadge,

    // 3rd Party
    VuePerfectScrollbar,
  },
  props: {
    
  },
  beforeCreate() {
    this.$store.dispatch('app/loadNotifications')
  },
  computed: {
    activeAll() {
      return (this.$store.state.app.selectedNotificationType != 'read') && (this.$store.state.app.selectedNotificationType != 'unread');
    }
  },
  methods: {
    isActive() {
      return this.$store.state.app.selectedNotificationType;
    },
    filter(type) {
      this.$emit('close-left-sidebar');
      this.$store.commit('app/setSelectedNotificationType', type);
      this.$store.dispatch('app/loadNotifications', { type: type});
    },
    showAll() {
      this.$store.commit('app/setSelectedNotificationType', '');
      this.$store.dispatch('app/loadNotifications');
    }
  },
  setup() {
    const perfectScrollbarSettings = {
      maxScrollbarLength: 60,
    }

    const emailFilters = [
      { title: 'Inbox', icon: 'MailIcon', route: { name: 'apps-email' } },
      { title: 'Sent', icon: 'SendIcon', route: { name: 'apps-email-folder', params: { folder: 'sent' } } },
      { title: 'Draft', icon: 'Edit2Icon', route: { name: 'apps-email-folder', params: { folder: 'draft' } } },
      { title: 'Starred', icon: 'StarIcon', route: { name: 'apps-email-folder', params: { folder: 'starred' } } },
      { title: 'Spam', icon: 'InfoIcon', route: { name: 'apps-email-folder', params: { folder: 'spam' } } },
      { title: 'Trash', icon: 'TrashIcon', route: { name: 'apps-email-folder', params: { folder: 'trash' } } },
    ]

    const emailLabel = [
      { title: 'Personal', color: 'success', route: { name: 'apps-email-label', params: { label: 'personal' } } },
      { title: 'Company', color: 'primary', route: { name: 'apps-email-label', params: { label: 'company' } } },
      { title: 'Important', color: 'warning', route: { name: 'apps-email-label', params: { label: 'important' } } },
      { title: 'Private', color: 'danger', route: { name: 'apps-email-label', params: { label: 'private' } } },
    ]

    const resolveFilterBadgeColor = filter => {
      if (filter === 'Draft') return 'light-warning'
      if (filter === 'Spam') return 'light-danger'
      return 'light-primary'
    }

    return {
      // UI
      perfectScrollbarSettings,
      isDynamicRouteActive,
      resolveFilterBadgeColor,

      // Filter & Labels
      emailFilters,
      emailLabel,
    }
  },
}
</script>

<style>

</style>
