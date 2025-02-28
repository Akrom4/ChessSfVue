<template>
  <div @mousemove="movePiece" @mousedown="grabPiece" @mouseup="dropPiece"
    @touchstart="handleTouch($event); grabPiece($event)" @touchmove="handleTouch($event); movePiece($event)"
    @touchend="handleTouch($event); dropPiece($event)" @contextmenu.prevent ref="chessboardRef" id="chessboard">
    <Square v-for="(square, index) in board" :key="index" :isWhite="square.isWhite" :piece="square.piece"
      :highlight="square.highlight" :drag="square.drag" />
    <div class="chessboard-nav">
      <!-- Add navigation buttons or other elements here -->
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, computed } from 'vue';
import Square from '../Square/Square.vue';
import { row, column } from '../../Constants';
import { Position } from "../../models";
import type { PropType } from 'vue';

export default defineComponent({
  name: 'Chessboard',
  components: {
    Square
  },
  props: {
    playMove: {
      type: Function as PropType<(piece: any, position: Position, chessboard: HTMLElement) => boolean>,
      required: true
    },
    pieces: {
      type: Array as PropType<any[]>,
      required: true
    },
    setOrientation: {
      type: Function as PropType<(orientation: string) => void>,
      required: true
    },
    getOrientation: {
      type: Function as PropType<() => string>,
      required: true
    },
    teamTurn: {
      type: String,
      required: true
    }
  },
  setup(props) {
    const activePiece = ref<HTMLElement | null>(null);
    const grabPosition = ref<Position | null>(null);
    const isDragging = ref(false);
    const chessboardRef = ref<HTMLElement | null>(null);

    const handleTouch = (e: TouchEvent) => {
      e.stopPropagation();
    };

    const grabPiece = (e: MouseEvent | TouchEvent) => {
      const chessboard = chessboardRef.value;
      const element = e.target as HTMLElement;
      let x = 0;
      let y = 0;

      if (element.classList.contains("piece") && element.classList.contains("drag") && chessboard) {
        if (e.type === "touchstart") {
          isDragging.value = true;
          x = (e as TouchEvent).touches[0].clientX;
          y = (e as TouchEvent).touches[0].clientY;
        } else {
          x = (e as MouseEvent).clientX;
          y = (e as MouseEvent).clientY;
        }

        const width = element.offsetWidth;
        const height = element.offsetHeight;
        const left = x - (width / 2);
        const top = y - (height / 2);

        element.style.position = "absolute";
        element.style.zIndex = "999";
        element.style.left = `${left}px`;
        element.style.top = `${top}px`;

        const grabX = Math.floor((x - chessboard.offsetLeft) / (chessboard.clientHeight / row.length));
        const grabY = Math.abs(Math.ceil((y - chessboard.offsetTop - chessboard.clientHeight) / (chessboard.clientHeight / column.length)));
        grabPosition.value = props.getOrientation() === "white"
          ? new Position(grabX, grabY)
          : new Position(column.length - 1 - grabX, row.length - 1 - grabY);

        activePiece.value = element;
      }
    };

    const movePiece = (e: MouseEvent | TouchEvent) => {
      const chessboard = chessboardRef.value;
      if (activePiece.value && chessboard) {
        const minX = chessboard.offsetLeft;
        const minY = chessboard.offsetTop;
        const maxX = chessboard.offsetLeft + chessboard.clientWidth;
        const maxY = chessboard.offsetTop + chessboard.clientHeight;

        let x = grabPosition.value?.x || 0;
        let y = grabPosition.value?.y || 0;

        if (isDragging.value) {
          x = (e as TouchEvent).touches[0].clientX;
          y = (e as TouchEvent).touches[0].clientY;
        } else {
          x = (e as MouseEvent).clientX;
          y = (e as MouseEvent).clientY;
        }

        const width = activePiece.value.offsetWidth;
        const height = activePiece.value.offsetHeight;
        const left = x - (width / 2);
        const top = y - (height / 2);

        activePiece.value.style.left = x < minX ? `${minX - width / 2}px` : x > maxX ? `${maxX - width / 2}px` : `${left}px`;
        activePiece.value.style.top = y < minY ? `${minY - height / 2}px` : y > maxY ? `${maxY - height / 2}px` : `${top}px`;
      }
    };

    const dropPiece = (e: MouseEvent | TouchEvent) => {
      const chessboard = chessboardRef.value;

      if (activePiece.value && chessboard) {
        let x = grabPosition.value?.x || 0;
        let y = grabPosition.value?.y || 0;
        if (isDragging.value) {
          x = Math.floor(((e as TouchEvent).changedTouches[0].clientX - chessboard.offsetLeft) / (chessboard.clientHeight / row.length));
          y = Math.abs(Math.ceil(((e as TouchEvent).changedTouches[0].clientY - chessboard.offsetTop - chessboard.clientHeight) / (chessboard.clientHeight / column.length)));
        } else {
          x = Math.floor(((e as MouseEvent).clientX - chessboard.offsetLeft) / (chessboard.clientHeight / row.length));
          y = Math.abs(Math.ceil(((e as MouseEvent).clientY - chessboard.offsetTop - chessboard.clientHeight) / (chessboard.clientHeight / column.length)));
        }

        if (props.getOrientation() === 'black') {
          x = column.length - 1 - x;
          y = row.length - 1 - y;
        }

        const currentPiece = props.pieces.find(p => p.samePosition(grabPosition.value));

        if (currentPiece) {
          const moveSuccessful = props.playMove(currentPiece, new Position(x, y), chessboard);

          if (!moveSuccessful) {
            activePiece.value.style.removeProperty('top');
            activePiece.value.style.removeProperty('left');
          }

          activePiece.value.style.zIndex = "0";
          activePiece.value = null;
          isDragging.value = false;
        }
      }
    };

    const renderBoard = () => {
      const board = [];
      let white_square = false;
      let jStart, jEnd, jIncrement, iStart, iEnd, iIncrement;

      if (props.getOrientation() === 'white') {
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
          const currentPiece = activePiece.value != null ? props.pieces.find(p => p.samePosition(grabPosition.value)) : undefined;
          let highlight = currentPiece?.possibleMoves ? currentPiece.possibleMoves.some((p: Position) => p.samePosition(new Position(i, j))) : false;

          if (!highlight && currentPiece?.isPawn && piece?.isPawn) {
            const enPassantPiece = piece.enPassant &&
              piece.team !== currentPiece.team &&
              Math.abs(piece.position.x - currentPiece.position.x) === 1 &&
              piece.position.y === currentPiece.position.y;
            if (enPassantPiece) highlight = true;
          }

          const drag = piece?.team === props.teamTurn;

          board.push({
            isWhite: white_square,
            piece: image,
            highlight,
            drag
          });

          white_square = !white_square;
        }
      }

      return board;
    };

    const board = computed(() => renderBoard());


    return {
      chessboardRef,
      movePiece,
      grabPiece,
      dropPiece,
      handleTouch,
      board
    };
  }
});
</script>

<style scoped>
#chessboard {
  height: 80vmin;
  width: 80vmin;
  display: grid;
  grid-template-columns: repeat(8, 10vmin);
  grid-template-rows: repeat(8, 10vmin);
  outline: none;
  background-color: rgba(228, 2, 2, 1);
  touch-action: none;
}

#chessboard:hover {
  cursor: pointer;
}

.squareHighlight {
  position: relative;
  outline: none;
  display: flex;
  justify-content: center;
  align-items: center;
}

.squareHighlight::before {
  content: "";
  width: 3vmin;
  height: 3vmin;
  box-shadow: inset 0 0 3vmin 3vmin rgba(0, 0, 0, 0.5);
  border-radius: 50%;
}

.squareHighlightOccupied {
  border-radius: 3vmin;
}

@media screen and (max-width:800px) {
  #chessboard {
    height: 100vmin;
    width: 100vmin;
    grid-template-columns: repeat(8, 12.5vmin);
    grid-template-rows: repeat(8, 12.5vmin);
  }

  .piece {
    width: 12.5vmin;
    height: 12.5vmin;
  }
}
</style>