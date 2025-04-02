<template>
  <div class="chessboard-container">
    <!-- Controls in their own div, positioned next to the chessboard -->
    <div class="chessboard-controls">
      <button @click="flipBoard" class="control-button" title="Retourner l'échiquier">
        <!-- Circular arrows icon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
      </button>

      <button @click="toggleSettingsMenu" class="control-button" title="Paramètres">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
      </button>

      <!-- Settings dropdown menu -->
      <div v-if="showSettingsMenu" class="settings-menu">
        <div class="settings-header">
          <div class="settings-title">Paramètres</div>
          <!-- X mark close button -->
          <button @click="toggleSettingsMenu" class="settings-close" title="Fermer">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="settings-options">
          <label class="settings-option">
            <span>Aperçu des coups</span>
            <div class="toggle-switch">
              <input type="checkbox" v-model="showMovePreview" class="sr-only" id="toggle-move-preview" />
              <div class="toggle-track"></div>
              <div class="toggle-thumb" :class="showMovePreview ? 'toggle-on' : 'toggle-off'"></div>
            </div>
          </label>
          <label class="settings-option">
            <span>Coordonnées</span>
            <div class="toggle-switch">
              <input type="checkbox" v-model="showCoordinates" class="sr-only" id="toggle-coordinates" />
              <div class="toggle-track"></div>
              <div class="toggle-thumb" :class="showCoordinates ? 'toggle-on' : 'toggle-off'"></div>
            </div>
          </label>
        </div>
      </div>
    </div>

    <div ref="chessboardRef" id="chessboard" class="overflow-hidden select-none touch-action-none"
      @mousemove="movePiece" @mousedown="grabPiece" @mouseup="dropPiece"
      @touchstart="handleTouch($event); grabPiece($event)" @touchmove="handleTouch($event); movePiece($event)"
      @touchend="handleTouch($event); dropPiece($event)" @contextmenu.prevent="resetPiece($event)">
      <Square v-for="(square, index) in board" :key="index" :isWhite="square.isWhite" :piece="square.piece"
        :highlight="square.highlight && showMovePreview" :drag="square.drag"
        :isHighlighted="isSquareHighlighted(square.position)"
        :isPuzzleComplete="isPuzzleComplete && isLastSquare(square.position)" />
      <div class="chessboard-nav">
        <!-- Navigation buttons or additional content can go here -->
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, computed, onMounted, onUnmounted } from "vue";
import Square from "./Square.vue";
import { row, column } from "../Constants";
import { Position } from "../models";
import type { PropType } from "vue";

export default defineComponent({
  name: "Chessboard",
  components: { Square },
  props: {
    playMove: {
      type: Function as PropType<
        (piece: any, destination: Position, chessboard: HTMLElement) => boolean
      >,
      required: true,
    },
    pieces: {
      type: Array as PropType<any[]>,
      required: true,
    },
    setOrientation: {
      type: Function as PropType<(orientation: string) => void>,
      required: true,
    },
    getOrientation: {
      type: Function as PropType<() => string>,
      required: true,
    },
    teamTurn: {
      type: String,
      required: true,
    },
    lastMoveSquares: {
      type: Array as PropType<Position[]>,
      default: () => []
    },
    isPuzzle: {
      type: Boolean,
      default: false
    },
    isPuzzleComplete: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const activePiece = ref<HTMLElement | null>(null);
    const grabPosition = ref<Position | null>(null);
    const isDragging = ref(false);
    const chessboardRef = ref<HTMLElement | null>(null);

    // Settings state
    const showSettingsMenu = ref(false);
    const showMovePreview = ref(true);
    const showCoordinates = ref(false);

    // Toggle settings menu
    const toggleSettingsMenu = () => {
      showSettingsMenu.value = !showSettingsMenu.value;
    };

    // Handle clicks outside of the settings menu
    const handleClickOutside = (event: MouseEvent) => {
      const menuEl = document.querySelector('.settings-menu');
      const buttonEl = document.querySelector('.control-button[title="Paramètres"]');

      if (showSettingsMenu.value &&
        menuEl &&
        buttonEl &&
        !menuEl.contains(event.target as Node) &&
        !buttonEl.contains(event.target as Node)) {
        showSettingsMenu.value = false;
      }
    };

    // Add and remove event listener for click outside
    onMounted(() => {
      document.addEventListener('click', handleClickOutside);
    });

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside);
    });

    // Flip the board
    const flipBoard = () => {
      const newOrientation = props.getOrientation() === "white" ? "black" : "white";
      props.setOrientation(newOrientation);
      // Close the menu after flipping the board
      showSettingsMenu.value = false;
    };

    // Helper: get board bounding rectangle
    const getBoardRect = () => {
      return chessboardRef.value?.getBoundingClientRect() || { left: 0, top: 0, width: 0, height: 0 };
    };

    const handleTouch = (e: TouchEvent) => {
      e.stopPropagation();
    };

    // Revised grabPiece:
    const grabPiece = (e: MouseEvent | TouchEvent) => {
      const chessboard = chessboardRef.value;
      if (!chessboard) return;

      // Use closest() so clicking on a child still selects the piece.
      const element = (e.target as HTMLElement).closest(".piece") as HTMLElement;
      if (!element || !element.classList.contains("drag")) return;

      let clientX = 0, clientY = 0;
      if (e.type === "touchstart") {
        isDragging.value = true;
        clientX = (e as TouchEvent).touches[0].clientX;
        clientY = (e as TouchEvent).touches[0].clientY;
      } else {
        clientX = (e as MouseEvent).clientX;
        clientY = (e as MouseEvent).clientY;
      }

      const rect = getBoardRect();
      const cellSize = rect.height / 8; // Since board is 80vmin and 8 rows → 10vmin each

      // We leave the piece's CSS size as defined (10vmin in your CSS).
      element.style.position = "absolute";
      element.style.zIndex = "999";
      element.style.left = `${clientX - cellSize / 2}px`;
      element.style.top = `${clientY - cellSize / 2}px`;

      // For white orientation, row index = 7 - floor((clientY - top) / cellSize)
      const relativeX = clientX - rect.left;
      const relativeY = clientY - rect.top;
      const colIndex = Math.floor(relativeX / cellSize);
      const rowIndexWhite = 7 - Math.floor(relativeY / cellSize);

      grabPosition.value =
        props.getOrientation() === "white"
          ? new Position(colIndex, rowIndexWhite)
          : new Position(7 - colIndex, 7 - rowIndexWhite);

      activePiece.value = element;
    };

    const movePiece = (e: MouseEvent | TouchEvent) => {
      const chessboard = chessboardRef.value;
      if (!activePiece.value || !chessboard) return;
      const rect = getBoardRect();
      let clientX = 0, clientY = 0;
      if (isDragging.value) {
        clientX = (e as TouchEvent).touches[0].clientX;
        clientY = (e as TouchEvent).touches[0].clientY;
      } else {
        clientX = (e as MouseEvent).clientX;
        clientY = (e as MouseEvent).clientY;
      }
      const cellSize = rect.height / 8;
      const clampedX = Math.max(rect.left, Math.min(clientX, rect.left + rect.width));
      const clampedY = Math.max(rect.top, Math.min(clientY, rect.top + rect.height));
      activePiece.value.style.left = `${clampedX - cellSize / 2}px`;
      activePiece.value.style.top = `${clampedY - cellSize / 2}px`;
    };

    const dropPiece = (e: MouseEvent | TouchEvent) => {
      const chessboard = chessboardRef.value;
      if (!activePiece.value || !chessboard || !grabPosition.value) return;
      const rect = getBoardRect();
      let clientX = 0, clientY = 0;
      if (isDragging.value) {
        clientX = (e as TouchEvent).changedTouches[0].clientX;
        clientY = (e as TouchEvent).changedTouches[0].clientY;
      } else {
        clientX = (e as MouseEvent).clientX;
        clientY = (e as MouseEvent).clientY;
      }
      const cellSize = rect.height / 8;
      let col = Math.floor((clientX - rect.left) / cellSize);
      let rowWhite = 7 - Math.floor((clientY - rect.top) / cellSize);
      let finalPos = props.getOrientation() === "white"
        ? new Position(col, rowWhite)
        : new Position(7 - col, 7 - rowWhite);

      const currentPiece = props.pieces.find(p => p.samePosition(grabPosition.value));
      if (currentPiece) {
        const moveSuccessful = props.playMove(currentPiece, finalPos, chessboard);
        if (!moveSuccessful) {
          activePiece.value.style.removeProperty("top");
          activePiece.value.style.removeProperty("left");
        }
        activePiece.value.style.zIndex = "0";
        activePiece.value = null;
        isDragging.value = false;
      }
    };

    // New function to reset piece position on right-click
    const resetPiece = (e: MouseEvent | TouchEvent) => {
      if (activePiece.value) {
        // Reset the piece styling
        activePiece.value.style.removeProperty("top");
        activePiece.value.style.removeProperty("left");
        activePiece.value.style.removeProperty("position");
        activePiece.value.style.zIndex = "0";

        // Reset the drag state
        activePiece.value = null;
        isDragging.value = false;
        e.stopPropagation();
        e.preventDefault(); // Prevent default context menu
      }
    };

    // Keep renderBoard unchanged from before
    const renderBoard = () => {
      const boardArray = [];
      let white_square = false;
      let jStart, jEnd, jIncrement, iStart, iEnd, iIncrement;
      if (props.getOrientation() === "white") {
        jStart = row.length - 1;
        jEnd = -1;
        jIncrement = -1;
        iStart = 0;
        iEnd = column.length;
        iIncrement = 1;
      } else {
        jStart = 0;
        jEnd = row.length;
        jIncrement = 1;
        iStart = column.length - 1;
        iEnd = -1;
        iIncrement = -1;
      }
      for (let j = jStart; j !== jEnd; j += jIncrement) {
        white_square = !white_square;
        for (let i = iStart; i !== iEnd; i += iIncrement) {
          const piece = props.pieces.find(p => p.samePosition(new Position(i, j)));
          const image = piece ? piece.image : undefined;
          const currentPiece = activePiece.value
            ? props.pieces.find(p => p.samePosition(grabPosition.value))
            : undefined;
          let highlight = currentPiece?.possibleMoves
            ? currentPiece.possibleMoves.some((p: Position) => p.samePosition(new Position(i, j)))
            : false;
          if (!highlight && currentPiece?.isPawn && piece?.isPawn) {
            const enPassantPiece =
              piece.enPassant &&
              piece.team !== currentPiece.team &&
              Math.abs(piece.position.x - currentPiece.position.x) === 1 &&
              piece.position.y === currentPiece.position.y;
            if (enPassantPiece) highlight = true;
          }
          const drag = piece?.team === props.teamTurn;
          boardArray.push({
            isWhite: white_square,
            piece: image,
            highlight,
            drag,
            position: new Position(i, j)
          });
          white_square = !white_square;
        }
      }
      return boardArray;
    };

    const board = computed(() => renderBoard());

    function isSquareHighlighted(position: Position): boolean {
      return props.lastMoveSquares.some(square => square.samePosition(position));
    }

    function isLastSquare(position: Position): boolean {
      if (props.lastMoveSquares.length === 0) return false;
      const lastSquare = props.lastMoveSquares[props.lastMoveSquares.length - 1];
      return position.samePosition(lastSquare);
    }

    return {
      chessboardRef,
      movePiece,
      grabPiece,
      dropPiece,
      resetPiece,
      handleTouch,
      board,
      isSquareHighlighted,
      isLastSquare,
      // Settings state
      showSettingsMenu,
      toggleSettingsMenu,
      showMovePreview,
      showCoordinates,
      flipBoard
    };
  },
});
</script>

<style scoped>
.chessboard-container {
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.chessboard-controls {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.control-button {
  background-color: white;
  border: 1px solid #e2e8f0;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.2s;
}

.control-button:hover {
  background-color: #f8fafc;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.settings-menu {
  position: absolute;
  top: 40px;
  left: 0;
  width: 220px;
  background-color: white;
  border-radius: 6px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08);
  z-index: 100;
  overflow: hidden;
}

.settings-header {
  padding: 10px 16px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.settings-title {
  font-weight: 600;
  font-size: 14px;
  color: #1a202c;
}

.settings-close {
  cursor: pointer;
  color: #718096;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2px;
  border-radius: 4px;
}

.settings-close:hover {
  background-color: #f7fafc;
  color: #4a5568;
}

.settings-options {
  padding: 8px 0;
  border-bottom: 1px solid #e2e8f0;
}

.settings-option {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 16px;
  font-size: 14px;
  color: #4a5568;
  cursor: pointer;
}

.settings-option:hover {
  background-color: #f7fafc;
}

.toggle-switch {
  position: relative;
  width: 36px;
  height: 20px;
}

.toggle-track {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #e2e8f0;
  border-radius: 20px;
}

.toggle-thumb {
  position: absolute;
  top: 2px;
  width: 16px;
  height: 16px;
  background-color: white;
  border-radius: 50%;
  transition: transform 0.2s ease;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.toggle-off {
  left: 2px;
}

.toggle-on {
  transform: translateX(18px);
  background-color: #4299e1;
}

#chessboard {
  height: 80vmin;
  width: 80vmin;
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  grid-template-rows: repeat(8, 1fr);
  outline: none;
  background-color: #94242E;
  touch-action: none;
  overflow: hidden;
}

#chessboard:hover {
  cursor: pointer;
}

/* This applies to all squares with the .squareHighlight class */
:deep(.squareHighlight) {
  position: relative;
  outline: none;
  display: flex;
  justify-content: center;
  align-items: center;
}

:deep(.squareHighlight::before) {
  content: "";
  width: 3vmin;
  height: 3vmin;
  box-shadow: inset 0 0 3vmin 3vmin rgba(0, 0, 0, 0.5);
  border-radius: 50%;
}

/* This applies to all squares with the .squareHighlightOccupied class */
:deep(.squareHighlightOccupied) {
  border-radius: 3vmin;
  /* No additional styling to preserve just the red corner effect from the background */
}

@media screen and (max-width:768px) {
  #chessboard {
    height: 80vmin;
    width: 80vmin;
    grid-template-columns: repeat(8, 1fr);
    grid-template-rows: repeat(8, 1fr);
  }

  .piece {
    width: 10vmin;
    height: 10vmin;
  }
}
</style>
