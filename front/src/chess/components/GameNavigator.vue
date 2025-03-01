<template>
  <div class="flex justify-between md:justify-center items-stretch mt-4">
    <button @click="moveToStart" title="Start" class="h-full bg-light-pgn hover:bg-green-500">
      <ChevronDoubleLeftIcon class="w-10 h-10 text-dark-pgn" />
    </button>
    <button @click="moveToPrev" title="Prev" class="h-full bg-light-pgn hover:bg-green-500">
      <ChevronLeftIcon class="w-10 h-10 text-dark-pgn" />
    </button>
    <button @click="moveToNext" title="Next" class="h-full bg-light-pgn hover:bg-green-500">
      <ChevronRightIcon class="w-10 h-10 text-dark-pgn" />
    </button>
    <button @click="moveToEnd" title="End" class="h-full bg-light-pgn hover:bg-green-500">
      <ChevronDoubleRightIcon class="w-10 h-10 text-dark-pgn" />
    </button>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import type { PropType } from "vue";
import {
  ChevronDoubleLeftIcon,
  ChevronDoubleRightIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
} from "@heroicons/vue/24/outline";

export default defineComponent({
  name: "GameNavigator",
  components: {
    ChevronDoubleLeftIcon,
    ChevronDoubleRightIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
  },
  props: {
    gameState: {
      type: Array as PropType<any[]>,
      required: true,
    },
    currentGameStateIndex: {
      type: Number,
      required: true,
    },
    setCurrentGameStateIndex: {
      type: Function as PropType<(index: number) => void>,
      required: true,
    },
    setFen: {
      type: Function as PropType<(fen: string) => void>,
      required: true,
    },
    INITIAL_FEN: {
      type: String,
      required: true,
    },
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
      if (props.currentGameStateIndex <= 0) {
        props.setCurrentGameStateIndex(-1);
        props.setFen(props.INITIAL_FEN);
      } else {
        props.setCurrentGameStateIndex(props.currentGameStateIndex - 1);
      }
    };

    const moveToNext = () => {
      if (props.currentGameStateIndex < props.gameState.length - 1) {
        props.setCurrentGameStateIndex(props.currentGameStateIndex + 1);
      }
    };

    return {
      moveToStart,
      moveToEnd,
      moveToPrev,
      moveToNext,
    };
  },
});
</script>


