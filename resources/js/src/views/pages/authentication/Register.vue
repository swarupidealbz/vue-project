<template>
  <div class="auth-wrapper auth-v2">
    <b-row class="auth-inner m-0">

      <!-- Brand logo-->
      <b-link class="brand-logo">
        <vuexy-logo />

        <h2 class="brand-text text-primary ml-1">
          {{ appName }}
        </h2>
      </b-link>
      <!-- /Brand logo-->

      <!-- Left Text-->
      <b-col
        lg="8"
        class="d-none d-lg-flex align-items-center p-5"
      >
        <div class="w-100 d-lg-flex align-items-center justify-content-center px-5">
          <b-img
            fluid
            :src="imgUrl"
            alt="Register V2"
          />
        </div>
      </b-col>
      <!-- /Left Text-->

      <!-- Register-->
      <b-col
        lg="4"
        class="d-flex align-items-center auth-bg px-2 p-lg-5"
      >
        <b-col
          sm="8"
          md="6"
          lg="12"
          class="px-xl-2 mx-auto"
        >
          <b-card-title class="mb-1">
            Welcome to {{ appName }} 🚀
          </b-card-title>
          <b-card-text class="mb-2">
            Make your content creation easy and fun!
          </b-card-text>

          <!-- form -->
          <validation-observer
            ref="registerForm"
            #default="{invalid}"
          >
            <b-form
              class="auth-register-form mt-2"
              @submit.prevent="register"
            >

              <!-- firstname -->
              <b-form-group
                label="First Name"
                label-for="register-firstname"
              >
                <validation-provider
                  #default="{ errors }"
                  name="First Name"
                  vid="firstname"
                  rules="required"
                >
                  <b-form-input
                    id="register-firstname"
                    v-model="firstname"
                    name="register-firstname"
                    :state="errors.length > 0 ? false:null"
                    placeholder="john"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- lastname -->
              <b-form-group
                label="Last Name"
                label-for="register-lastname"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Last Name"
                  vid="lastname"
                  rules="required"
                >
                  <b-form-input
                    id="register-lastname"
                    v-model="lastname"
                    name="register-lastname"
                    :state="errors.length > 0 ? false:null"
                    placeholder="doe"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>


              <!-- email -->
              <b-form-group
                label="Email"
                label-for="register-email"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Email"
                  vid="email"
                  rules="required|email"
                >
                  <b-form-input
                    id="register-email"
                    v-model="userEmail"
                    name="register-email"
                    :state="errors.length > 0 ? false:null"
                    placeholder="john@example.com"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- password -->
              <b-form-group
                label-for="register-password"
                label="Password"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Password"
                  vid="password"
                  rules="required"
                >
                  <b-input-group
                    class="input-group-merge"
                    :class="errors.length > 0 ? 'is-invalid':null"
                  >
                    <b-form-input
                      id="register-password"
                      v-model="password"
                      class="form-control-merge"
                      :type="passwordFieldType"
                      :state="errors.length > 0 ? false:null"
                      name="register-password"
                      placeholder="············"
                    />
                    <b-input-group-append is-text>
                      <feather-icon
                        :icon="passwordToggleIcon"
                        class="cursor-pointer"
                        @click="togglePasswordVisibility"
                      />
                    </b-input-group-append>
                  </b-input-group>
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- confirm password -->
              <b-form-group
                label-for="register-confirm-password"
                label="Confirm Password"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Confirm Password"
                  vid="confirm-password"
                  rules="required|confirmed:password"
                >
                  <b-input-group
                    class="input-group-merge"
                    :class="errors.length > 0 ? 'is-invalid':null"
                  >
                    <b-form-input
                      id="register-confirm-password"
                      v-model="confirm_password"
                      class="form-control-merge"
                      :type="confirmPasswordFieldType"
                      :state="errors.length > 0 ? false:null"
                      name="register-confirm-password"
                      placeholder="············"
                    />
                    <b-input-group-append is-text>
                      <feather-icon
                        :icon="confirmPasswordToggleIcon"
                        class="cursor-pointer"
                        @click="toggleConfirmPasswordVisibility"
                      />
                    </b-input-group-append>
                  </b-input-group>
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- mobile -->
              <b-form-group
                label="Mobile"
                label-for="register-mobile"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Mobile"
                  vid="mobile"
                  rules="required"
                >
                  <b-input-group class="input-group-merge">
                    <b-input-group-prepend>
                      <vue-country-code
                        @onSelect="onSelect">
                      </vue-country-code>
                    </b-input-group-prepend>
                    <b-form-input
                      id="register-mobile"
                      v-model="mobile"
                      name="register-mobile"
                      :state="errors.length > 0 ? false:null"
                      placeholder="Ex:9000000000"
                    />
                    <small class="text-danger">{{ errors[0] }}</small>
                  </b-input-group>
                </validation-provider>
              </b-form-group>

              <!-- register as -->
              <b-form-group
                label="Register As a"
                label-for="register-role"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Register As"
                  vid="role"
                  rules="required"
                >
                  <b-form-select
                    id="register-role"
                    name="register-role"
                    v-model="role"
                    :options="options"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- <b-form-group>
                <b-form-checkbox
                  id="register-privacy-policy"
                  v-model="status"
                  name="checkbox-1"
                >
                  I agree to
                  <b-link>privacy policy & terms</b-link>
                </b-form-checkbox>
              </b-form-group> -->

              <b-button
                variant="primary"
                block
                type="submit"
                :disabled="invalid"
              >
                Sign up
              </b-button>
            </b-form>
          </validation-observer>

          <p class="text-center mt-2">
            <span>Already have an account?</span>
            <b-link :to="{name:'auth-login'}">
              <span>&nbsp;Sign in instead</span>
            </b-link>
          </p>

          <!-- divider -->
          <!-- <div class="divider my-2">
            <div class="divider-text">
              or
            </div>
          </div> -->

          <!-- <div class="auth-footer-btn d-flex justify-content-center">
            <b-button
              variant="facebook"
              href="javascript:void(0)"
            >
              <feather-icon icon="FacebookIcon" />
            </b-button>
            <b-button
              variant="twitter"
              href="javascript:void(0)"
            >
              <feather-icon icon="TwitterIcon" />
            </b-button>
            <b-button
              variant="google"
              href="javascript:void(0)"
            >
              <feather-icon icon="MailIcon" />
            </b-button>
            <b-button
              variant="github"
              href="javascript:void(0)"
            >
              <feather-icon icon="GithubIcon" />
            </b-button>
          </div> -->
        </b-col>
      </b-col>
    <!-- /Register-->
    </b-row>
  </div>
</template>

<script>
/* eslint-disable global-require */
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import VuexyLogo from '@core/layouts/components/Logo.vue'
import { $themeColors, $themeBreakpoints, $themeConfig } from "@themeConfig";
import {
  BRow,
  BCol,
  BLink,
  BButton,
  BForm,
  BFormCheckbox,
  BFormGroup,
  BFormInput,
  BInputGroup,
  BInputGroupAppend,
  BImg,
  BCardTitle,
  BCardText,
  BFormSelect,
  BInputGroupPrepend,
} from 'bootstrap-vue'
import { required, email } from '@validations'
import { togglePasswordVisibility } from '@core/mixins/ui/forms'
import store from '@/store/index'
import useJwt from '@/auth/jwt/useJwt'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    VuexyLogo,
    BRow,
    BImg,
    BCol,
    BLink,
    BButton,
    BForm,
    BCardText,
    BCardTitle,
    BFormCheckbox,
    BFormGroup,
    BFormInput,
    BInputGroup,
    BInputGroupAppend,
    BInputGroupPrepend,
    BFormSelect,
    // validations
    ValidationProvider,
    ValidationObserver,
  },
  mixins: [togglePasswordVisibility],
  data() {
    return {
      status: '',
      firstname: '',
      lastname: '',
      username: '',
      userEmail: '',
      password: '',
      confirm_password: '',
      mobile: '',
      role: '',
      options: [
        { value: '', text: 'select' },
        { value: 'client', text: 'Client' },
        { value: 'writer', text: 'Writer' }
      ],
      sideImg: require('@/assets/images/pages/register-v2.svg'),
      // validation
      required,
      email,
    }
  },
  computed: {
    passwordToggleIcon() {
      return this.passwordFieldType === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
    confirmPasswordToggleIcon() {
      return this.confirmPasswordFieldType === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
    imgUrl() {
      if (store.state.appConfig.layout.skin === 'dark') {
        // eslint-disable-next-line vue/no-side-effects-in-computed-properties
        this.sideImg = require('@/assets/images/pages/register-v2-dark.svg')
        return this.sideImg
      }
      return this.sideImg
    },    
    appName() {
      return $themeConfig.app.appName;
    }
  },
  methods: {
    onSelect({name, iso2, dialCode}) {
       console.log(name, iso2, dialCode);
     },
    register() {
      this.$refs.registerForm.validate().then(success => {
        if (success) {
          // useJwt
          //   .register({
          //     username: this.username,
          //     email: this.userEmail,
          //     password: this.password,
          //   })
          //   .then(response => {
          //     useJwt.setToken(response.data.accessToken)
          //     useJwt.setRefreshToken(response.data.refreshToken)
          //     localStorage.setItem('userData', JSON.stringify(response.data.userData))
          //     this.$ability.update(response.data.userData.ability)
          //     this.$router.push('/')
          //   })
          //   .catch(error => {
          //     this.$refs.registerForm.setErrors(error.response.data.error)
          //   })
          this.$http.post(this.$store.state.app.apiBaseUrl+'register', 
          {
            first_name: this.firstname,
            last_name: this.lastname,
            email: this.userEmail,
            username: this.userEmail,
            password: this.password,
            confirm_password: this.confirm_password,
            mobile: this.mobile,
            role: this.role
          }).then(response => {
            this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Welcome ${this.firstname} ${this.lastname}`,
                icon: 'CoffeeIcon',
                variant: 'success',
                text: response.data.message,
              },
            })
            this.$router.push('/')
          }).catch(error => {
            this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Error`,
                icon: 'UserXIcon',
                variant: 'danger',
                text: error.response.data.message,
              },
            })
            this.$refs.registerForm.setErrors(error.response.data)
          })
        }
      })
    },
  },
}
/* eslint-disable global-require */
</script>

<style lang="scss">
@import '~@core/scss/vue/pages/page-auth.scss';
</style>
