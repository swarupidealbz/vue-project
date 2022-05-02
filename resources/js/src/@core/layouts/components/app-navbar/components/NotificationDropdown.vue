<template>
  <b-nav-item-dropdown
    class="dropdown-notification mr-25"
    menu-class="dropdown-menu-media"
    right
  >
    <template #button-content>
      <feather-icon
        :badge="count"
        badge-classes="bg-danger"
        class="text-body"
        icon="BellIcon"
        size="21"
      />
    </template>

    <!-- Header -->
    <li class="dropdown-menu-header">
      <div class="dropdown-header d-flex">
        <h4 class="notification-title mb-0 mr-auto">
          Notifications
        </h4>
        <b-badge
          pill
          variant="light-primary"
        >
          {{ count }} New
        </b-badge>
      </div>
    </li>

    <!-- Notifications -->
    <vue-perfect-scrollbar
      
      :settings="perfectScrollbarSettings"
      class="scrollable-container media-list scroll-area"
      tagname="li"
    >
      <!-- Account Notification -->
      <b-link
        v-for="(notification, key) in list"
        :key="notification.heading+key"
      >
        <!-- <b-media @click="readNotification(notification)"> -->
        <b-dropdown-item @click="readNotification(notification)">
          <p class="media-heading mb-0">
            <span class="font-weight-bolder">
              {{ notification.heading }}
            </span>
          </p>
          <small class="notification-text">{{ notification.details }}</small>
        </b-dropdown-item>
        <!-- </b-media> -->
      </b-link>

      <!-- System Notification Toggler -->
      <div class="media d-flex align-items-center" v-if="isAdmin">
        <h6 class="font-weight-bolder mr-auto mb-0">
          System Notifications
        </h6>
        <b-form-checkbox
          :checked="true"
          switch
        />
      </div>

      <!-- System Notifications -->
      <b-link
        v-for="notification in systemNotifications"
        :key="notification.subtitle"
      >
        <b-media>
          <template #aside>
            <b-avatar
              size="32"
              :variant="notification.type"
            >
              <feather-icon :icon="notification.icon" />
            </b-avatar>
          </template>
          <p class="media-heading">
            <span class="font-weight-bolder">
              {{ notification.title }}
            </span>
          </p>
          <small class="notification-text">{{ notification.subtitle }}</small>
        </b-media>
      </b-link>
    </vue-perfect-scrollbar>

    <!-- Cart Footer -->
    
    <b-dropdown-item
      class="dropdown-menu-footer p-0"
      link-class="align-items-center"
    >      
      <b-button
      v-ripple.400="'rgba(255, 255, 255, 0.15)'"
      variant="primary"
      block
      :to="{ name: 'notifications' }"
    >Read all notifications</b-button>
    </b-dropdown-item>
  </b-nav-item-dropdown>
</template>

<script>
import {
  BNavItemDropdown, BBadge, BMedia, BLink, BAvatar, BButton, BFormCheckbox,BDropdownItem
} from 'bootstrap-vue'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import Ripple from 'vue-ripple-directive'
import store from '@/store/index'
import { isUserLoggedIn, getUserData, isAdmin } from '@/auth/utils'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'


export default {
  components: {
    BNavItemDropdown,
    BBadge,
    BMedia,
    BLink,
    BAvatar,
    VuePerfectScrollbar,
    BButton,
    BFormCheckbox,
    BDropdownItem,
  },
  directives: {
    Ripple,
  },
  props: {
    list: {}
  },
  data() {
    return {
      systemNotifications: [],
      perfectScrollbarSettings:{
        maxScrollbarLength: 60,
        wheelPropagation: false,
      },
    }
  },
  computed: {
    count() {
      return store.state.app.topBar.notifications.count;
    },
    isAdmin() {
      return isAdmin();
    },
  },
  methods: {
    readNotification(notification) {
      notification.is_read = true;
      this.updateNotification(notification)
    },
    updateNotification(notification) {
      console.log(notification)
      this.$store.dispatch('app/updateNotification', notification).then(res => {
        this.$store.dispatch('app/loadTop');
        this.$router.push('/topic/timeline/'+notification.object_to_id )
      }).catch(err => {
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Failed`,
                icon: 'UserCheckIcon',
                variant: 'danger',
                text: 'Can not show topic.',
              },
            })
      })
    }
  }
}
</script>

<style>
.dropdown-item:hover {
  background-color: #fff;
}

</style>
