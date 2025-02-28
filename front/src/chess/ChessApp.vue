<!-- ChessApp.vue -->
<template>
  <div id="chess-app">
    <div class="game-container">
      <Referee :fen="fen" />
      <GameNavigator
        :gameState="gameState"
        :currentGameStateIndex="currentGameStateIndex"
        :setCurrentGameStateIndex="setCurrentGameStateIndex"
        :setFen="setFen"
        :INITIAL_FEN="INITIAL_FEN"
      />
    </div>

    <PgnReader
      :pgnData="data"
      @moveClick="handleMoveClick"
      :gameState="gameState"
      :currentGameStateIndex="currentGameStateIndex"
    />
  </div>
</template>

<script>
import { ref, reactive, watch } from 'vue';
import Referee from './components/Referee/Referee.vue';
import PgnReader from './components/PgnReader/PgnReader.vue';
import GameNavigator from './components/GameNavigator/GameNavigator.vue';

export default {
  components: {
    Referee,
    PgnReader,
    GameNavigator
  },
  setup() {
    const data = reactive({
      title: "",
      chapter: [
        {
          FEN: "",
          Moves: [
            { move: "e4", position: "rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1", teamColor: "w", moveNumber: 1 },
            { move: "e5", position: "rnbqkbnr/pppp1ppp/8/4p3/4P3/8/PPPP1PPP/RNBQKBNR w KQkq e6 0 1", teamColor: "b", moveNumber: 1 }
          ],
          Title: "",
          Number: 1,
          Comments: [],
          Variations: []
        }
      ]
    });

    const INITIAL_FEN = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1";

    const gameState = ref(data.chapter?.[0]?.Moves || []);
    const fen = ref(INITIAL_FEN);
    const currentGameStateIndex = ref(-1);

    const setCurrentGameStateIndex = (index) => {
      currentGameStateIndex.value = index;
    };

    const setFen = (newFen) => {
      fen.value = newFen;
    };

    watch([gameState, currentGameStateIndex], () => {
      if (gameState.value.length > 0 && currentGameStateIndex.value >= 0 && currentGameStateIndex.value < gameState.value.length) {
        fen.value = gameState.value[currentGameStateIndex.value].position;
      }
    });

    const handleMoveClick = (move) => {
      fen.value = move.position;
      const index = gameState.value.findIndex(m => m.position === move.position);
      if (index !== -1) {
        currentGameStateIndex.value = index;
      }
    };

    return {
      data,
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
    background-color: #D0CCD0;
    user-select: none;
    align-items: center;
    justify-content: center;
}
</style>