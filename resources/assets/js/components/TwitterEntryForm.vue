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
</template>

<script>
  export default {
    data() {
      return {
        isContained: false
      };
    },

    mounted() {},

    methods: {
      checkUser() {
        axios.get('/twitter/vue/check', {
          params: {
            tuser: this.form.tuser
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
