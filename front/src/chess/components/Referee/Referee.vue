<template>
  <div>
    <div id="promotion" class="hidden" ref="modalRef">
      <div class="modalBody" ref="modalBodyRef">
        <div id="queen" @click="promote('QUEEN')">
          <div :style="{ backgroundImage: `url(${promoteColor() === TeamType.W ? whiteQueen : blackQueen})` }" />
        </div>
        <div id="knight" @click="promote('KNIGHT')">
          <div :style="{ backgroundImage: `url(${promoteColor() === TeamType.W ? whiteKnight : blackKnight})` }" />
        </div>
        <div id="rook" @click="promote('ROOK')">
          <div :style="{ backgroundImage: `url(${promoteColor() === TeamType.W ? whiteRook : blackRook})` }" />
        </div>
        <div id="bishop" @click="promote('BISHOP')">
          <div :style="{ backgroundImage: `url(${promoteColor() === TeamType.W ? whiteBishop : blackBishop})` }" />
        </div>
      </div>
    </div>
    <Chessboard :playMove="playMove" :pieces="board.pieces" :setOrientation="setOrientation"
      :getOrientation="getOrientation" :teamTurn="teamTurn" />
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, reactive, onMounted, watch } from 'vue';
import { column, initialBoard, PieceType, TeamType } from '../../Constants';
import { Piece, Position } from '../../models';
import Chessboard from '../../components/Chessboard/Chessboard.vue';

import whiteRook from '../../assets/images/chess_wr.svg';
import whiteKnight from '../../assets/images/chess_wn.svg';
import whiteBishop from '../../assets/images/chess_wb.svg';
import whiteQueen from '../../assets/images/chess_wq.svg';
import blackRook from '../../assets/images/chess_br.svg';
import blackKnight from '../../assets/images/chess_bn.svg';
import blackBishop from '../../assets/images/chess_bb.svg';
import blackQueen from '../../assets/images/chess_bq.svg';

export default defineComponent({
  name: 'Referee',
  props: {
    fen: String
  },
  components: {
    Chessboard
  },
  setup(props) {
    const board = reactive(initialBoard);
    const promotion = ref<Piece | null>(null);
    const modalRef = ref<HTMLElement | null>(null);
    const modalBodyRef = ref<HTMLElement | null>(null);
    const orientation = ref('white');
    const teamTurn = ref<'w' | 'b'>(TeamType.W);

    onMounted(() => {
      updatePossibleMoves();
    });

    watch(() => props.fen, (newFen) => {
      if (newFen) {
        readFen(newFen);
      }
    });

    function setOrientation(color: string) {
      orientation.value = color;
    }

    function getOrientation() {
      return orientation.value;
    }

    function updatePossibleMoves() {
      board.validMoves();
    }

    function playMove(playedPiece: Piece, destination: Position, chessboard: HTMLElement) {
      let validMove = false;
      validMove = board.playMove(destination, playedPiece);
      board.clone();
      const promotionRow = playedPiece.team === TeamType.W ? 7 : 0;
      if (playedPiece.position.y === promotionRow && playedPiece.isPawn) {
        modalPosition(chessboard, playedPiece, playedPiece.position.x);
        promotion.value = playedPiece;
        modalRef.value?.classList.remove('hidden');
      }
      teamTurn.value = validMove ? (teamTurn.value === TeamType.W ? TeamType.B : TeamType.W) : teamTurn.value;
      return validMove;
    }

    function promote(type: keyof typeof PieceType) {
      board.pieces = board.pieces.reduce((results, piece) => {
        if (piece.samePosition(promotion.value!.position)) {
          const newPiece = new Piece(promotion.value!.position, type, piece.team);
          results.push(newPiece);
        } else {
          results.push(piece);
        }
        return results;
      }, [] as Piece[]);
      updatePossibleMoves();
      board.clone();
      modalRef.value?.classList.add('hidden');
    }

    function promoteColor() {
      if (!promotion.value) return TeamType.W;
      return promotion.value.team;
    }

    function modalPosition(chessboard: HTMLElement, piece: Piece, x: number) {
      const squareSize = chessboard.clientWidth / column.length;
      const squareTop = chessboard.offsetTop;
      let modalTop;
      let flexDirection;
      let squareLeft;
      if (orientation.value === 'white') {
        squareLeft = chessboard.offsetLeft + x * squareSize;
        modalTop = piece.team === TeamType.W ? squareTop : squareTop + 4 * squareSize;
        flexDirection = piece.team === TeamType.W ? 'column' : 'column-reverse';
      } else {
        squareLeft = chessboard.offsetLeft + (column.length - 1 - x) * squareSize;
        modalTop = piece.team === TeamType.W ? squareTop + 4 * squareSize : squareTop;
        flexDirection = piece.team === TeamType.W ? 'column-reverse' : 'column';
      }
      modalBodyRef.value!.style.left = `${squareLeft}px`;
      modalBodyRef.value!.style.top = `${modalTop}px`;
      modalBodyRef.value!.style.flexDirection = flexDirection;
    }

    function readFen(fen: string) {
      const newBoardState = board.fenReader(fen);
      newBoardState.validMoves();
      teamTurn.value = newBoardState.getTurn() as 'w' | 'b';
      board.clone();
    }

    return {
      board,
      promotion,
      modalRef,
      modalBodyRef,
      orientation,
      teamTurn,
      setOrientation,
      getOrientation,
      updatePossibleMoves,
      playMove,
      promote,
      promoteColor,
      modalPosition,
      readFen,
      PieceType,
      TeamType,
      whiteQueen,
      blackQueen,
      whiteKnight,
      blackKnight,
      whiteRook,
      blackRook,
      whiteBishop,
      blackBishop
    };
  }
});
</script>

<style scoped>
#promotion {
  position: absolute;
  top: 0px;
  left: 0px;
  right: 0px;
  bottom: 0px;
}

#promotion.hidden {
  display: none;
}

#promotion>.modalBody {
  position: absolute;
  display: flex;
  justify-content: space-between;
  flex-direction: column;
  height: 40vmin;
  width: 10vmin;
  background-color: rgba(0, 0, 0, 0.6);
}

#promotion>.modalBody>div {
  height: 10vmin;
  width: 10vmin;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 50%;
  background-color: rgb(208, 204, 208, 1);
}

#promotion>.modalBody>div>div {
  height: 9vmin;
  width: 9vmin;
  background-repeat: no-repeat;
  background-position: center;
  background-size: contain;
}

#promotion>.modalBody>div:hover {
  background-image: radial-gradient(rgb(208, 204, 208, 1) 0%, transparent 0%, rgb(224, 71, 51) 100%);
  border-radius: 0%;
}

@media screen and (max-width:800px) {
  #promotion>.modalBody {
    height: 50vmin;
    width: 12.5vmin;
  }

  #promotion>.modalBody>div {
    height: 12.5vmin;
    width: 12.5vmin;
  }

  #promotion>.modalBody>div>div {
    height: 10vmin;
    width: 10vmin;
  }
}
</style>
