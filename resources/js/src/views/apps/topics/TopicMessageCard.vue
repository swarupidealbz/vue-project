<template>
  <b-card no-body>
    <b-card-header
      v-if="message.topic"
      class="email-detail-head"
    >
      <div class="user-details d-flex justify-content-between align-items-center flex-wrap">
        <!-- <b-avatar
          size="48"
          :src="message.from.avatar"
          class="mr-75"
        /> -->
        <div class="mail-items">
          <h5 class="mb-0">
            {{ message.topic }}
          </h5>
        </div>
      </div>
      <div class="mail-meta-item d-flex align-items-center">
        <small class="mail-date-time text-muted">{{ formatDate(message.created_at) }}</small>
        <!-- Mail Action DD -->
        <b-dropdown
          variant="link"
          no-caret
          toggle-class="p-0"
          right
        >
          <template #button-content>
            <feather-icon
              icon="MoreVerticalIcon"
              size="17"
              class="ml-50 text-body"
            />
          </template>

          <b-dropdown-item
          @click="$emit('edit-topic',message)"
          v-if="!isWriter && message.is_editable"
          >
            <feather-icon icon="EditIcon" />
            <span class="align-middle ml-50">Edit</span>
          </b-dropdown-item>

          <b-dropdown-item
          @click="showChild"
          v-if="!isWriter && isPrimary"
          >
            <feather-icon icon="GridIcon" />
            <span class="align-middle ml-50">Show Child Topics</span>
          </b-dropdown-item>

          <b-dropdown-item
          :to="{ name: 'topic-timeline', params: { id: message.id } }">
            <feather-icon icon="FileIcon" />
            <span class="align-middle ml-50">Show Content</span>
          </b-dropdown-item>

          <b-dropdown-item 
          variant="success" 
          @click="$emit('accept-status')"
          v-if="!isWriter"
          >
            <feather-icon icon="CheckCircleIcon" />
            <span class="align-middle ml-50">Accept</span>
          </b-dropdown-item>

          <b-dropdown-item 
          variant="danger"
          @click="$emit('reject-status')"
          v-if="!isWriter"
          >
            <feather-icon icon="XCircleIcon" />
            <span class="align-middle ml-50 text-danger">Reject</span>
          </b-dropdown-item>
                    
        </b-dropdown>
      </div>
    </b-card-header>

    <b-card-body class="mail-message-wrapper topic-details pt-2">
      <!-- eslint-disable vue/no-v-html -->
      <div
        class="mail-message"
        v-html="message.description"
      />
      <!-- eslint-enable -->
      <b-img v-if="message.topic_image_path"
        :src="message.topic_image_path"
        width="250px"
        class="mr-50"
      />
    </b-card-body>

    <!-- <b-card-footer v-if="message.attachments && message.attachments.length">
      <div class="mail-attachments">
        <div class="d-flex align-items-center mb-1">
          <feather-icon
            icon="PaperclipIcon"
            size="16"
          />
          <h5 class="font-weight-bolder text-body mb-0 ml-50">
            {{ message.attachments.length }} Attachment{{ message.attachments.length > 1 ? 's' : '' }}
          </h5>
        </div>

        <div class="d-flex flex-column">
          <b-link
            v-for="(attachment, index) in message.attachments"
            :key="index"
            :href="attachment.url"
            target="_blank"
            :class="{'mt-75': index}"
          >
            <b-img
              :src="attachment.thumbnail"
              width="16px"
              class="mr-50"
            />
            <span class="text-muted font-weight-bolder align-text-top">{{ attachment.fileName }}</span>
            <span class="text-muted font-small-2 ml-25">({{ attachment.size }})</span>
          </b-link>
        </div>
      </div>
    </b-card-footer> -->
  </b-card>
</template>

<script>
import {
  BDropdown, BDropdownItem, BCard, BCardHeader, BCardBody, BCardFooter, BAvatar, BLink, BImg,
} from 'bootstrap-vue'
import { formatDate } from '@core/utils/filter'

export default {
  components: {
    BDropdown, BDropdownItem, BCard, BCardHeader, BCardBody, BCardFooter, BAvatar, BLink, BImg,
  },
  props: {
    message: {
      type: Object,
      required: true,
    },
  },
  computed: {
    isWriter() {
      let user = JSON.parse(localStorage.getItem('userData'))
      return user.role == 'writer';
    },
    user() {
      return JSON.parse(localStorage.getItem('userData'));
    },
    isPrimary() {
      return !this.$store.state.app.showChild;
    }
  },
  methods: {
    showChild() {
      let topic = this.$store.state.app.selectedTopic
      // this.$store.commit('app/setTopics', []);
      this.$store.commit('app/setGroups', []);
      this.$store.commit('app/setTopicCount', 0);
      this.$store.commit('app/setShowChild', true);
      localStorage.setItem('selectedTopic', JSON.stringify(topic));
      this.$store.dispatch('app/loadChildTopics', {
        website: this.$store.state.app.selectedWebsite.id, 
        primary_topic_id:topic.id 
      }).then(res => {
        this.$router.push('child-topics');
      })
    },
  },
  setup() {
    return {
      formatDate,
    }
  },
}
</script>

<style>
.topic-details img {
  object-fit: contain;
  width: 100%;
}
</style>
