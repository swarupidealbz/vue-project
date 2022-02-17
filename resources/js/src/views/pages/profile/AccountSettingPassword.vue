<template>
  <b-card>
    <!-- form -->
    <b-form>
      <b-row>
        <!-- old password -->
        <b-col md="6">
          <b-form-group
            label="Old Password"
            label-for="account-old-password"
          >
            <b-input-group class="input-group-merge">
              <validation-provider
                #default="{ errors }"
                name="Old Password"
                rules="required"
              >
                <b-form-input
                  id="account-old-password"
                  v-model="passwordValueOld"
                  name="old-password"
                  :type="passwordFieldTypeOld"
                  placeholder="Old Password"
                />
                <b-input-group-append is-text>
                  <feather-icon
                    :icon="passwordToggleIconOld"
                    class="cursor-pointer"
                    @click="togglePasswordOld"
                  />
                </b-input-group-append>
                <small class="text-danger">{{ errors[0] }}</small>
              </validation-provider>
            </b-input-group>
          </b-form-group>
        </b-col>
        <!--/ old password -->
      </b-row>
      <b-row>
        <!-- new password -->
        <b-col md="6">
          <b-form-group
            label-for="account-new-password"
            label="New Password"
          >
            <b-input-group class="input-group-merge">
              <validation-provider
                #default="{ errors }"
                name="New Password"
                vid="account-new-password"
                rules="required|password"
              >
                <b-form-input
                  id="account-new-password"
                  v-model="newPasswordValue"
                  :type="passwordFieldTypeNew"
                  name="new-password"
                  placeholder="New Password"
                />
                <b-input-group-append is-text>
                  <feather-icon
                    :icon="passwordToggleIconNew"
                    class="cursor-pointer"
                    @click="togglePasswordNew"
                  />
                </b-input-group-append>
                <small class="text-danger">{{ errors[0] }}</small>
              </validation-provider>
            </b-input-group>
          </b-form-group>
        </b-col>
        <!--/ new password -->

        <!-- retype password -->
        <b-col md="6">
          <b-form-group
            label-for="account-retype-new-password"
            label="Retype New Password"
          >
            <b-input-group class="input-group-merge">
              <validation-provider
                #default="{ errors }"
                name="Retype Password"
                rules="required|confirmed:account-new-password"
              >
                <b-form-input
                  id="account-retype-new-password"
                  v-model="RetypePassword"
                  :type="passwordFieldTypeRetype"
                  name="retype-password"
                  placeholder="New Password"
                />
                <b-input-group-append is-text>
                  <feather-icon
                    :icon="passwordToggleIconRetype"
                    class="cursor-pointer"
                    @click="togglePasswordRetype"
                  />
                </b-input-group-append>
                <small class="text-danger">{{ errors[0] }}</small>
              </validation-provider>
            </b-input-group>
          </b-form-group>
        </b-col>
        <!--/ retype password -->

        <!-- buttons -->
        <b-col cols="12">
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="primary"
            class="mt-1 mr-1"
            @click="updatePassword"
          >
            Save changes
          </b-button>
          <b-button
            v-ripple.400="'rgba(186, 191, 199, 0.15)'"
            type="reset"
            variant="outline-secondary"
            class="mt-1"
          >
            Reset
          </b-button>
        </b-col>
        <!--/ buttons -->
      </b-row>
    </b-form>
  </b-card>
</template>

<script>
import {
  BButton, BForm, BFormGroup, BFormInput, BRow, BCol, BCard, BInputGroup, BInputGroupAppend,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BButton,
    BForm,
    BFormGroup,
    BFormInput,
    BRow,
    BCol,
    BCard,
    BInputGroup,
    BInputGroupAppend,
  },
  directives: {
    Ripple,
  },
  props: {
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
      passwordValueOld: '',
      newPasswordValue: '',
      RetypePassword: '',
      passwordFieldTypeOld: 'password',
      passwordFieldTypeNew: 'password',
      passwordFieldTypeRetype: 'password',
    }
  },
  computed: {
    passwordToggleIconOld() {
      return this.passwordFieldTypeOld === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
    passwordToggleIconNew() {
      return this.passwordFieldTypeNew === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
    passwordToggleIconRetype() {
      return this.passwordFieldTypeRetype === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
  },
  methods: {
    togglePasswordOld() {
      this.passwordFieldTypeOld = this.passwordFieldTypeOld === 'password' ? 'text' : 'password'
    },
    togglePasswordNew() {
      this.passwordFieldTypeNew = this.passwordFieldTypeNew === 'password' ? 'text' : 'password'
    },
    togglePasswordRetype() {
      this.passwordFieldTypeRetype = this.passwordFieldTypeRetype === 'password' ? 'text' : 'password'
    },
    updatePassword() {
      let payload = {
        email: this.userData.email,
        password: this.passwordValueOld,
        new_password: this.newPasswordValue,
        confirm_password: this.RetypePassword
      }
      this.$store.dispatch('app/changePassword', payload).then(res => {
        this.$toast({
              component: ToastificationContent,
              position: 'top-right',
              props: {
                title: `Success`,
                icon: 'UserXIcon',
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
                icon: 'UserXIcon',
                variant: 'danger',
                text: err.data.message,
              },
            })
      })
      this.passwordValueOld = '';
      this.newPasswordValue = '';
      this.RetypePassword = '';
    }
  },
}
</script>
