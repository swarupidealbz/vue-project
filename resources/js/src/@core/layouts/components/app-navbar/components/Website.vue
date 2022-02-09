<template>
  <b-nav-item-dropdown
    id="dropdown-grouped"
    variant="link"
    class="dropdown-language website-dropdown"
    right
  >
    <template #button-content>
      <span class="ml-50 text-body">{{ currentLocale.name }}</span>
    </template>
    <b-dropdown-item
      v-for="website in websites"
      :key="'web-'+website.id"
      @click="setLocal(website)"
    >
      <span class="ml-50">{{ website.name }}</span>
    </b-dropdown-item>
  </b-nav-item-dropdown>
</template>

<script>
import { BNavItemDropdown, BDropdownItem, BImg } from 'bootstrap-vue'

export default {
  components: {
    BNavItemDropdown,
    BDropdownItem,
    BImg,
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
      this.$router.push('/')
    }
  }
}
</script>

<style>

</style>
