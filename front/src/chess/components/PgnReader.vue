<template>
  <div class="pgn-reader">
    <div class="pgn-container">
      <div v-for="(chapter, chapterIndex) in chapters" :key="chapterIndex">
        <!-- Chapter title -->
        <div v-if="chapter.Title" class="flex justify-between mb-4">
          <h3 class="text-xl font-bold">{{ chapter.Title }}</h3>
        </div>

        <div class="pgn-content">
          <!-- Moves display area -->
          <div class="pgn-moves">
            <div class="moves-list">
              <template v-for="(move, index) in chapter.Moves" :key="index">
                <div class="move-container">
                  <span v-if="move.teamColor === 'w'" class="move-number">{{ move.moveNumber }}.</span>
                  <span :class="['move', { 'active': currentPosition === move.position }]"
                    @click="handleMoveClick(move)">
                    {{ toFrenchNotation(move.move) }}
                  </span>
                </div>

                <!-- Display comments -->
                <div
                  v-for="(comment, commentIndex) in renderComments(chapter.Comments, move.moveNumber, move.teamColor)"
                  :key="`${index}-${commentIndex}`" class="comment">
                  {{ comment }}
                </div>
              </template>
            </div>
          </div>

          <!-- Navigation controls -->
          <div class="pgn-navigation mt-4 md:mt-4 sm:mt-0">
            <div class="flex items-center justify-center gap-2">
              <button @click="goToStart(chapter)" title="Initial position" class="nav-button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-icon">
                  <polyline points="11 17 6 12 11 7"></polyline>
                  <polyline points="18 17 13 12 18 7"></polyline>
                </svg>
              </button>
              <button @click="goToPrev(chapter)" title="Previous move" class="nav-button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-icon">
                  <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
              </button>
              <button @click="playMoves(chapter)" title="Play moves" class="nav-button">
                <svg v-if="!isPlaying" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  class="nav-icon">
                  <polygon points="5 3 19 12 5 21 5 3"></polygon>
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-icon">
                  <rect x="6" y="4" width="4" height="16"></rect>
                  <rect x="14" y="4" width="4" height="16"></rect>
                </svg>
              </button>
              <button @click="goToNext(chapter)" title="Next move" class="nav-button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-icon">
                  <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
              </button>
              <button @click="goToEnd(chapter)" title="Final position" class="nav-button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-icon">
                  <polyline points="13 17 18 12 13 7"></polyline>
                  <polyline points="6 17 11 12 6 7"></polyline>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed, ref, onUnmounted } from 'vue';

// Define interfaces for the PGN data structure
interface PgnMove {
  move: string;
  position: string;
  teamColor: string;
  moveNumber: number;
}

interface PgnComment {
  text: string;
  teamColor: string;
  moveNumber: number;
}

interface PgnChapter {
  Title?: string;
  Moves: PgnMove[];
  Comments?: PgnComment[];
  FEN?: string;
  Number?: number;
  Variations?: any[];
}

export default defineComponent({
  name: 'PgnReader',
  props: {
    pgnData: {
      type: Object,
      default: null
    },
    onMoveClick: {
      type: Function,
      required: true
    }
  },
  setup(props, { emit }) {
    const currentPosition = ref('');
    const currentMoveIndex = ref(-1);
    const isPlaying = ref(false);
    const lastMoveAlg = ref(''); // To store the last move in algebraic notation for highlighting
    let playInterval: number | null = null;

    const chapters = computed<PgnChapter[]>(() => {
      if (!props.pgnData) return [];

      if (props.pgnData.chapter) {
        return props.pgnData.chapter || [];
      }

      if (props.pgnData.Moves) {
        return [props.pgnData as PgnChapter];
      }

      return [];
    });

    // Convert piece notation to French notation
    const toFrenchNotation = (move: string): string => {
      // Skip conversion if the move doesn't start with a piece letter
      if (!move || move.length < 2) return move;

      // Replace standard piece notation with French notation
      const frenchPieces: Record<string, string> = {
        'R': 'T', // Rook (Tour)
        'N': 'C', // Knight (Cavalier)
        'B': 'F', // Bishop (Fou)
        'Q': 'D', // Queen (Dame)
        'K': 'R'  // King (Roi)
      };

      // Handle castling notation
      if (move === 'O-O' || move === '0-0') return 'O-O';
      if (move === 'O-O-O' || move === '0-0-0') return 'O-O-O';

      // Regular expression to match piece notation followed by disambiguation, captures, etc.
      // Format: Piece(optional file/rank for disambiguation)x(for captures)(destination square)(promotion)(check/mate)
      const moveRegex = /^([RNBQK])([a-h1-8]?)([a-h1-8]?)([x]?)([a-h][1-8])([=][RNBQK])?([+#]?)$/;
      const match = move.match(moveRegex);

      if (match) {
        const [_, piece, disambigFile, disambigRank, capture, destination, promotion, checkOrMate] = match;
        return `${frenchPieces[piece]}${disambigFile}${disambigRank}${capture}${destination}${promotion || ''}${checkOrMate || ''}`;
      }

      // Check if the move starts with a piece letter (simpler fallback)
      const firstChar = move.charAt(0);
      if (frenchPieces[firstChar]) {
        return frenchPieces[firstChar] + move.substring(1);
      }

      return move;
    };

    // Convert SAN move to algebraic notation for highlighting (e.g. e2e4 format)
    const extractAlgebraicMove = (move: PgnMove): string => {
      // For moves already in algebraic notation (e.g., e2e4), return as is
      if (move.move && move.move.match(/^[a-h][1-8][a-h][1-8][qrbnk]?$/)) {
        return move.move;
      }

      // Handle castling
      if (move.move === 'O-O' || move.move === '0-0') {
        // Kingside castling
        return move.teamColor === 'w' ? 'e1g1' : 'e8g8';
      } else if (move.move === 'O-O-O' || move.move === '0-0-0') {
        // Queenside castling
        return move.teamColor === 'w' ? 'e1c1' : 'e8c8';
      }

      // For other moves, we need to extract the destination square at minimum
      // This is a simplified version and may not work for all moves
      const moveText = move.move;

      // Try to extract the destination square
      const destSquareMatch = moveText.match(/[a-h][1-8]$/);
      if (destSquareMatch) {
        const destSquare = destSquareMatch[0];
        console.log(`Found destination square ${destSquare} for move ${moveText}`);
        return destSquare; // Return just the destination square for highlighting
      }

      console.log(`Could not extract algebraic notation from: ${moveText}`);
      return '';
    };

    const handleMoveClick = (move: PgnMove) => {
      currentPosition.value = move.position;

      // Extract algebraic notation for highlighting
      lastMoveAlg.value = extractAlgebraicMove(move);
      console.log(`Move clicked: ${move.move}, position: ${move.position}, algebraic: ${lastMoveAlg.value}`);

      // Call the parent's onMoveClick with position and lastMove
      props.onMoveClick(move, move.position, lastMoveAlg.value);

      // Find the move index in the chapter
      const chapter = chapters.value.find((c: PgnChapter) => c.Moves.some((m: PgnMove) => m.position === move.position));
      if (chapter) {
        currentMoveIndex.value = chapter.Moves.findIndex((m: PgnMove) => m.position === move.position);
      }

      // Scroll the active move into view
      scrollActiveIntoView();
    };

    const goToStart = (chapter: PgnChapter) => {
      if (chapter && chapter.Moves && chapter.Moves.length > 0) {
        currentMoveIndex.value = 0;
        const move = chapter.Moves[0];
        lastMoveAlg.value = extractAlgebraicMove(move);
        handleMoveClick(move);
      }
    };

    const goToEnd = (chapter: PgnChapter) => {
      if (chapter && chapter.Moves && chapter.Moves.length > 0) {
        currentMoveIndex.value = chapter.Moves.length - 1;
        const move = chapter.Moves[chapter.Moves.length - 1];
        lastMoveAlg.value = extractAlgebraicMove(move);
        handleMoveClick(move);
      }
    };

    const goToPrev = (chapter: PgnChapter) => {
      if (chapter && chapter.Moves && chapter.Moves.length > 0) {
        if (currentMoveIndex.value > 0) {
          currentMoveIndex.value--;
          const move = chapter.Moves[currentMoveIndex.value];
          lastMoveAlg.value = extractAlgebraicMove(move);
          handleMoveClick(move);
        } else {
          goToStart(chapter);
        }
      }
    };

    const goToNext = (chapter: PgnChapter) => {
      if (chapter && chapter.Moves && chapter.Moves.length > 0) {
        if (currentMoveIndex.value < chapter.Moves.length - 1) {
          currentMoveIndex.value++;
          const move = chapter.Moves[currentMoveIndex.value];
          lastMoveAlg.value = extractAlgebraicMove(move);
          handleMoveClick(move);
        } else {
          goToEnd(chapter);
        }
      }
    };

    const playMoves = (chapter: PgnChapter) => {
      // Toggle playback state
      isPlaying.value = !isPlaying.value;

      // Stop existing playback if any
      if (playInterval) {
        clearInterval(playInterval);
        playInterval = null;
        if (!isPlaying.value) {
          return;
        }
      }

      // If toggling on playback
      if (isPlaying.value) {
        // Start from beginning if at the end
        if (currentMoveIndex.value >= chapter.Moves.length - 1) {
          goToStart(chapter);
        }

        // Play moves with 1-second interval
        playInterval = window.setInterval(() => {
          if (currentMoveIndex.value < chapter.Moves.length - 1) {
            goToNext(chapter);
          } else {
            // Stop when we reach the end
            isPlaying.value = false;
            if (playInterval) {
              clearInterval(playInterval);
              playInterval = null;
            }
          }
        }, 1000);
      }
    };

    const renderComments = (comments: PgnComment[] | undefined, moveNumber: number, color: string): string[] => {
      if (!comments) return [];

      return comments
        .filter(comment => comment.moveNumber === moveNumber && comment.teamColor === color)
        .map(comment => String(comment.text).replace(/\[%cal .*?\]/g, ''));
    };

    // Helper function to scroll the active move into view
    const scrollActiveIntoView = () => {
      setTimeout(() => {
        const activeMove = document.querySelector('.move.active');
        if (activeMove) {
          activeMove.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }, 10);
    };

    // Clean up interval when component unmounts
    onUnmounted(() => {
      if (playInterval) {
        clearInterval(playInterval);
        playInterval = null;
      }
    });

    return {
      chapters,
      currentPosition,
      currentMoveIndex,
      isPlaying,
      lastMoveAlg,
      handleMoveClick,
      goToStart,
      goToEnd,
      goToPrev,
      goToNext,
      playMoves,
      renderComments,
      toFrenchNotation
    };
  }
});
</script>

<style scoped>
.pgn-reader {
  display: flex;
  flex-direction: column;
  width: 100%;
}

.pgn-container {
  background-color: #f5f5f5;
  border-radius: 0.5rem;
  padding: 1rem;
  box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.1);
  width: 100%;
}

.pgn-content {
  display: flex;
  flex-direction: column;
}

.pgn-moves {
  max-height: 18.75rem;
  overflow-y: auto;
  margin-bottom: 1rem;
  background: white;
  padding: 1rem;
  border-radius: 0.25rem;
  border: 0.0625rem solid #e0e0e0;
  scroll-behavior: smooth;
}

.moves-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
  align-items: center;
  padding: 0.5rem 0;
}

.move-container {
  display: inline-flex;
  align-items: center;
  margin-bottom: 0.125rem;
  margin-right: 0.125rem;
}

.move-number {
  color: #666;
  font-weight: bold;
  margin-right: 0.1rem;
  display: inline-block;
}

.move {
  cursor: pointer;
  padding: 0.1rem 0.35rem;
  border-radius: 0.25rem;
  transition: background-color 0.2s;
  color: #214a7c;
  font-weight: bold;
}

.move:hover {
  background-color: #eee;
}

.move.active {
  background-color: #4CAF50;
  color: white;
  font-weight: bold;
  box-shadow: 0 0 0.25rem rgba(0, 0, 0, 0.2);
  transform: scale(1.05);
  position: relative;
  z-index: 1;
}

.comment {
  width: 100%;
  margin: 0.5rem 0;
  padding: 0.5rem;
  background-color: #f5f5f5;
  border-left: 0.25rem solid #214a7c;
  font-size: 0.875rem;
  line-height: 1.4;
}

.pgn-navigation {
  display: flex;
  justify-content: center;
}

.nav-button {
  width: 2.5rem;
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.5rem;
  background-color: var(--color-nav-start, #214a7c);
  color: var(--color-custom-yellow, white);
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.15);
  transition: all 0.2s;
}

.nav-button:hover {
  background-color: var(--color-nav-hover, #1a3a61);
  transform: translateY(-0.125rem);
  box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
}

.nav-button:active {
  background-color: var(--color-nav-end, #152f4f);
  transform: translateY(0);
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.15);
}

.nav-icon {
  width: 1.25rem;
  height: 1.25rem;
}

@media (max-width: 768px) {
  .pgn-container {
    width: 100%;
    max-width: 100%;
    height: auto;
    max-height: 80vh;
    margin: 0;
    padding: 1rem;
  }

  /* Move navigation controls to top of the moves list on mobile */
  .pgn-content {
    display: flex;
    flex-direction: column-reverse;
  }

  .pgn-navigation {
    margin-bottom: 1rem;
    margin-top: 0;
  }

  /* Hide title on mobile */
  .flex.justify-between.mb-4 {
    display: none;
  }
}

@media (max-width: 480px) {
  .pgn-container {
    padding: 0.5rem;
  }

  .pgn-navigation {
    margin-bottom: 0.5rem;
  }

  .nav-button {
    width: 2.25rem;
    height: 2.25rem;
  }

  .pgn-moves {
    padding: 0.5rem;
    max-height: 15rem;
  }

  .comment {
    margin: 0.25rem 0;
    padding: 0.25rem;
    font-size: 0.8rem;
  }
}
</style>