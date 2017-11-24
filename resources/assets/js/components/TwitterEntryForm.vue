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
          <form class="form" role="form" @submit.prevent="checkUser">
            <div class="form-group">
              <label class="control-label">Twitter User</label>
              <input id="twitter-user-name" class="form-control" name="user" v-model="form.user"
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
            <div class="alert alert-danger" v-if="form.errors.length > 0">
                <p><strong>Whoops!</strong> Something went wrong!</p>
                <br>
                <ul>
                    <li v-for="(error, index) in form.errors" v-bind:key="index">
                        {{ error }}
                    </li>
                </ul>
            </div>


          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" @click="store">Verify</button>
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

        form: {
          user: "",
          pin: "",
          errors: []
        }
      };
    },

    mounted() {},

    methods: {
      checkUser() {
        axios.get('/twitter/vue/check', {
          params: {
            tuser: this.form.user
          }
        })
        .then(response => {
          //
        })
        .catch(error => {
          console.log(error);
        });
      }
    }
  }
</script>
