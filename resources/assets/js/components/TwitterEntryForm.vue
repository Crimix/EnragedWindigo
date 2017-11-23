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
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Twitter verification modal -->
</template>

<script>
  export default {
    data() {
      return {
        isContained: false,

        form: {
          user: "",
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
