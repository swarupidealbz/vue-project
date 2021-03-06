<template>
  <b-card>

    <!-- media -->
    <b-media no-body>
      <b-media-aside>
        <b-link>
          <b-img
            ref="previewEl"
            rounded
            :src="local.profile_image || '/images/account.png'"
            height="80"
          />
        </b-link>
        <!--/ avatar -->
      </b-media-aside>

      <b-media-body class="mt-75 ml-75">
        <!-- upload button -->
        <b-button
          v-ripple.400="'rgba(255, 255, 255, 0.15)'"
          variant="primary"
          size="sm"
          class="mb-75 mr-75"
          @click="$refs.refInputEl.$el.click()"
        >
          Upload
        </b-button>
        <b-form-file
          ref="refInputEl"
          v-model="profileFile"
          accept=".jpg, .png, .gif"
          :hidden="true"
          plain
          @input="uploadProfileImage"
        />
        <!--/ upload button -->

        <!-- reset -->
        <b-button
          v-ripple.400="'rgba(186, 191, 199, 0.15)'"
          variant="outline-secondary"
          size="sm"
          class="mb-75 mr-75"
          @click="unsetImage"
        >
          Reset
        </b-button>
        <!--/ reset -->
        <b-card-text>Allowed JPG, GIF or PNG. Max size of 800kB</b-card-text>
      </b-media-body>
    </b-media>
    <!--/ media -->

    <!-- form -->
    <b-form class="mt-2">
      <b-row>
        <b-col sm="6">
          <b-form-group
            label="First Name"
            label-for="account-first_name"
          >
            <b-form-input
              v-model="local.first_name"
              placeholder="First Name"
              name="first_name"
            />
          </b-form-group>
        </b-col>
        <b-col sm="6">
          <b-form-group
            label="Last Name"
            label-for="account-last_name"
          >
            <b-form-input
              v-model="local.last_name"
              name="first_name"
              placeholder="Last Name"
            />
          </b-form-group>
        </b-col>
        <b-col sm="6">
          <b-form-group
            label="E-mail"
            label-for="account-e-mail"
          >
            <b-form-input
              v-model="local.email"
              name="email"
              disabled
              placeholder="Email"
            />

          </b-form-group>
        </b-col>
        <b-col sm="6">
          <b-form-group
            label="Company"
            label-for="account-company"
          >
            <b-form-input
              v-model="local.company"
              name="company"
              placeholder="Company name"
            />
          </b-form-group>
        </b-col>

        <!-- alert -->
        <b-col
          cols="12"
          class="mt-75"
        >
          <b-alert
            show
            variant="warning"
            class="mb-50"
            v-if="isAdmin"
          >
            <h4 class="alert-heading">
              Your email is not confirmed. Please check your inbox.
            </h4>
            <div class="alert-body">
              <b-link class="alert-link">
                Resend confirmation
              </b-link>
            </div>
          </b-alert>
        </b-col>
        <!--/ alert -->

        <b-col cols="12">
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="primary"
            class="mt-2 mr-1"
            @click="updateProfile"
          >
            Save changes
          </b-button>
          <b-button
            v-ripple.400="'rgba(186, 191, 199, 0.15)'"
            variant="outline-secondary"
            type="reset"
            class="mt-2"
            @click.prevent="resetForm"
          >
            Reset
          </b-button>
        </b-col>
      </b-row>
    </b-form>
  </b-card>
</template>

<script>
import {
  BFormFile, BButton, BForm, BFormGroup, BFormInput, BRow, BCol, BAlert, BCard, BCardText, BMedia, BMediaAside, BMediaBody, BLink, BImg,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { useInputImageRenderer } from '@core/comp-functions/forms/form-utils'
import { ref } from '@vue/composition-api'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BButton,
    BForm,
    BImg,
    BFormFile,
    BFormGroup,
    BFormInput,
    BRow,
    BCol,
    BAlert,
    BCard,
    BCardText,
    BMedia,
    BMediaAside,
    BMediaBody,
    BLink,
  },
  directives: {
    Ripple,
  },
  props: {
    generalData: {
      type: Object,
      default: () => {},
    },
    isAdmin: {
      type: Boolean,
      default: true
    },
    userData: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      optionsLocal: JSON.parse(JSON.stringify(this.generalData)),
      local: JSON.parse(JSON.stringify(this.userData)),
      profileFile: null,
      image: 'set',
    }
  },
  methods: {
    resetForm() {
      this.optionsLocal = JSON.parse(JSON.stringify(this.generalData))
      this.local = JSON.parse(JSON.stringify(this.userData))
    },
    unsetImage() {
      this.image = 'unset';
      this.profileFile = null;
      this.upload();
    },
    uploadProfileImage() {      
      if(this.profileFile == null) {
        return false;
      }
      this.image ='set';
      this.upload();
    },
    upload() {
      var formData = new FormData();
      formData.append("profile_image", this.profileFile);
      formData.append("action", this.image);
      this.$store.dispatch('app/updateProfileImage',formData).then(res => {
        this.local = res.data;
        let user = JSON.parse(localStorage.getItem('userData'))
        user.profile_image = this.local.profile_image;
        localStorage.setItem('userData', JSON.stringify(user));
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Updated`,
                icon: 'UserCheckIcon',
                variant: 'success',
                text: res.message,
              },
            })
        // this call is to reflect user icon in topic assign pic
        this.$store.dispatch('app/sortRecord', { website: this.$store.state.app.selectedWebsite.id });
      }).catch(err => {
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
      })
    },
    updateProfile() {
      this.$store.dispatch('app/updateProfile', this.local).then(res => {
        this.local = res.data;
        let user = JSON.parse(localStorage.getItem('userData'))
        user.company = this.local.company;
        user.first_name = this.local.first_name;
        user.last_name = this.local.last_name;
        user.email = this.local.email;
        localStorage.setItem('userData', JSON.stringify(user));
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Updated`,
                icon: 'UserCheckIcon',
                variant: 'success',
                text: res.message,
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
                text: err.message,
              },
            })
      })
    }
  },
  setup() {
    const refInputEl = ref(null)
    const previewEl = ref(null)

    const { inputImageRenderer } = useInputImageRenderer(refInputEl, previewEl)

    return {
      refInputEl,
      previewEl,
      inputImageRenderer,
    }
  },
}
</script>
