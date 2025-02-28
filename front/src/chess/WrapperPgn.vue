<template>
  <div id="wrap-pgn" v-show="isVisible">
    <Referee />
    <PgnWrapper />
  </div>
</template>

<script>
import Referee from './components/Referee/Referee.vue';
import PgnWrapper from './components/PgnReader/PgnWrapper.vue';

export default {
  components: {
    Referee,
    PgnWrapper
  },
  data() {
    return {
      isVisible: false // Set to true to make the component visible
    };
  }
};
</script>

<style scoped>
#wrap-pgn{
	display: none;
}
</style>
