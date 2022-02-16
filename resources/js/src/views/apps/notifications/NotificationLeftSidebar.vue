<template>
  <div class="sidebar-left">
    <div class="sidebar">
      <div class="sidebar-content todo-sidebar">
        <div class="todo-app-menu">
          <vue-perfect-scrollbar
            :settings="perfectScrollbarSettings"
            class="sidebar-menu-list scroll-area"
          >
            <!-- Filters -->
            <b-list-group class="list-group-filters">
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
                :active="isActive('read')"
                @click="filter('read')"
                class="cursor-pointer"
              >
                <span class="align-text-bottom line-height-1">Read</span>                
              </b-list-group-item>
              <b-list-group-item
                key="all-unread"
                :active="isActive('unread')"
                @click="filter('unread')"
                class="cursor-pointer"
              >
                <span class="align-text-bottom line-height-1">Unread</span>                
              </b-list-group-item>
            </b-list-group>

            
          </vue-perfect-scrollbar>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import { BButton, BListGroup, BListGroupItem } from 'bootstrap-vue'
import { isDynamicRouteActive } from '@core/utils/utils'
import Ripple from 'vue-ripple-directive'

export default {
  directives: {
    Ripple,
  },
  components: {
    BButton,
    BListGroup,
    BListGroupItem,
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
    isActive(type) {
      return this.$store.state.app.selectedNotificationType == type;
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

    const taskFilters = [
      { title: 'My Task', icon: 'MailIcon', route: { name: 'apps-todo' } },
      { title: 'Important', icon: 'StarIcon', route: { name: 'apps-todo-filter', params: { filter: 'important' } } },
      { title: 'Completed', icon: 'CheckIcon', route: { name: 'apps-todo-filter', params: { filter: 'completed' } } },
      { title: 'Deleted', icon: 'TrashIcon', route: { name: 'apps-todo-filter', params: { filter: 'deleted' } } },
    ]

    return {
      perfectScrollbarSettings,
      taskFilters,
      isDynamicRouteActive,
    }
  },
}
</script>

<style>

</style>
