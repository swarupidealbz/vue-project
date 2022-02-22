<template>
<div>
<b-nav>
  <b-nav-item-dropdown
    id="my-nav-dropdown"
    :text="currentLocale.name"
    toggle-class="nav-link-custom"
    right
  >
    <!-- <template #button-content>
      <span class="ml-50 text-body">{{ currentLocale.name }}</span>
    </template> -->
    <b-dropdown-item
      key="title"
      disabled
    >
      <span class="ml-50">Websites</span>
    </b-dropdown-item>
    <b-dropdown-divider />
    <b-dropdown-item
      v-for="website in websites"
      :key="'web-'+website.id"
      @click="setLocal(website)"
    >
      <span class="ml-50">{{ website.name }}</span>
    </b-dropdown-item>
  </b-nav-item-dropdown>
</b-nav>
</div>
</template>

<script>
import { BNavItemDropdown, BDropdownItem, BImg, BDropdownDivider, BNav } from 'bootstrap-vue'

export default {
  components: {
    BNavItemDropdown,
    BDropdownItem,
    BImg,
    BDropdownDivider,
    BNav,
  },
  props: {
    websites: {}
  },
  // ! Need to move this computed property to comp function once we get to Vue 3
  computed: {
    currentLocale() {
      return this.$store.state.app.selectedWebsite || {}
    },
  },
  methods: {
    setLocal(website) {
      this.$store.commit('app/setSelectedWebsite', website);
      localStorage.setItem('website', JSON.stringify(website))
      this.$store.dispatch('app/loadTopics', {website:this.$store.state.app.selectedWebsite.id}).then(res => {
        this.$router.push('/')
      })
    }
  }
}
</script>

<style>

.b-nav-dropdown .dropdown-toggle::after {
  background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236e6b7b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-chevron-down'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E") !important;
}
</style>
