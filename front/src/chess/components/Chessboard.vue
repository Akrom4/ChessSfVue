<template>
  <div ref="chessboardRef" id="chessboard" class="overflow-hidden select-none touch-action-none" @mousemove="movePiece"
    @mousedown="grabPiece" @mouseup="dropPiece" @touchstart="handleTouch($event); grabPiece($event)"
    @touchmove="handleTouch($event); movePiece($event)" @touchend="handleTouch($event); dropPiece($event)"
    @contextmenu.prevent="resetPiece($event)">
    <Square v-for="(square, index) in board" :key="index" :isWhite="square.isWhite" :piece="square.piece"
      :highlight="square.highlight" :drag="square.drag" :isHighlighted="isSquareHighlighted(square.position)"
      :isPuzzleComplete="isPuzzleComplete && isLastSquare(square.position)" />
    <div class="chessboard-nav">
      <!-- Navigation buttons or additional content can go here -->
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, computed } from "vue";
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
      isLastSquare
    };
  },
});
</script>

<style scoped>
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
