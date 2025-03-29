<!-- ChessApp.vue -->
<template>
  <div id="chess-app">
    <div class="game-container">
      <Referee :fen="fen" :moves="lastMoveAlg" />
      <GameNavigator :gameState="gameState" :currentGameStateIndex="currentGameStateIndex"
        :setCurrentGameStateIndex="setCurrentGameStateIndex" :setFen="setFen" :INITIAL_FEN="INITIAL_FEN" />
    </div>

    <!-- PgnReader with chapter data from props -->
    <PgnReader :pgnData="chapterData || fallbackChapter" :onMoveClick="handleMoveClick" :gameState="gameState"
      :currentGameStateIndex="currentGameStateIndex" />
  </div>
</template>

<script>
import { ref, reactive, watch, computed, onMounted } from 'vue';
import Referee from './components/Referee.vue';
import PgnReader from './components/PgnReader.vue';
import GameNavigator from './components/GameNavigator.vue';

export default {
  components: {
    Referee,
    PgnReader
  },
  props: {
    chapterData: {
      type: Object,
      required: false,
      default: null
    }
  },
  setup(props) {
    // Log the chapter data received from props
    console.log('ChessApp received chapterData:', props.chapterData, 'Component key refresh');

    // Fallback test data to use if no chapter data is provided
    const fallbackChapter = reactive({
      FEN: "",
      Moves: [
        { move: "e4", position: "rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1", teamColor: "w", moveNumber: 1 },
        { move: "e5", position: "rnbqkbnr/pppp1ppp/8/4p3/4P3/8/PPPP1PPP/RNBQKBNR w KQkq e6 0 1", teamColor: "b", moveNumber: 1 }
      ],
      Title: "Test Chapter",
      Number: 1,
      Comments: [],
      Variations: []
    });

    const INITIAL_FEN = computed(() => {
      // Use chapter's starting FEN if available, otherwise use standard starting position
      return props.chapterData?.FEN || "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1";
    });

    // Initialize gameState based on chapter data when available
    const gameState = computed(() => {
      return props.chapterData?.Moves || fallbackChapter.Moves;
    });

    // Initialize with the initial position
    const fen = ref(INITIAL_FEN.value);
    const currentGameStateIndex = ref(-1);
    const lastMoveAlg = ref(''); // Store the last move algebraic notation for highlighting

    // Force reset on component initialization
    onMounted(() => {
      console.log('ChessApp mounted, resetting state with:', INITIAL_FEN.value);
      currentGameStateIndex.value = -1;
      fen.value = INITIAL_FEN.value;
      lastMoveAlg.value = ''; // Clear any highlighting
    });

    const setCurrentGameStateIndex = (index) => {
      currentGameStateIndex.value = index;
    };

    const setFen = (newFen) => {
      fen.value = newFen;
    };

    watch([gameState, currentGameStateIndex], () => {
      if (gameState.value.length > 0 && currentGameStateIndex.value >= 0 && currentGameStateIndex.value < gameState.value.length) {
        fen.value = gameState.value[currentGameStateIndex.value].position;
      } else {
        // Reset to initial position if no move is selected
        fen.value = INITIAL_FEN.value;
        lastMoveAlg.value = ''; // Clear highlighting when going back to initial position
      }
    });

    // When chapter data changes, reset the position
    watch(() => props.chapterData, (newData) => {
      console.log('ChapterData changed, resetting state:', newData);
      // Force reset
      currentGameStateIndex.value = -1;
      fen.value = newData?.FEN || "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1";
      lastMoveAlg.value = ''; // Clear highlighting when changing chapters
    }, { deep: true, immediate: true });

    const handleMoveClick = (move, position, moveAlg) => {
      fen.value = position;
      // Set the last move algebraic notation for highlighting
      lastMoveAlg.value = moveAlg || '';
      console.log(`Move clicked: ${move.move}, position: ${position}, algebraic for highlighting: ${lastMoveAlg.value}`);

      const index = gameState.value.findIndex(m => m.position === position);
      if (index !== -1) {
        currentGameStateIndex.value = index;
      }
    };

    return {
      fallbackChapter,
      INITIAL_FEN,
      gameState,
      fen,
      currentGameStateIndex,
      lastMoveAlg,
      setCurrentGameStateIndex,
      setFen,
      handleMoveClick
    };
  }
};
</script>

<style scoped>
#chess-app {
  display: flex;
  user-select: none;
  width: 100%;
  max-width: 1200px;
}

.game-container {
  display: flex;
  flex-direction: column;
  flex: 1;
  align-items: center;
}

@media (max-width: 768px) {
  #chess-app {
    flex-direction: column;
    gap: 1rem;
    align-items: center;
  }

  .game-container {
    width: 100%;
  }

  /* Ensure PgnReader is full width on mobile */
  :deep(#chess-app > div:not(.game-container)) {
    width: 100% !important;
    max-width: none;
    margin-top: 1rem;
  }
}
</style>