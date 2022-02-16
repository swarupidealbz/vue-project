<template>
  <!-- Need to add height inherit because Vue 2 don't support multiple root ele -->
  <div style="height: inherit">
    <div
      class="body-content-overlay"
      :class="{'show': mqShallShowLeftSidebar}"
      @click="mqShallShowLeftSidebar = false"
    />

    <!-- Email List -->
    <div class="todo-app-list">

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
        <div class="d-flex align-content-center justify-content-between w-100">
          <b-input-group class="input-group-merge">
            <b-input-group-prepend is-text>
              <feather-icon
                icon="SearchIcon"
                class="text-muted"
              />
            </b-input-group-prepend>
            <b-form-input
              :value="searchQuery"
              placeholder="Search task"
              @input="updateRouteQuery"
            />
          </b-input-group>
        </div>       
      </div>

      <!-- Todo List -->
      <vue-perfect-scrollbar
        :settings="perfectScrollbarSettings"
        class="todo-task-list-wrapper list-group scroll-area"
      >
        <draggable
          v-model="notifications"
          handle=".draggable-task-handle"
          tag="ul"
          class="todo-task-list media-list"
        >
          <li
            v-for="notification in notifications"
            :key="notification.id"
            class="todo-item"
            :class="{ 'completed': notification.is_read }"
            @click="handleTaskClick(task)"
          >
            <feather-icon
              icon="MoreVerticalIcon"
              class="draggable-task-handle d-inline"
            />
            <div class="todo-title-wrapper">
              <div class="todo-title-area">
                <div class="title-wrapper">
                  <b-form-checkbox
                    :checked="notification.is_read"
                    @click.native.stop
                    @change="updateTaskIsCompleted(notification)"
                  />
                  <span class="todo-title">{{ notification.title }}</span>
                  <small class="text-muted">{{ notification.subtitle }}</small>
                </div>
              </div>
              <div class="todo-item-action">                
                <small class="text-nowrap text-muted mr-1">{{ formatDate(notification.created_at, { month: 'short', day: 'numeric'}) }}</small>
                
              </div>
            </div>

          </li>
        </draggable>
        <div
          class="no-results"
          :class="{'show': !notifications.length}"
        >
          <h5>No Items Found</h5>
        </div>
      </vue-perfect-scrollbar>
    </div>

    
    <!-- Sidebar -->
    <portal to="content-renderer-sidebar-left">
      <notification-left-sidebar
        :emails-meta="emailsMeta"
        :class="{'show': mqShallShowLeftSidebar}"
        @close-left-sidebar="mqShallShowLeftSidebar = false"
      />
    </portal>

   
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
import draggable from 'vuedraggable'
import { filterTags, formatDateToMonthShort, formatDate } from '@core/utils/filter'
import { useRouter } from '@core/utils/utils'
import { useResponsiveAppLeftSidebarVisibility } from '@core/comp-functions/ui/app'
import NotificationLeftSidebar from './NotificationLeftSidebar.vue'
import emailStoreModule from './emailStoreModule'
import useEmail from './useEmail'
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
    draggable,

    // 3rd Party
    VuePerfectScrollbar,

    // App SFC
    NotificationLeftSidebar,
  },
  computed: {    
    notifications() {
      return this.$store.state.app.allNotifications;
    },
  },
  methods: {
    bulkApproved() {
      this.$store.dispatch('app/topicStatusUpdate', {
        website: this.$store.state.app.selectedWebsite.id,
        topic: this.selectedEmails.join(','),
        status: 'approved'
      }).then((res) => {
        let payload = {
          website: this.$store.state.app.selectedWebsite.id
        }
        if(this.$store.state.app.selectedOrder.id) {
          payload.order = this.$store.state.app.selectedOrder.id
        }
        this.$store.dispatch('app/sortRecord', payload);
        this.selectedEmails = [];
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
    bulkReject() {
      this.$store.dispatch('app/topicStatusUpdate', {
        website: this.$store.state.app.selectedWebsite.id,
        topic: this.selectedEmails.join(','),
        status: 'rejected'
      }).then((res) => {
        let payload = {
          website: this.$store.state.app.selectedWebsite.id
        }
        if(this.$store.state.app.selectedOrder.id) {
          payload.order = this.$store.state.app.selectedOrder.id
        }
        this.$store.dispatch('app/sortRecord', payload);
        this.selectedEmails = [];
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
  },
  setup() {
    const EMAIL_APP_STORE_MODULE_NAME = 'app-email'

    // Register module
    if (!store.hasModule(EMAIL_APP_STORE_MODULE_NAME)) store.registerModule(EMAIL_APP_STORE_MODULE_NAME, emailStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(EMAIL_APP_STORE_MODULE_NAME)) store.unregisterModule(EMAIL_APP_STORE_MODULE_NAME)
    })

    const { route, router } = useRouter()
    const { resolveLabelColor } = useEmail()

    // Route Params
    const routeParams = computed(() => route.value.params)
    watch(routeParams, () => {
      // eslint-disable-next-line no-use-before-define
      fetchEmails()
    })

    // Emails & EmailsMeta
    const emails = ref([])
    const emailsMeta = ref({})

    const perfectScrollbarSettings = {
      maxScrollbarLength: 150,
    }

    // Search Query
    const routeQuery = computed(() => route.value.query.q)
    const searchQuery = ref(routeQuery.value)
    watch(routeQuery, val => {
      searchQuery.value = val
    })
    // eslint-disable-next-line no-use-before-define
    watch(searchQuery, () => fetchEmails())
    const updateRouteQuery = val => {
      const currentRouteQuery = JSON.parse(JSON.stringify(route.value.query))

      if (val) currentRouteQuery.q = val
      else delete currentRouteQuery.q

      router.replace({ name: route.name, query: currentRouteQuery })
    }

    const fetchEmails = () => {
      store.dispatch('app-email/fetchEmails', {
        q: searchQuery.value,
        folder: router.currentRoute.params.folder || 'inbox',
        label: router.currentRoute.params.label,
      })
        .then(response => {
          emails.value = response.data.emails
          emailsMeta.value = response.data.emailsMeta
        })
    }

    fetchEmails()

    // ------------------------------------------------
    // Mail Selection
    // ------------------------------------------------
    const selectedEmails = ref([])
    const toggleSelectedMail = mailId => {
      const mailIndex = selectedEmails.value.indexOf(mailId)

      if (mailIndex === -1) selectedEmails.value.push(mailId)
      else selectedEmails.value.splice(mailIndex, 1)
    }
    const selectAllEmailCheckbox = computed(() => store.state.app.topics.length && (store.state.app.topics.length === selectedEmails.value.length))
    const isSelectAllEmailCheckboxIndeterminate = computed(() => Boolean(selectedEmails.value.length) && store.state.app.topics.length !== selectedEmails.value.length)
    const selectAllCheckboxUpdate = val => {
      selectedEmails.value = val ? store.state.app.topics.map(mail => mail.id) : []
    }
    // ? Watcher to reset selectedEmails is somewhere below due to watch dependecy fullfilment

    // ------------------------------------------------
    // Mail Actions
    // ------------------------------------------------
    

    const moveSelectedEmailsToFolder = folder => {
      store.dispatch('app-email/updateEmail', {
        emailIds: selectedEmails.value,
        dataToUpdate: { folder },
      })
        .then(() => { fetchEmails() })
        .finally(() => { selectedEmails.value = [] })
    }

    const updateSelectedEmailsLabel = label => {
      store.dispatch('app-email/updateEmailLabels', {
        emailIds: selectedEmails.value,
        label,
      })
        .then(() => { fetchEmails() })
        .finally(() => { selectedEmails.value = [] })
    }

    const markSelectedEmailsAsUnread = () => {
      store.dispatch('app-email/updateEmail', {
        emailIds: selectedEmails.value,
        dataToUpdate: { isRead: false },
      })
        .then(() => { fetchEmails() })
        .finally(() => { selectedEmails.value = [] })
    }

    const handleTaskClick = taskData => {
      task.value = taskData
      isTaskHandlerSidebarActive.value = true
    }


    // Single Task isCompleted update
    const updateTaskIsCompleted = taskData => {
      // eslint-disable-next-line no-param-reassign
      taskData.is_read = !taskData.is_read
      updateTask(taskData)
    }

    const updateTask = taskData => {
      store.dispatch('app/updateNotification', taskData)
      // store.dispatch('app-todo/updateTask', { task: taskData })
      //   .then(() => {
      //     // eslint-disable-next-line no-use-before-define
      //     fetchTasks()
      //   })
    }

    // ------------------------------------------------
    // Email Details
    // ------------------------------------------------
    const showTopicDetails = ref(false)
    const showEmailDetails = ref(false)
    const emailViewData = ref({})
    const topicDetails = ref({})
    const isTaskHandlerSidebarActive = ref(false)
    const blankTask = {
      id: null,
      topic: '',
      dueDate: new Date(),
      description: '',
      assignee: null,
      tags: [],
      isCompleted: false,
      isDeleted: false,
      isImportant: false,
      type: 1,
    }
    const task = ref(JSON.parse(JSON.stringify(blankTask)))
    const clearTaskData = () => {
      task.value = JSON.parse(JSON.stringify(blankTask))
    }
    const opendedEmailMeta = computed(() => {
      const openedEmailIndex = emails.value.findIndex(e => e.id === emailViewData.value.id)
      return {
        hasNextEmail: Boolean(emails.value[openedEmailIndex + 1]),
        hasPreviousEmail: Boolean(emails.value[openedEmailIndex - 1]),
      }
    })
    const openTopicDetails = topic => {
      axios.get(store.state.app.apiBaseUrl + 'primary-topic/show/' + topic.id).then((res) => {
        topicDetails.value = topic
        store.commit('app/setSelectedTopic', topic);
        showTopicDetails.value = true
      })
    }
    const updateEmailViewData = email => {
      // Mark email is read
      store.dispatch('app-email/updateEmail', {
        emailIds: [email.id],
        dataToUpdate: { isRead: true },
      })
        .then(() => {
          // If opened email is unread then decrease badge count for email meta based on email folder
          if (!email.isRead && (email.folder === 'inbox' || email.folder === 'spam')) {
            emailsMeta.value[email.folder] -= 1
          }

          // eslint-disable-next-line no-param-reassign
          email.isRead = true
        })
        .finally(() => {
          emailViewData.value = email
          showEmailDetails.value = true
        })
    }
    const moveOpenEmailToFolder = folder => {
      selectedEmails.value = [emailViewData.value.id]
      moveSelectedEmailsToFolder(folder)
      selectedEmails.value = []
      showEmailDetails.value = false
    }
    const updateOpenEmailLabel = label => {
      selectedEmails.value = [emailViewData.value.id]
      updateSelectedEmailsLabel(label)

      // Update label in opened email
      const labelIndex = emailViewData.value.labels.indexOf(label)
      if (labelIndex === -1) emailViewData.value.labels.push(label)
      else emailViewData.value.labels.splice(labelIndex, 1)

      selectedEmails.value = []
    }

    const markOpenEmailAsUnread = () => {
      selectedEmails.value = [emailViewData.value.id]
      markSelectedEmailsAsUnread()

      selectedEmails.value = []
      showEmailDetails.value = false
    }

    const changeOpenedEmail = dir => {
      const openedEmailIndex = emails.value.findIndex(e => e.id === emailViewData.value.id)
      const newEmailIndex = dir === 'previous' ? openedEmailIndex - 1 : openedEmailIndex + 1
      emailViewData.value = emails.value[newEmailIndex]
    }

    // * If someone clicks on filter while viewing detail => Close the email detail view
    watch(routeParams, () => {
      showEmailDetails.value = false
    })

    // * Watcher to reset selectedEmails
    // ? You can also use showEmailDetails (instead of `emailViewData`) but it will trigger execution twice in this case
    // eslint-disable-next-line no-use-before-define
    watch([emailViewData, routeParams], () => {
      selectedEmails.value = []
    })

    // Compose
    const shallShowEmailComposeModal = ref(false)

    // Left Sidebar Responsiveness
    const { mqShallShowLeftSidebar } = useResponsiveAppLeftSidebarVisibility()

    return {
      // UI
      perfectScrollbarSettings,

      // Emails & EmailsMeta
      emails,
      emailsMeta,

      // Mail Selection
      selectAllEmailCheckbox,
      isSelectAllEmailCheckboxIndeterminate,
      selectedEmails,
      toggleSelectedMail,
      selectAllCheckboxUpdate,

      // Mail Actions
      moveSelectedEmailsToFolder,
      updateSelectedEmailsLabel,
      markSelectedEmailsAsUnread,
      openTopicDetails,

      // Email Details
      showEmailDetails,
      showTopicDetails,
      emailViewData,
      topicDetails,
      opendedEmailMeta,
      updateEmailViewData,
      moveOpenEmailToFolder,
      updateOpenEmailLabel,
      markOpenEmailAsUnread,
      changeOpenedEmail,

      // Search Query
      searchQuery,
      updateRouteQuery,

      // UI Filters
      filterTags,
      formatDateToMonthShort,
      formatDate,

      // useEmail
      resolveLabelColor,

      // Compose
      shallShowEmailComposeModal,

      isTaskHandlerSidebarActive,
      task,
      clearTaskData,
      updateTaskIsCompleted,

      // Left Sidebar Responsiveness
      mqShallShowLeftSidebar,
    }
  },
}
</script>

<style lang="scss" scoped>
.draggable-task-handle {
position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    visibility: hidden;
    cursor: move;

    .todo-task-list .todo-item:hover & {
      visibility: visible;
    }
}
</style>

<style lang="scss">
@import "~@core/scss/base/pages/app-todo.scss";
</style>

