<template>
  <b-nav-item-dropdown
    id="dropdown-grouped"
    variant="link"
    :text="currentLocale.name"
    class="website-dropdown mr-1"
    right
  >
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
</template>

<script>
import { BNavItemDropdown, BDropdownItem, BImg, BDropdownDivider } from 'bootstrap-vue'

export default {
  components: {
    BNavItemDropdown,
    BDropdownItem,
    BImg,
    BDropdownDivider,
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
      localStorage.setItem('website', JSON.stringify(website))
      this.$store.commit('app/setSelectedWebsite', website)
      this.$store.dispatch('app/loadAppData');
      this.$store.dispatch('app/loadTopics', {website:this.$store.state.app.selectedWebsite.id}).then(res => {
        if(this.$router.history.current.name != 'dashboard') {
          this.$router.push('/')
        }
      })
    }
  }
}
</script>

<style>

</style>
