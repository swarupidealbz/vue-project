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

      <div class="app-action">
        <div class="action-left">
          <span class="go-back mr-1">
            <feather-icon
              icon="XIcon"
              size="25"
              class="align-bottom"
              
              @click.stop="removeChildTopicShow"
            />
          </span>
          <span class="justify">
            <strong>{{ topicName }}</strong>
          </span>
        </div>
        <div
          class="align-items-center d-flex"
        >
        </div>        

      </div>

      <!-- App Action Bar -->
      <div class="app-action">
        <div class="action-left">
          <b-form-checkbox
            :checked="selectAllEmailCheckbox"
            :indeterminate="isSelectAllEmailCheckboxIndeterminate"
            @change="selectAllCheckboxUpdate"
            v-if="!isWriter"
          >
            Select All
          </b-form-checkbox>
        </div>
        <div
          v-show="selectedEmails.length"
          class="align-items-center"
          :class="{'d-flex': selectedEmails.length}"
        >

          <b-button
            v-ripple.400="'rgba(40, 199, 111, 0.15)'"
            variant="flat-success"
            class="btn-sm"
            @click="bulkApproved"
            v-if="!isWriter"
          >
            Accept
          </b-button>

          <b-button
            v-ripple.400="'rgba(40, 199, 111, 0.15)'"
            variant="flat-danger"
            class="btn-sm"
            @click="bulkReject"
            v-if="!isWriter"
          >
            Reject
          </b-button>
        </div>

      </div>

      <!-- Emails List -->
      <vue-perfect-scrollbar
        :settings="perfectScrollbarSettings"
        class="email-user-list scroll-area"
      >
        <div class="text-center mt-5" v-if="loader">
          <b-spinner variant="primary" style="width: 3rem; height: 3rem;" label="Loading..."/>
        </div>
        <ul class="email-media-list" v-else>
          <b-media
            v-for="topic in topics"
            :key="topic.id"
            tag="li"
            no-body
          >

            <b-media-aside class="media-left mr-50">                          
              <div class="user-action">
                 <b-form-checkbox
                  :checked="selectedEmails.includes(topic.id)"
                  @change="toggleSelectedMail(topic.id)"
                  @click.native.stop
                  v-if="!isWriter"
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
                  <span class="text-truncate" v-html="topic.topic"></span>
                </div>
                <div class="mail-meta-item">
                  <b-avatar
                    size="30"
                    :src="topic.assignee.profile_image"
                    variant="light-primary"
                    v-b-tooltip.hover.v-primary
                    :title="topic.assignee.name"
                    v-if="topic.assignee_id"
                  >
                  </b-avatar>
                  <span
                    class="mx-50 bullet bullet-sm"
                    :class="`bullet-${topic.status == 'approved' ? 'success' : (topic.status == 'rejected' ? 'danger' : 'warning')}`"
                  />
                  <span class="mail-date">{{ formatDateToMonthShort(topic.created_at, { hour: 'numeric', minute: 'numeric', }) }}</span>
                </div>
              </div>

              <div class="mail-message">
                <p class="text-truncate mb-0" v-html="(topic.description ? filterTags(topic.description) : '')">
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
                  <b-dropdown-item
                  @click="editTopic(topic)"
                  v-if="!isWriter && topic.is_editable"
                  >
                    <feather-icon icon="EditIcon" />
                    <span class="align-middle ml-50">Edit</span>
                  </b-dropdown-item>
                  <b-dropdown-item
                  @click="assigneSelf(topic)"
                  v-if="canAssign(topic)"
                  >
                    <feather-icon icon="FileIcon" />
                    <span class="align-middle ml-50">Assign to Self</span>
                  </b-dropdown-item>
                  <b-dropdown-item
                  @click="removeAssign(topic)"
                  v-if="canRemoveAssign(topic)"
                  >
                    <feather-icon icon="FileIcon" />
                    <span class="align-middle ml-50">Remove from assign</span>
                  </b-dropdown-item>
                  <b-dropdown-item
                  :to="{ name: 'topic-timeline', params: { id: topic.id } }"
                  >
                    <feather-icon icon="FileIcon" />
                    <span class="align-middle ml-50">Show Content</span>
                  </b-dropdown-item>

                  <b-dropdown-item
                  @click="addContentBlock(topic.id)"
                  v-if="isWriter"
                  :disabled="addContentDisabled(topic)"
                  >
                    <feather-icon icon="PlusIcon" />
                    <span class="align-middle ml-50">Add Content</span>
                  </b-dropdown-item>

                  <b-dropdown-item variant="success" @click="approved(topic)" v-if="!isWriter">
                    <feather-icon icon="CheckCircleIcon" />
                    <span class="align-middle ml-50">Accept</span>
                  </b-dropdown-item>

                  <b-dropdown-item variant="danger" @click="reject(topic)" v-if="!isWriter">
                    <feather-icon icon="XCircleIcon" />
                    <span class="align-middle ml-50 text-danger">Reject</span>
                  </b-dropdown-item>
                </b-dropdown>
              </div>
              
          </b-media>
              <div class="text-center mt-1" v-if="show">
                <b-link @click="more">Show more</b-link>
              </div>
        </ul>
        <div
          class="no-results"
          :class="{'show': !topics.length}"
          v-if="!loader"
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
      @edit-topic="editTopic(topicDetails)"
    />

    <!-- Sidebar -->
    <portal to="content-renderer-sidebar-left">
      <topic-left-sidebar
        :shall-show-email-compose-modal.sync="shallShowEmailComposeModal"
        :is-task-handler-sidebar-active.sync="isTaskHandlerSidebarActive"
        :emails-meta="emailsMeta"
        :class="{'show': mqShallShowLeftSidebar}"
        @close-left-sidebar="mqShallShowLeftSidebar = false"
        @close-topic-view="showTopicDetails = false"
      />
    </portal>

    <!-- Compose Email Modal -->
    <email-compose v-model="shallShowEmailComposeModal" />

    <!-- Task Handler -->
    <todo-task-handler-sidebar
      v-model="isTaskHandlerSidebarActive"
      :task="task"
      :clear-task-data="clearTaskData" 
      :topic-details.sync="topicDetails"
    />

    <content-task-handler-sidebar
      v-model="isContentHandlerSidebarActive"
      :task="task"
      :clear-task-data="clearTaskData" 
      :t-id="topicId"
      @reload-content="reloadContent"
    />
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
  BFormCheckbox, BMedia, BMediaAside, BMediaBody, BAvatar,BButton, BSpinner, BLink, VBTooltip
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
import TodoTaskHandlerSidebar from './TodoTaskHandlerSidebar.vue'
import Ripple from 'vue-ripple-directive'
import ContentTaskHandlerSidebar from './ContentTaskHandlerSidebar.vue'

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
    BButton,
    BSpinner,
    BLink,
    VBTooltip,

    // 3rd Party
    VuePerfectScrollbar,

    // App SFC
    TopicLeftSidebar,
    TopicView,
    EmailCompose,
    TodoTaskHandlerSidebar,
    ContentTaskHandlerSidebar,
  },
  directives: {
    'b-tooltip': VBTooltip,
    Ripple,
  },
  data() {
    return {
      isContentHandlerSidebarActive: false,
      topicId: '',
      loadingTopic: 10,
    }
  },
  computed: {
    topics() {
      return this.$store.state.app.childtopics;
    },
    loader() {
      return this.$store.state.app.loading;
    },
    isWriter() {
      let user = JSON.parse(localStorage.getItem('userData'))
      return user.role == 'writer';
    },
    user() {
      return JSON.parse(localStorage.getItem('userData'));
    },
    show() {
      return this.$store.state.app.topicMore;
    },
    topicName() {
      let local = localStorage.getItem('selectedTopic')
      if(local) {
        local = JSON.parse(local)
        console.log(local);
        return local.topic
      }

      return '';
    },
    localTopic() {
      let local = localStorage.getItem('selectedTopic')
      if(local) {
        local = JSON.parse(local)
      }
      return local
    }
  },
  methods: {
    editTopic(topic) {
      this.isTaskHandlerSidebarActive = true;
      topic.type = topic.is_primary_topic;
      this.task = topic;
    },
    removeChildTopicShow() {
      this.$store.commit('app/setShowChild', false);
      this.$store.commit('app/setTopics', []);
      this.$store.commit('app/setGroups', []);
      this.$store.commit('app/setTopicCount', 0);
      this.$store.commit('app/setSelectedTopic', {})
      this.$store.dispatch('app/loadTopics', {website:this.$store.state.app.selectedWebsite.id})
      localStorage.removeItem('selectedTopic')
      this.$router.push('topics');
    },
    more() {
      this.$store.commit('app/setTopicMore', false);
      this.$store.dispatch('app/loadMoreTopic', { 
        website: this.$store.state.app.selectedWebsite.id, 
        order: this.$store.state.app.selectedOrder.id,
        off: this.loadingTopic,
        primary_topic_id: this.localTopic.id && this.$store.state.app.showChild ? this.localTopic.id : ''
      }).then(res => {
        this.loadingTopic += 10;
      });
    },
    addContentBlock(tId){
      this.isContentHandlerSidebarActive = true
      this.topicId = tId;
    },
    reloadContent() {
      this.$router.push('/topic/timeline/'+this.topicId);
    },
    addContentDisabled(topic) {
      if(topic.assignee_id) {
        if(topic.assignee_id != this.user.id) {
          return true;
        }
      }
      return false;
    },
    canAssign(topic) {
      if(this.isWriter) {
        if(topic.can_self_assign) {
          return true;
        }
      }

      return false;
    },
    canRemoveAssign(topic) {
      if(this.isWriter) {
        if(topic.assignee_id == this.user.id) {
          return true;
        }
      }

      return false;
    },
    assigneSelf(topic) {
      this.$store.dispatch('app/topicSelfAssign', {
        assignee: this.user.id,
        topic: topic.id,
        action: 'assign'
      }).then((res) => {
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Assigned`,
                icon: 'UserCheckIcon',
                variant: 'success',
                text: res.message,
              },
            })
        this.$store.dispatch('app/sortRecord', { 
          website: this.$store.state.app.selectedWebsite.id,
          primary_topic_id: this.localTopic.id && this.$store.state.app.showChild ? this.localTopic.id : ''
         });
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
    removeAssign(topic) {
      this.$store.dispatch('app/topicSelfAssign', {
        assignee: this.user.id,
        topic: topic.id,
        action: 'unassign'
      }).then((res) => {
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Assigned`,
                icon: 'UserCheckIcon',
                variant: 'success',
                text: res.message,
              },
            })
        this.$store.dispatch('app/sortRecord', { 
          website: this.$store.state.app.selectedWebsite.id,
          primary_topic_id: this.localTopic.id && this.$store.state.app.showChild ? this.localTopic.id : ''
        });
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
          website: this.$store.state.app.selectedWebsite.id,
          primary_topic_id: this.localTopic.id && this.$store.state.app.showChild ? this.localTopic.id : ''
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
          website: this.$store.state.app.selectedWebsite.id,
          primary_topic_id: this.localTopic.id && this.$store.state.app.showChild ? this.localTopic.id : ''
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
    },
    bulkApproved() {
      this.$store.dispatch('app/topicStatusUpdate', {
        website: this.$store.state.app.selectedWebsite.id,
        topic: this.selectedEmails.join(','),
        status: 'approved'
      }).then((res) => {
        let payload = {
          website: this.$store.state.app.selectedWebsite.id,
          primary_topic_id: this.localTopic.id && this.$store.state.app.showChild ? this.localTopic.id : ''
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
          website: this.$store.state.app.selectedWebsite.id,
          primary_topic_id: this.localTopic.id && this.$store.state.app.showChild ? this.localTopic.id : ''
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
    toggleStarred(topic) {
      let url = 'app/setFavorite'
      if(topic.is_favorite) {
        url = 'app/setUnfavorite'
      }
      store.dispatch(url, {
        topic: topic.id,
      }).then((res) => {
        let payload = {
          website: store.state.app.selectedWebsite.id,
          primary_topic_id: this.localTopic.id && this.$store.state.app.showChild ? this.localTopic.id : ''
        }
        if(store.state.app.selectedOrder.id) {
          payload.order = store.state.app.selectedOrder.id
        }
        if(this.topicDetails.id) {
          this.topicDetails.is_favorite = res.data.is_favorite;
        }
        store.dispatch('app/sortRecord', payload);
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Success`,
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
      selectedEmails.value = val ? store.state.app.childtopics.map(mail => mail.id) : []
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
      content_type: 'article',
      assignee: null,
      tags: [],
      isCompleted: false,
      isDeleted: false,
      isImportant: false,
      type: 1,
      primary_topic_id: '',
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

      // useEmail
      resolveLabelColor,

      // Compose
      shallShowEmailComposeModal,

      isTaskHandlerSidebarActive,
      task,
      clearTaskData,

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
