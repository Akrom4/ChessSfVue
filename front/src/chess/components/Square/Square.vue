<template>
  <div :class="className">
    <div v-if="piece" :style="{ backgroundImage: `url(${piece})` }" :class="['piece', drag ? 'drag' : '']"></div>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed } from 'vue';

export default defineComponent({
  name: 'Square',
  props: {
    isWhite: Boolean,
    piece: String,
    highlight: Boolean,
    drag: Boolean
  },
  setup(props) {
    const className = computed(() => [
      'square',
      props.isWhite ? 'whiteSquare' : 'blackSquare',
      !props.piece && props.highlight ? 'squareHighlight' : null,
      props.piece && props.highlight ? 'squareHighlightOccupied' : null,
      props.drag ? 'drag' : null
    ].filter(Boolean).join(' '));

    return { className };
  }
});
</script>

<style scoped>
.square {
    display: flex;
    justify-items: center;
}

.whiteSquare{
    background-color: #F0D9B7;
}

.blackSquare{
    background-color: #B58866;

}

.piece{
    background-repeat: no-repeat;
    background-position: center; 
    background-size: contain;
    width: 10vmin;
    height: 10vmin;
}

*::selection {
    background-color: transparent;
  }
</style>