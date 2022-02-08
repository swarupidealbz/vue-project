<template>
  <!-- Need to add height inherit because Vue 2 don't support multiple root ele -->
  <div style="height: inherit">
    <div
      class="body-content-overlay"
      :class="{'show': mqShallShowLeftSidebar}"
      @click="mqShallShowLeftSidebar = false"
    />

    <!-- Email List -->
    <div class="email-app-list">

      <!-- App Searchbar Header -->
      <div class="app-fixed-search d-flex align-items-center">

        <!-- Toggler -->
        <div class="sidebar-toggle d-block d-lg-none ml-1">
          <feather-icon
            icon="MenuIcon"
            size="21"
            class="cursor-pointer"
            @click="mqShallShowLeftSidebar = true"
          />
        </div>

        <!-- Searchbar -->
        <!-- <div class="d-flex align-content-center justify-content-between w-100">
          <b-input-group class="input-group-merge">
            <b-input-group-prepend is-text>
              <feather-icon
                icon="SearchIcon"
                class="text-muted"
              />
            </b-input-group-prepend>
            <b-form-input
              :value="searchQuery"
              placeholder="Search email"
              @input="updateRouteQuery"
            />
          </b-input-group>
        </div> -->
      </div>

      <!-- App Action Bar -->
      <div class="app-action">
        <div class="action-left">
          <b-form-checkbox
            :checked="selectAllEmailCheckbox"
            :indeterminate="isSelectAllEmailCheckboxIndeterminate"
            @change="selectAllCheckboxUpdate"
          >
            Select All
          </b-form-checkbox>
        </div>
        <div
          v-show="selectedEmails.length"
          class="align-items-center"
          :class="{'d-flex': selectedEmails.length}"
        >

          <feather-icon
            icon="CheckCircleIcon"
            size="21"
            class="cursor-pointer ml-1 text-success"
          />

          <feather-icon
            icon="XCircleIcon"
            size="21"
            class="cursor-pointer ml-1 text-danger"
          />

        </div>
      </div>

      <!-- Emails List -->
      <vue-perfect-scrollbar
        :settings="perfectScrollbarSettings"
        class="email-user-list scroll-area"
      >
        <ul class="email-media-list">
          <b-media
            v-for="topic in topics"
            :key="topic.id"
            tag="li"
            no-body
          >

            <b-media-aside class="media-left mr-50">                          
              <div class="user-action">
                 <b-form-checkbox
                  :checked="selectedTopics.includes(topic.id)"
                  @change="toggleSelectedMail(topic.id)"
                  @click.native.stop
                /> 
                <div class="email-favorite">
                  <feather-icon
                    icon="StarIcon"
                    size="17"
                    :class="{ 'text-warning fill-current': topic.is_favorite }"
                    @click.stop="toggleStarred(topic)"
                  />
                </div>
              </div>
            </b-media-aside>

            <b-media-body 
            @click="openTopicDetails(topic)">
              <div class="mail-details">
                <div class="mail-items">
                  <span class="text-truncate">{{ topic.topic }}</span>
                </div>
                <div class="mail-meta-item">
                  <span
                    class="mx-50 bullet bullet-sm"
                    :class="`bullet-${topic.status == 'approved' ? 'success' : (topic.status == 'rejected' ? 'danger' : 'warning')}`"
                  />
                  <span class="mail-date">{{ formatDateToMonthShort(topic.created_at, { hour: 'numeric', minute: 'numeric', }) }}</span>
                </div>
              </div>

              <div class="mail-message">
                <p class="text-truncate mb-0">
                  {{ topic.description ? filterTags(topic.description) : '' }}
                </p>
              </div>
            </b-media-body>
              <!-- Dropdown -->
              <div class="dropdown">
                <b-dropdown
                  variant="link"
                  no-caret
                  toggle-class="p-0 ml-1"
                  right
                >
                  <template #button-content>
                    <feather-icon
                      icon="MoreVerticalIcon"
                      size="16"
                      class="align-middle text-body"
                    />
                  </template>
                  <b-dropdown-item>
                    <feather-icon icon="FileIcon" />
                    <span class="align-middle ml-50">Show Content</span>
                  </b-dropdown-item>

                  <b-dropdown-item variant="success" @click="approved(topic)">
                    <feather-icon icon="CheckCircleIcon" />
                    <span class="align-middle ml-50">Accept</span>
                  </b-dropdown-item>

                  <b-dropdown-item variant="danger" @click="reject(topic)">
                    <feather-icon icon="XCircleIcon" />
                    <span class="align-middle ml-50 text-danger">Reject</span>
                  </b-dropdown-item>
                </b-dropdown>
              </div>
          </b-media>
        </ul>
        <div
          class="no-results"
          :class="{'show': !topics.length}"
        >
          <h5>No Items Found</h5>
        </div>
      </vue-perfect-scrollbar>
    </div>

    <!-- Email View/Detail -->
    <topic-view
      :class="{'show': showTopicDetails}"
      :topic-view-data="topicDetails"
      @close-topic-view="showTopicDetails = false"
      @toggle-topic-starred="toggleStarred(topicDetails)"
      @accept-status="acceptStatus"
      @reject-status="rejectStatus"
    />

    <!-- Sidebar -->
    <portal to="content-renderer-sidebar-left">
      <topic-left-sidebar
        :shall-show-email-compose-modal.sync="shallShowEmailComposeModal"
        :emails-meta="emailsMeta"
        :class="{'show': mqShallShowLeftSidebar}"
        @close-left-sidebar="mqShallShowLeftSidebar = false"
      />
    </portal>

    <!-- Compose Email Modal -->
    <email-compose v-model="shallShowEmailComposeModal" />
  </div>
</template>

<script>
import store from '@/store'
import {
  ref, onUnmounted, computed, watch,
  // ref, watch, computed, onUnmounted,
} from '@vue/composition-api'
import {
  BFormInput, BInputGroup, BInputGroupPrepend, BDropdown, BDropdownItem,
  BFormCheckbox, BMedia, BMediaAside, BMediaBody, BAvatar,
} from 'bootstrap-vue'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import { filterTags, formatDateToMonthShort } from '@core/utils/filter'
import { useRouter } from '@core/utils/utils'
import { useResponsiveAppLeftSidebarVisibility } from '@core/comp-functions/ui/app'
import TopicLeftSidebar from './TopicLeftSidebar.vue'
import TopicView from './TopicView.vue'
import emailStoreModule from './emailStoreModule'
import useEmail from './useEmail'
import EmailCompose from './EmailCompose.vue'
import axios from '@axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BFormInput,
    BInputGroup,
    BInputGroupPrepend,
    BDropdown,
    BDropdownItem,
    BFormCheckbox,
    BMedia,
    BMediaAside,
    BMediaBody,
    BAvatar,

    // 3rd Party
    VuePerfectScrollbar,

    // App SFC
    TopicLeftSidebar,
    TopicView,
    EmailCompose,
  },
  computed: {
    topics() {
      return this.$store.state.app.topics;
    }
  },
  methods: {
    acceptStatus() {
      this.$store.dispatch('app/topicStatusUpdate', {
        website: this.$store.state.app.selectedWebsite.id,
        topic: this.$store.state.app.selectedTopic.id,
        status: 'approved'
      }).then((res) => {
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Approved`,
                icon: 'UserCheckIcon',
                variant: 'success',
                text: res.message,
              },
            })
      }).catch(error => {
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Failed`,
                icon: 'UserCheckIcon',
                variant: 'danger',
                text: error.message,
              },
            })
      });
    },
    rejectStatus() {
      this.$store.dispatch('app/topicStatusUpdate', {
        website: this.$store.state.app.selectedWebsite.id,
        topic: this.$store.state.app.selectedTopic.id,
        status: 'rejected'
      }).then((res) => {
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Rejected`,
                icon: 'UserCheckIcon',
                variant: 'success',
                text: res.message,
              },
            })
      }).catch((err) => {
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Failed`,
                icon: 'UserCheckIcon',
                variant: 'danger',
                text: err.message,
              },
            })
      });
    },
    approved(topic) {
      this.$store.commit('app/setSelectedTopic', topic);
      this.$store.dispatch('app/topicStatusUpdate', {
        website: this.$store.state.app.selectedWebsite.id,
        topic: this.$store.state.app.selectedTopic.id,
        status: 'approved'
      }).then((res) => {
        let payload = {
          website: this.$store.state.app.selectedWebsite.id
        }
        if(this.$store.state.app.selectedOrder.id) {
          payload.order = this.$store.state.app.selectedOrder.id
        }
        this.$store.dispatch('app/sortRecord', payload);
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Approved`,
                icon: 'UserCheckIcon',
                variant: 'success',
                text: res.message,
              },
            })
      }).catch(error => {
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Failed`,
                icon: 'UserCheckIcon',
                variant: 'danger',
                text: error.message,
              },
            })
      });
    },
    reject(topic) {
      this.$store.commit('app/setSelectedTopic', topic);
      this.$store.dispatch('app/topicStatusUpdate', {
        website: this.$store.state.app.selectedWebsite.id,
        topic: this.$store.state.app.selectedTopic.id,
        status: 'rejected'
      }).then((res) => {
        let payload = {
          website: this.$store.state.app.selectedWebsite.id
        }
        if(this.$store.state.app.selectedOrder.id) {
          payload.order = this.$store.state.app.selectedOrder.id
        }
        this.$store.dispatch('app/sortRecord', payload);
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Rejected`,
                icon: 'UserCheckIcon',
                variant: 'success',
                text: res.message,
              },
            })
      }).catch((err) => {
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Failed`,
                icon: 'UserCheckIcon',
                variant: 'danger',
                text: err.message,
              },
            })
      });
    }
  },
  setup() {
    const EMAIL_APP_STORE_MODULE_NAME = 'app-email'

    // Register module
    if (!store.hasModule(EMAIL_APP_STORE_MODULE_NAME)) store.registerModule(EMAIL_APP_STORE_MODULE_NAME, emailStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(EMAIL_APP_STORE_MODULE_NAME)) store.unregisterModule(EMAIL_APP_STORE_MODULE_NAME)
    })

    
    const perfectScrollbarSettings = {
      maxScrollbarLength: 150,
    }

    

    // ------------------------------------------------
    // Mail Selection
    // ------------------------------------------------
    const selectedTopics = ref([])
    const toggleSelectedMail = mailId => {
      const mailIndex = selectedTopics.value.indexOf(mailId)

      if (mailIndex === -1) selectedTopics.value.push(mailId)
      else selectedTopics.value.splice(mailIndex, 1)
    }
    const selectAllEmailCheckbox = computed(() => topics.value.length && (topics.value.length === selectedTopics.value.length))
    const isSelectAllEmailCheckboxIndeterminate = computed(() => Boolean(selectedTopics.value.length) && emails.value.length !== selectedTopics.value.length)
    const selectAllCheckboxUpdate = val => {
      selectedTopics.value = val ? emails.value.map(mail => mail.id) : []
    }
    // ? Watcher to reset selectedEmails is somewhere below due to watch dependecy fullfilment

    
    // ------------------------------------------------
    // Email Details
    // ------------------------------------------------
    const showTopicDetails = ref(false)
    const showEmailDetails = ref(false)
    const emailViewData = ref({})
    const topicDetails = ref({})
    
    const openTopicDetails = topic => {
      axios.get(store.state.app.apiBaseUrl + 'primary-topic/show/' + topic.id).then((res) => {
        topicDetails.value = topic
        store.commit('app/setSelectedTopic', topic);
        showTopicDetails.value = true
      })
    }
    
    // Compose
    const shallShowEmailComposeModal = ref(false)

    // Left Sidebar Responsiveness
    const { mqShallShowLeftSidebar } = useResponsiveAppLeftSidebarVisibility()

    return {
      // UI
      perfectScrollbarSettings,

      // Emails & EmailsMeta
      topics,
      emailsMeta,

      // Mail Selection
      selectAllEmailCheckbox,
      isSelectAllEmailCheckboxIndeterminate,
      selectedEmails,
      selectedTopics,
      toggleSelectedMail,
      selectAllCheckboxUpdate,

      // Mail Actions
      openTopicDetails,

      // Email Details
      showTopicDetails,
      topicDetails,

      // Compose
      shallShowEmailComposeModal,

      // Left Sidebar Responsiveness
      mqShallShowLeftSidebar,
    }
  },
}
</script>

<style lang="scss" scoped>
@media(max-width: 460px) {
 .mail-meta-item {
   display: none !important;
 } 
}
</style>

<style lang="scss">
@import "~@core/scss/base/pages/app-email.scss";
</style>
