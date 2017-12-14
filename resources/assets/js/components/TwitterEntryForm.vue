<style scoped>
  .action-link {
    cursor: pointer;
  }
</style>

<template>
  <div>
    <div v-bind:class="{ container: !isContained }">
      <div class="panel panel-default">
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
              <input id="twitter-user-name" class="form-control" name="user" v-model="form.user" :disabled="formIsWorking">
            </div>
            <div class="form-group text-center">
              <button type="button" class="btn btn-primary" @click="checkUser" :disabled="form.isProcessing" v-if="!form.isRedirecting">
                Check user
              </button>
              <button type="button" class="btn btn-info" @click="showInformation" :disabled="form.isProcessing" v-if="!form.isRedirecting">
                Information
              </button>
              <h2 v-if="form.isRedirecting">
                Redirecting to result. Please wait...
              </h2>
            </div>
            <vue-simple-spinner message="Please wait..." v-if="form.isProcessing"></vue-simple-spinner>
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
              In order to authenticate the app to do this, please use the Twitter Auth
              button below, which will open the Twitter authentication page in a new tab.
              Once you authorise the use of your account for the query, you will receive
              a PIN. Enter it into the appropriate text field below to verify authorisation.
            </p>

            <p>
              In addition we also need your email-address in order to let you
              know when processing has been completed and we have a response
              ready for you.
            </p>

            <form class="form" role="form" @submit.prevent="verifyPin">
              <div class="form-group">
                <label class="control-label">Twitter PIN</label>
                <input id="twitter-pin" class="form-control" name="pin" v-model="pinForm.pin" :disabled="pinFormIsWorking" required>
              </div>

              <div class="form-group">
                <label class="control-label">Your email</label>
                <input id="user-email" class="form-control" name="email" v-model="pinForm.email" :disabled="pinFormIsWorking" required>
              </div>
            </form>
          </div>

          <div class="modal-footer">
            <a :href="twitterLink" target="_blank" class="btn btn-success pull-left" :disabled="pinFormIsWorking">Twitter Auth</a>
            <button type="button" class="btn btn-default" data-dismiss="modal" :disabled="pinFormIsWorking">Close</button>
            <button type="button" class="btn btn-primary" @click="verifyPin" :disabled="pinFormIsWorking">Verify</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Information modal -->
    <div class="modal fade" id="modal-information" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">About Enraged Windigo</h4>
          </div>

          <div class="modal-body">
            <p>
              <strong>What does it do?</strong><br>
              Enraged Windigo is a tool for determining the political affiliation of the people
              you follow on twitter. This works by analyzing tweets by you, and the people you
              follow, and using algorithms to determine their political affiliation.
            </p>

            <p>
              <strong>Why was it created?</strong><br>
              Looking objectively at politics has become increasingly difficult in recent years,
              as more and more media is simply brought in front of us to consume, without us taking
              a critical look on who is presenting it to you, and what motivations they could have
              to do as. As such, we aim to give you an insight into the political affiliation of the
              people you follow on twitter, as this could help you to identify if you are only
              recieving one-sided coverage.
            </p>

            <p>
              <strong>Who are the developers?</strong><br>
              The developers are a set of students at Aalborg University in Denmark. The developers
              are all male, but have a wide variety in political beliefs, and affiliations.
            </p>

            <p>
              <strong>What info do we require?</strong><br>
              In order to determine the political affiliations of your peers, we need some data to
              work with. As such, we will download up to 3200 tweets from you and each of the people
              you follow. In these tweets we will look at the sentiment (happy/angry), certain
              keywords (politicians/hashtags/legislature), and what news media people share.
            </p>

            <p>
              <strong>Why do you need to authenticate?</strong><br>
              In order to prevent misuse, Twitter has a system where each user has a certain amount
              of data they can recieve from Twitter per 15 minute interval. As our application only
              counts as a single user, we need to retrieve data on your behalf. And in order to do
              this, we need your permission, which we can get through you authorizing. Don't worry,
              the retrieval of data wont have any effect on your ability to enjoy twitter in the
              meantime.
            </p>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
  export default {
    props: {
      isContained: {
        default: false,
        type: Boolean
      }
    },
    data() {
      return {
        twitterLink: "",

        form: {
          isProcessing: false,
          isRedirecting: false,
          user: "",
          errors: []
        },

        pinForm: {
          isProcessing: false,
          isRedirecting: false,
          user: "",
          pin: "",
          email: "",
          errors: []
        }
      };
    },

    computed: {
      formIsWorking: function() {
        return this.form.isProcessing || this.form.isRedirecting;
      },

      pinFormIsWorking: function() {
        return this.pinForm.isProcessing || this.pinForm.isRedirecting;
      }
    },

    mounted() {},

    methods: {
      checkUser() {
        this.form.errors = [];
        this.form.isProcessing = true;

        axios.post('/twitter/vue/check', {
          twitter_user: this.form.user
        })
        .then(response => {
          this.form.isProcessing = false;

          if (response.data.hasRecent) {
            this.form.isRedirecting = true;
            window.location.href = response.data.redirectTo;
          } else {
            this.twitterLink = response.data.twitterLink;
            $('#modal-twitter-verification').modal('show');
          }
        })
        .catch(error => {
          this.form.isProcessing = false;

          if (error.response) {
            this.unpackErrorList(error.response.data, this.form);
          } else {
            this.form.errors = ['Request failed with an undefined error!'];
          }
        });
      },

      verifyPin() {
        this.pinForm.errors = [];
        this.pinForm.isProcessing = true;

        axios.post('/twitter/vue/check_pin', {
          'twitter_user': this.form.user,
          'pin_number': this.pinForm.pin,
          'email': this.pinForm.email
        })
        .then(response => {
          this.pinForm.isProcessing = false;
          this.pinForm.isRedirecting = true;
          window.location.href = response.data.redirectTo;
        })
        .catch(error => {
          this.pinForm.isProcessing = false;

          if (error.response) {
            this.unpackErrorList(error.response.data, this.pinForm);
          } else {
            this.pinForm.errors = ['Request failed with an undefined error!'];
          }
        })
      },

      showInformation() {
        $('#modal-information').modal('show');
      },

      unpackErrorList(data, elem) {
        var errors = 'Unknown error.';

        elem.errors = [];

        if (data.hasOwnProperty('errors')) {
          errors = data.errors;
        } else if (data.hasOwnProperty('message')) {
          errors = data.message;
        }

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
