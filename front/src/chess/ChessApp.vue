<!-- ChessApp.vue -->
<template>
  <div id="chess-app">
    <div class="game-container">
      <Referee :fen="fen" />
      <GameNavigator :gameState="gameState" :currentGameStateIndex="currentGameStateIndex"
        :setCurrentGameStateIndex="setCurrentGameStateIndex" :setFen="setFen" :INITIAL_FEN="INITIAL_FEN" />
    </div>

    <!-- PgnReader with chapter data from props -->
    <PgnReader :pgnData="chapterData || fallbackChapter" @moveClick="handleMoveClick" :gameState="gameState"
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
    PgnReader,
    GameNavigator
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

    // Force reset on component initialization
    onMounted(() => {
      console.log('ChessApp mounted, resetting state with:', INITIAL_FEN.value);
      currentGameStateIndex.value = -1;
      fen.value = INITIAL_FEN.value;
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
      }
    });

    // When chapter data changes, reset the position
    watch(() => props.chapterData, (newData) => {
      console.log('ChapterData changed, resetting state:', newData);
      // Force reset
      currentGameStateIndex.value = -1;
      fen.value = newData?.FEN || "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1";
    }, { deep: true, immediate: true });

    const handleMoveClick = (move) => {
      fen.value = move.position;
      const index = gameState.value.findIndex(m => m.position === move.position);
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
  height: 100%;
  width: 100%;
  max-width: 1200px;
}

.game-container {
  display: flex;
  flex-direction: column;
  flex: 1;
}

@media (max-width: 768px) {
  #chess-app {
    flex-direction: column;
  }
}
</style>