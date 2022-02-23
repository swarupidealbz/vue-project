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

    <div>
    <b-nav>
      <b-nav-item active>
        Active
      </b-nav-item>
      <b-nav-item>Link</b-nav-item>
      <b-nav-item-dropdown
        id="my-nav-dropdown"
        text="Dropdown"
        toggle-class="nav-link-custom"
        right
      >
        <b-dropdown-item>One</b-dropdown-item>
        <b-dropdown-item>Two</b-dropdown-item>
        <b-dropdown-divider />
        <b-dropdown-item>Three</b-dropdown-item>
      </b-nav-item-dropdown>
    </b-nav>
  </div>

    <b-navbar-nav class="nav align-items-center ml-auto">
      <website :websites="websites"/>
      <locale :locales="languages"/>
      <dark-Toggler class="d-none d-lg-block" />
      <search-bar v-if="isAdmin"/>
      <cart-dropdown v-if="isAdmin"/>
      <notification-dropdown :list="list"/>
      <user-dropdown />
    </b-navbar-nav>
  </div>
</template>

<script>
import {
  BLink, BNavbarNav,BNav, BNavItem, BNavItemDropdown, BDropdownDivider, BDropdownItem,
} from 'bootstrap-vue'
import Bookmarks from './components/Bookmarks.vue'
import Website from './components/Website.vue'
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
    BNav,
    BNavItem,BNavItemDropdown, BDropdownDivider, BDropdownItem,
    Bookmarks,
    Website,
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
    list() {
      return store.state.app.topBar.notifications.data;
    },
    languages() {
      return store.state.app.topBar.languages.map(m => {
        return {
          locale: m.sort_name.toLowerCase(),
          img: require(`@/assets/images/flags/${m.sort_name.toLowerCase()}.png`),
          name: m.name
        }
      });
    },
    websites() {
      return store.state.app.topBar.websites;
    }
  },
}
</script>
<style lang="scss" scoped>
@media(max-width: 460px) {
 .website-dropdown {
   display: none !important;
 } 
}
</style>