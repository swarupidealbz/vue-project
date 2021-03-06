<template>
  <div>
    <b-sidebar
      id="sidebar-task-handler"
      sidebar-class="sidebar-xl"
      :visible="isTaskHandlerSidebarActive"
      bg-variant="white"
      shadow
      backdrop
      no-header
      right
      @change="(val) => $emit('update:is-task-handler-sidebar-active', val)"
      @hidden="clearForm"
    >
      <template #default="{ hide }">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
          
          <h5
            class="mb-0"
          >
            Create Topic
          </h5>
          <div>
            <!-- <feather-icon
              v-show="taskLocal.id"
              icon="TrashIcon"
              class="cursor-pointer"
              @click="$emit('remove-task'); hide();"
            /> -->
            <feather-icon
              class="ml-1 cursor-pointer"
              icon="XIcon"
              size="16"
              @click="hide"
            />
          </div>
        </div>

        <!-- Body -->
        <validation-observer
          #default="{ handleSubmit }"
          ref="refFormObserver"
        >

          <!-- Form -->
          <b-form
            class="p-2"
            @submit.prevent="handleSubmit(onSubmit)"
            @reset.prevent="resetForm"
          >

          <div class="demo-inline-spacing mb-1">
            <b-form-radio
              v-model="taskLocal.type"
              plain
              name="primary"
              value="1"
              :disabled="taskLocal.id && taskLocal.child_topics.length > 0"
            >
              Primary Topic
            </b-form-radio>
            <b-form-radio
              v-model="taskLocal.type"
              plain
              name="child topic"
              value="0"
              :disabled="taskLocal.id && taskLocal.child_topics.length > 0"
            >
              Child Topic
            </b-form-radio>
          </div>

           <validation-provider
              #default="validationTopicId"
              name="Primary topic name"
              rules="required"
              v-if="taskLocal.type == 0"
            >
              <b-form-group
                label="Primary topic name"
                label-for="primary-topic-name"
              >
                  <b-form-select
                    id="primary-topic-name"
                    name="primary-topic-name"
                    v-model="taskLocal.primary_topic_id"
                    :state="getValidationState(validationTopicId)"
                    :options="topicOptions"
                  />
              <b-form-invalid-feedback>
                  {{ validationTopicId.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>

            <!-- category / group -->
            <validation-provider
              #default="validationContext"
              name="Groups"
              rules="required"
            >
              <b-form-group
                label="Groups"
                label-for="group"
              >
                <v-select
                  v-model="taskLocal.groups"
                  multiple
                  taggable
                  push-tags
                  placeholder="Select Groups"
                  label="name"
                  :options="groupOption"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>

            <!-- Title -->
            <validation-provider
              #default="validationContext"
              name="Topic"
              rules="required"
            >
              <b-form-group
                label="Topic"
                label-for="topic"
              >
                <b-form-input
                  id="topic"
                  v-model="taskLocal.topic"
                  autofocus
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder="Topic"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>

            <!-- Description -->
            <b-form-group
              label="Description"
              label-for="task-description"
            >
              <quill-editor
                id="quil-content"
                v-model="taskLocal.description"
                :options="editorOption"
                class="border-bottom-0"
              />
              
            </b-form-group>

            <!-- Form Actions -->
            <div class="d-flex mt-2">
              <b-button
                v-ripple.400="'rgba(255, 255, 255, 0.15)'"
                variant="primary"
                class="mr-2"
                type="submit"
                :disabled="!taskLocal.topic || !taskLocal.description"
                @click="addTopic"
              >
                {{ taskLocal.id ? 'Update' : 'Create Topic' }}
              </b-button>
              <b-button
                v-ripple.400="'rgba(186, 191, 199, 0.15)'"
                type="reset"
                variant="outline-secondary"
                v-if="!taskLocal.id"
              >
                Reset
              </b-button>
            </div>
          </b-form>
        </validation-observer>
      </template>
    </b-sidebar>
  </div>
</template>

<script>
import { BSidebar, BForm, BFormGroup, BFormInput, BAvatar, BButton, BFormInvalidFeedback,BFormRadio, BFormSelect, BFormSelectOption } from 'bootstrap-vue'
import vSelect from 'vue-select'
import flatPickr from 'vue-flatpickr-component'
import Ripple from 'vue-ripple-directive'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, url } from '@validations'
import { avatarText } from '@core/utils/filter'
import formValidation from '@core/comp-functions/forms/form-validation'
import { toRefs } from '@vue/composition-api'
import { quillEditor } from 'vue-quill-editor'
import useTaskHandler from '../todo/useTaskHandler'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    // BSV
    BButton,
    BSidebar,
    BForm,
    BFormGroup,
    BFormInput,
    BAvatar,
    BFormRadio,
    BFormInvalidFeedback,
    BFormSelect,
    BFormSelectOption,

    // 3rd party packages
    vSelect,
    flatPickr,
    quillEditor,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
  },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isTaskHandlerSidebarActive',
    event: 'update:is-task-handler-sidebar-active',
  },
  props: {
    isTaskHandlerSidebarActive: {
      type: Boolean,
      required: true,
    },
    task: {
      type: Object,
      required: true,
    },
    clearTaskData: {
      type: Function,
      required: true,
    },
  },
  data() {
    return {
      required,
      email,
      url,
    }
  },
  computed: {
    topics() {
      return this.$store.state.app.topics;
    },
    topicOptions() {
      let result = [
        { value: '', text: 'Select Primary Topic'}
      ]
      Object.values(this.topics).map(topic => {
        result.push({
          value: topic.id,
          text: topic.topic
        })
      })

      return result;
    },
    groupOption() {
      return Object.values(this.$store.state.app.allGroups) || [];
    }
  },
  methods: {
    addTopic() {
      if((this.taskLocal.type == 0) && !this.taskLocal.primary_topic_id) {
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Failed`,
                icon: 'UserCheckIcon',
                variant: 'danger',
                text: `Please select a primary topic from list.`,
              },
            })
        return false
      }
      let payload = {
        website: this.$store.state.app.selectedWebsite.id,
        id: this.taskLocal.id || null,
        is_primary: this.taskLocal.type,
        topic_name: this.taskLocal.topic,
        description: this.taskLocal.description,
        primary_topic_id: this.taskLocal.type == '1' ? null : this.taskLocal.primary_topic_id,
        groups: this.taskLocal.groups,
      }
      this.$emit('update:topic-details', this.taskLocal);
      this.$store.dispatch('app/addOrUpdateTopic', payload).then((res) => {
        let payload = {
          website: this.$store.state.app.selectedWebsite.id,
          primary_topic_id: this.$store.state.app.selectedTopic.id && this.$store.state.app.showChild ? this.$store.state.app.selectedTopic.id : ''
        }
        if(this.$store.state.app.selectedOrder.id) {
          payload.order = this.$store.state.app.selectedOrder.id
        }
        this.$store.dispatch('app/sortRecord', payload);     
        this.$store.dispatch('app/loadGroups', {website: this.$store.state.app.selectedWebsite.id});   
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
        this.$emit('update:is-task-handler-sidebar-active', false);
      }).catch(error => {
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Failed`,
                icon: 'UserCheckIcon',
                variant: 'danger',
                text: error.data.data[0],
              },
            })
      })
    }
  },
  setup(props, { emit }) {
    const {
      taskLocal,
      resetTaskLocal,

      // UI
      assigneeOptions,
      onSubmit,
      tagOptions,
      resolveAvatarVariant,
    } = useTaskHandler(toRefs(props), emit)

    const { refFormObserver, getValidationState, resetForm, clearForm } = formValidation(
      resetTaskLocal,
      props.clearTaskData,
    )

    const editorOption = {
      // modules: {
      //   toolbar: '#quill-toolbar',
      // },
      theme: 'snow',
      placeholder: 'Write your description',
    }

    return {
      // Add New
      taskLocal,
      onSubmit,
      assigneeOptions,
      tagOptions,

      // Form Validation
      resetForm,
      clearForm,
      refFormObserver,
      getValidationState,

      // UI
      editorOption,
      resolveAvatarVariant,

      // Filter/Formatter
      avatarText,
    }
  },
}
</script>

<style lang="scss">
@import '~@core/scss/vue/libs/vue-select.scss';
@import '~@core/scss/vue/libs/vue-flatpicker.scss';
@import '~@core/scss/vue/libs/quill.scss';
</style>

<style lang="scss" scoped>
@import '~@core/scss/base/bootstrap-extended/include';

.assignee-selector {
  ::v-deep .vs__dropdown-toggle {
    padding-left: 0;
  }
}

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
