<template>
    <div>
        About
    </div>
</template>

<script>
import axios from 'axios';

export default {
  created() {
    axios
      .get('/api/user')
      .then(function(response) {
          console.log(response)
      }).catch(function(error ){
          console.log('error');
      });
  }
}
</script>