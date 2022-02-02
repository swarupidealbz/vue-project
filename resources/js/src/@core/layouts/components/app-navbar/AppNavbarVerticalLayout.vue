<template>
  <div class="navbar-container d-flex content align-items-center">

    <!-- Nav Menu Toggler -->
    <ul class="nav navbar-nav d-xl-none">
      <li class="nav-item">
        <b-link
          class="nav-link"
          @click="toggleVerticalMenuActive"
        >
          <feather-icon
            icon="MenuIcon"
            size="21"
          />
        </b-link>
      </li>
    </ul>

    <!-- Left Col -->
    <div class="bookmark-wrapper align-items-center flex-grow-1 d-none d-lg-flex" v-if="isAdmin">

      <!-- Bookmarks Container -->
      <bookmarks />
    </div>

    <b-navbar-nav class="nav align-items-center ml-auto">
      <locale />
      <dark-Toggler class="d-none d-lg-block" />
      <search-bar />
      <cart-dropdown v-if="isAdmin"/>
      <notification-dropdown />
      <user-dropdown />
    </b-navbar-nav>
  </div>
</template>

<script>
import {
  BLink, BNavbarNav,
} from 'bootstrap-vue'
import Bookmarks from './components/Bookmarks.vue'
import Locale from './components/Locale.vue'
import SearchBar from './components/SearchBar.vue'
import DarkToggler from './components/DarkToggler.vue'
import CartDropdown from './components/CartDropdown.vue'
import NotificationDropdown from './components/NotificationDropdown.vue'
import UserDropdown from './components/UserDropdown.vue'
import { isUserLoggedIn, getUserData, isAdmin } from '@/auth/utils'
import store from '@/store/index'
import axios from '@axios'

export default {
  components: {
    BLink,

    // Navbar Components
    BNavbarNav,
    Bookmarks,
    Locale,
    SearchBar,
    DarkToggler,
    CartDropdown,
    NotificationDropdown,
    UserDropdown,
  },
  props: {
    toggleVerticalMenuActive: {
      type: Function,
      default: () => {},
    },
  },
  computed: {
    isAdmin() {
      return isAdmin();
    },
  },
  beforeMount() {
    axios.post(store.state.app.apiBaseUrl+'dashboard/data').then(response => {
      console.log('dashboard data');
      console.log(response);
      console.log(response.data);
      let top = {
        websites: response.data.data.websites,
        languages: response.data.data.languages,
        notifications: response.data.data.notifications
      };
      let menu = {
        side_menus: response.data.data.side_menus
      };
      let data = {
        statistics: response.data.data.statistics,
        topic_lists: response.data.data.topic_lists,
        article_lists: response.data.data.article_lists
      };
      store.commit('app/setTopBar', top);
      store.commit('app/setMenu', menu);
      store.commit('app/setDashboardData', data);
    })
    .catch(error => {
      console.log('error from loading dashboard api');
      console.log(error);
    });
  },
}
</script>
