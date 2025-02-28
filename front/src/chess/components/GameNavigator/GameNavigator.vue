<template>
  <div class="game-navigator">
    <button @click="moveToStart" title="Start">
      <ChevronDoubleLeftIcon class="icon" />
    </button>
    <button @click="moveToPrev" title="Prev">
      <ChevronLeftIcon class="icon" />
    </button>
    <button @click="moveToNext" title="Next">
      <ChevronRightIcon class="icon" />
    </button>
    <button @click="moveToEnd" title="End">
      <ChevronDoubleRightIcon class="icon" />
    </button>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import { ChevronDoubleLeftIcon, ChevronDoubleRightIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

export default defineComponent({
  name: 'GameNavigator',
  components: {
    ChevronDoubleLeftIcon,
    ChevronDoubleRightIcon,
    ChevronLeftIcon,
    ChevronRightIcon
  },
  props: {
    gameState: {
      type: Array as PropType<any[]>,
      required: true
    },
    currentGameStateIndex: {
      type: Number,
      required: true
    },
    setCurrentGameStateIndex: {
      type: Function as PropType<(index: number) => void>,
      required: true
    },
    setFen: {
      type: Function as PropType<(fen: string) => void>,
      required: true
    },
    INITIAL_FEN: {
      type: String,
      required: true
    }
  },
  setup(props) {
    const moveToStart = () => {
      props.setCurrentGameStateIndex(-1);
      props.setFen(props.INITIAL_FEN);
    };

    const moveToEnd = () => {
      if (props.gameState.length > 0) {
        props.setCurrentGameStateIndex(props.gameState.length - 1);
      }
    };

    const moveToPrev = () => {
      props.setCurrentGameStateIndex(props.currentGameStateIndex > 0 ? props.currentGameStateIndex - 1 : 0);
    };

    const moveToNext = () => {
      props.setCurrentGameStateIndex(props.currentGameStateIndex < props.gameState.length - 1 ? props.currentGameStateIndex + 1 : props.currentGameStateIndex);
    };

    return {
      moveToStart,
      moveToEnd,
      moveToPrev,
      moveToNext
    };
  }
});
</script>

<style scoped>
.icon {
  width: 24px;
  height: 24px;
}
</style>
