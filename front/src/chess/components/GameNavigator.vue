<template>
  <div class="my-4">
    <div class="flex items-center justify-center gap-2">
      <button @click="moveToStart" title="First position"
        class="w-10 h-10 flex items-center justify-center rounded-lg bg-[var(--color-nav-start)] text-[var(--color-custom-yellow)] shadow-md hover:bg-[var(--color-nav-hover)] active:bg-[var(--color-nav-end)] hover:-translate-y-0.5 active:translate-y-0 hover:shadow-lg active:shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white/30">
        <ChevronDoubleLeftIcon class="w-5 h-5" />
      </button>
      <button @click="moveToPrev" title="Previous move"
        class="w-10 h-10 flex items-center justify-center rounded-lg bg-[var(--color-nav-start)] text-[var(--color-custom-yellow)] shadow-md hover:bg-[var(--color-nav-hover)] active:bg-[var(--color-nav-end)] hover:-translate-y-0.5 active:translate-y-0 hover:shadow-lg active:shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white/30">
        <ChevronLeftIcon class="w-5 h-5" />
      </button>
      <button @click="moveToNext" title="Next move"
        class="w-10 h-10 flex items-center justify-center rounded-lg bg-[var(--color-nav-start)] text-[var(--color-custom-yellow)] shadow-md hover:bg-[var(--color-nav-hover)] active:bg-[var(--color-nav-end)] hover:-translate-y-0.5 active:translate-y-0 hover:shadow-lg active:shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white/30">
        <ChevronRightIcon class="w-5 h-5" />
      </button>
      <button @click="moveToEnd" title="Last position"
        class="w-10 h-10 flex items-center justify-center rounded-lg bg-[var(--color-nav-start)] text-[var(--color-custom-yellow)] shadow-md hover:bg-[var(--color-nav-hover)] active:bg-[var(--color-nav-end)] hover:-translate-y-0.5 active:translate-y-0 hover:shadow-lg active:shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white/30">
        <ChevronDoubleRightIcon class="w-5 h-5" />
      </button>
    </div>
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
