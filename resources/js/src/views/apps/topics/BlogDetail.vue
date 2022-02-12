<template>
  <content-with-sidebar
    
    class="cws-container cws-sidebar-right blog-wrapper"
  >
    <span class="go-back mb-1">
          <feather-icon
            icon="XIcon"
            size="23"
            class="align-bottom mb-1 hand cursor-pointer"
            @click="back"
          />
        </span>

    <!-- content -->
    <div class="blog-detail-wrapper" v-if="Object.keys(blogDetail).length">
      <b-row v-for="(item, index) in blogDetail" :key="(item.content_type ? 'content-' : 'comment-')+index">
        <!-- blogs -->
        <b-col cols="12" 
            v-if="item.content_type">
          <b-card
            :img-src="item.img"
            img-top
            img-alt="Blog Detail Pic"
            :title="item.title"
          >
            <b-media no-body>
              <b-media-aside
                vertical-align="center"
                class="mr-50"
              >
                <b-avatar
                  href="javascript:void(0)"
                  size="24"
                  :src="item.avatar"
                />
              </b-media-aside>
              <b-media-body>
                <small class="text-muted mr-50">by</small>
                <small>
                  <b-link class="text-body">{{ item.created_user.name }}</b-link>
                </small>
                <span class="text-muted ml-75 mr-50">|</span>
                <small class="text-muted">{{ fullDate(item.created_at) }}</small>
              </b-media-body>
            </b-media>
            <!-- <div class="my-1 py-25">
              <b-link
                v-for="tag in blogDetail.blog.tags"
                :key="tag"
              >
                <b-badge
                  pill
                  class="mr-75"
                  :variant="tagsColor(tag)"
                >
                  {{ tag }}
                </b-badge>
              </b-link>
            </div> -->
            <!-- eslint-disable vue/no-v-html -->
            <div
              class="blog-content"
              v-html="item.description"
            />

            <!-- user commnets -->
            <!-- <b-media
              v-for="user in blogDetail.blog.UserComment"
              :key="user.avatar"
              no-body
            >
              <b-media-aside>
                <b-avatar
                  size="60"
                  :src="user.avatar"
                />
              </b-media-aside>
              <b-media-body>
                <h6 class="font-weight-bolder">
                  {{ user.fullName }}
                </h6>
                <b-card-text>
                  {{ user.comment }}
                </b-card-text>
              </b-media-body>
            </b-media> -->
            <!-- eslint-enable -->
            <hr class="my-2">

            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <div class="d-flex align-items-center mr-1">
                  <b-link class="mr-50">
                    <feather-icon
                      icon="MessageSquareIcon"
                      size="21"
                      class="text-body"
                    />
                  </b-link>
                  <b-link>
                    <div class="text-body">
                      {{ kFormatter(19989) }}
                    </div>
                  </b-link>
                </div>
                <div class="d-flex align-items-center">
                  <b-link class="mr-50">
                    <feather-icon
                      size="21"
                      icon="BookmarkIcon"
                      class="text-body"
                    />
                  </b-link>
                  <b-link>
                    <div class="text-body">
                      {{ kFormatter(1134) }}
                    </div>
                  </b-link>
                </div>
              </div>

              <!-- dropdown -->
              <div class="blog-detail-share">
                <b-dropdown
                  variant="link"
                  toggle-class="p-0"
                  no-caret
                  right
                >
                  <template #button-content>
                    <feather-icon
                      size="21"
                      icon="Share2Icon"
                      class="text-body"
                    />
                  </template>
                  <b-dropdown-item
                    v-for="icon in socialShareIcons"
                    :key="icon"
                    href="#"
                  >
                    <feather-icon
                      :icon="icon"
                      size="18"
                    />
                  </b-dropdown-item>
                </b-dropdown>
              </div>
              <!--/ dropdown -->
            </div>
          </b-card>
        </b-col>
        <!--/ blogs -->

        <!-- blog comment -->
        <b-col
          :id="'blogComment-'+item.id"
          cols="12"
          class="mt-2"
          v-if="!item.content_type && item.comment"
        >
          <h6 class="section-label">
            Comment
          </h6>
          <b-card
            
          >
            <b-media no-body>
              <b-media-aside class="mr-75">
                <b-avatar
                  :src="item.avatar"
                  size="38"
                />
              </b-media-aside>
              <b-media-body>
                <h6 class="font-weight-bolder mb-25">
                  {{ item.created_user.name }}
                </h6>
                <b-card-text>{{ fullDate(item.created_at) }}</b-card-text>
                <b-card-text>
                  <div v-html="item.comment"></div>
                </b-card-text>
                <!-- <b-link>
                  <div class="d-inline-flex align-items-center">
                    <feather-icon
                      icon="CornerUpLeftIcon"
                      size="18"
                      class="mr-50"
                    />
                    <span>Reply</span>
                  </div>
                </b-link> -->
              </b-media-body>
            </b-media>
          </b-card>
        </b-col>
        <!--/ blog comment -->
      </b-row>
    </div>
    <div class="blog-detail-wrapper">
      <b-row>
        <!-- Leave a Blog Comment -->
        <b-col
          cols="12"
          class="mt-2"
        >
          <h6 class="section-label">
            Leave a Comment
          </h6>
          <b-card>
            <b-form>
              <b-row>
                <b-col cols="12">
                  <!-- Description -->
                  <b-form-group
                    label="Description"
                    label-for="task-description"
                  >
                    <quill-editor
                      id="quil-content"
                      v-model="comment"
                      :options="options"
                      class="border-bottom-0"
                    />
                    
                  </b-form-group>
                </b-col>
                <b-col cols="12">
                  <b-button
                    v-ripple.400="'rgba(255, 255, 255, 0.15)'"
                    variant="primary"
                    @click="addComment"
                  >
                    Post Comment
                  </b-button>
                </b-col>
              </b-row>
            </b-form>
          </b-card>
        </b-col>
        <!--/ Leave a Blog Comment -->
      </b-row>
      <!--/ blogs -->
    </div>
    <!--/ content -->

  </content-with-sidebar>
</template>

<script>
import {
  BFormInput,
  BMedia,
  BAvatar,
  BMediaAside,
  BMediaBody,
  BImg,
  BLink,
  BFormGroup,
  BInputGroup,
  BInputGroupAppend,
  BCard,
  BRow,
  BCol,
  BBadge,
  BCardText,
  BDropdown,
  BDropdownItem,
  BForm,
  BFormTextarea,
  BFormCheckbox,
  BButton,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { kFormatter, fullDate } from '@core/utils/filter'
import { quillEditor } from 'vue-quill-editor'
import ContentWithSidebar from '@core/layouts/components/content-with-sidebar/ContentWithSidebar.vue'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BFormInput,
    BMedia,
    BAvatar,
    BMediaAside,
    BMediaBody,
    BLink,
    BCard,
    BRow,
    BCol,
    BFormGroup,
    BInputGroup,
    BInputGroupAppend,
    BImg,
    BBadge,
    BCardText,
    BDropdown,
    BForm,
    BDropdownItem,
    BFormTextarea,
    BFormCheckbox,
    BButton,
    ContentWithSidebar,
    quillEditor,
  },
  directives: {
    Ripple,
  },
  data() {
    return {
      search_query: '',
      commentCheckmark: '',
      // blogDetail: [],
      blogSidebar: {},
      socialShareIcons: ['GithubIcon', 'GitlabIcon', 'FacebookIcon', 'TwitterIcon', 'LinkedinIcon'],
      options: {
        theme: 'snow',
        placeholder: 'Write your comment here',
      },
      comment: '',
    }
  },
  computed: {
    blogDetail() {
      return this.$store.state.app.contentData
    }
  },
  created() {
    let web = JSON.parse(localStorage.getItem('website'))
    let payload = {
      website: web.id,
      primary_topic: this.$route.params.id
    }
    this.$store.dispatch('app/loadContent', payload)
    // this.$http.get('/blog/list/data/sidebar').then(res => {
    //   this.blogSidebar = res.data
    // })
  },
  methods: {
    kFormatter,
    fullDate,
    tagsColor(tag) {
      if (tag === 'Quote') return 'light-info'
      if (tag === 'Gaming') return 'light-danger'
      if (tag === 'Fashion') return 'light-primary'
      if (tag === 'Video') return 'light-warning'
      if (tag === 'Food') return 'light-success'
      return 'light-primary'
    },
    back() {
      // this.$router.push('/topics')
      history.back()
    },
    addComment() {
      let web = JSON.parse(localStorage.getItem('website'))
      let payload = {
        website: web.id,
        primary_topic: this.$route.params.id,
        content_type: 'comment',
        comment: this.comment,
        action: 'add'
      }
      this.$store.dispatch('app/addEditComment', payload).then(res => {
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Success`,
                icon: 'UserCheckIcon',
                variant: 'success',
                text: res.data.message,
              },
            })
      }).catch(err => {
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Failed`,
                icon: 'UserCheckIcon',
                variant: 'danger',
                text: err.data.message,
              },
            })
      });
    }
  },
}
</script>

<style lang="scss">
@import '~@core/scss/vue/pages/page-blog.scss';
@import '~@core/scss/vue/libs/quill.scss';
</style>
<style lang="scss" scoped>
@import '~@core/scss/base/bootstrap-extended/include';

#quil-content ::v-deep {
  > .ql-container {
    // border-bottom: 0;
    min-height: 15rem;
  }

  + #quill-toolbar {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    border-bottom-left-radius: $border-radius;
    border-bottom-right-radius: $border-radius;
  }
}
</style>