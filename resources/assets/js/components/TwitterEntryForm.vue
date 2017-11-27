<style scoped>
  .action-link {
    cursor: pointer;
  }
</style>

<template>
  <div>
    <div v-bind:class="{ container: !isContained }">
      <div class="panel panel-default">
        <div class="panel-heading">
          <span>Check Twitter user</span>
        </div>
        <div class="panel-body">
          <div class="alert alert-danger" v-if="form.errors.length > 0">
            <p><strong>Whoops!</strong> Something went wrong!</p>
            <br>
            <ul>
              <li v-for="(error, index) in form.errors" v-bind:key="index">
                {{ error }}
              </li>
            </ul>
          </div>

          <form class="form" role="form" @submit.prevent="checkUser">
            <div class="form-group">
              <label class="control-label">Twitter User</label>
              <input id="twitter-user-name" class="form-control" name="user" v-model="form.user">
            </div>
            <div class="form-group">
              <button type="button" class="btn btn-primary form-control" @click="checkUser">Check user</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Twitter verification modal -->
    <div class="modal fade" id="modal-twitter-verification" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Twitter Verification</h4>
          </div>

          <div class="modal-body">
            <div class="alert alert-danger" v-if="pinForm.errors.length > 0">
              <p><strong>Whoops!</strong> Something went wrong!</p>
              <br>
              <ul>
                <li v-for="(error, index) in pinForm.errors" v-bind:key="index">
                  {{ error }}
                </li>
              </ul>
            </div>

            <p>
              We have no recent records for the Twitter user.
            </p>

            <p>
              In order to process the information we'll need to validate that you
              are a valid Twitter user and perform the requests on behalf of you.
              This is due to constraints placed on the number of tweets an application
              is allowed to retrieve on its own.
            </p>

            <p>
              In order to authenticate the app to do this, please visit
              <a v-bind:href="twitterLink" target="_blank">Twitter Auth</a>
              and once you have the PIN, enter it below to authorise the use.
            </p>

            <p>
              In addition we also need your email-address in order to let you
              know when processing has been completed and we have a response
              ready for you.
            </p>

            <form class="form" role="form" @submit.prevent="verifyPin">
              <div class="form-group">
                <label class="control-label">Twitter PIN</label>
                <input id="twitter-pin" class="form-control" name="pin" v-model="pinForm.pin" required>
              </div>

              <div class="form-group">
                <label class="control-label">Your email</label>
                <input id="user-email" class="form-control" name="email" v-model="pinForm.email" required>
              </div>
            </form>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" @click="verifyPin">Verify</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        isContained: false,
        twitterLink: "",

        form: {
          user: "",
          errors: []
        },

        pinForm: {
          pin: "",
          email: "",
          errors: []
        }
      };
    },

    mounted() {},

    methods: {
      checkUser() {
        axios.post('/twitter/vue/check', {
          twitter_user: this.form.user
        })
        .then(response => {
          if (response.data.hasRecent) {
            console.log('This would redirect to: ' + response.data.redirectTo);
          } else {
            this.twitterLink = response.data.twitterLink;
            $('#modal-twitter-verification').modal('show');
          }
        })
        .catch(error => {
          if (error.response) {
            this.unpackErrorList(error.response.data.errors, this.form);

            console.log(error.response);
          } else {
            this.form.errors = ['Request failed with an undefined error!'];
            console.log(error);
          }
        });
      },

      verifyPin() {
        axios.post('/twitter/vue/check_pin', {
          'pin_number': this.pinForm.pin,
          'email': this.pinForm.email
        })
        .then(response => {
          //
        })
        .catch(error => {
          if (error.response) {
            this.unpackErrorList(error.response.data.errors, this.form);

            console.log(error.response);
          } else {
            this.form.errors = ['Request failed with an undefined error!'];
            console.log(error);
          }
        })
      },

      unpackErrorList(errors, elem) {
        elem.errors = [];

        if (typeof errors === "object") {
          for (var key in errors) {
            if (errors.hasOwnProperty(key)) {
              var elementErrors = errors[key];

              if (!Array.isArray(elementErrors)) {
                elem.errors.push(elementErrors);
              } else {
                for (var i = 0; i < elementErrors.length; i++) {
                  elem.errors.push(elementErrors[i]);
                }
              }
            }
          }
        } else if (Array.isArray(errors)) {
          elem.errors = errors;
        } else {
          elem.errors = [errors];
        }
      }
    }
  }
</script>
