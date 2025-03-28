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
    <!-- Pass the computed boardPieces -->
    <Chessboard :playMove="playMove" :pieces="boardPieces" :setOrientation="setOrientation"
      :getOrientation="getOrientation" :teamTurn="teamTurn" :lastMoveSquares="lastMoveSquares" :isPuzzle="isPuzzle"
      :isPuzzleComplete="isPuzzleComplete" />
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, computed, onMounted, watch } from 'vue';
import { initialBoard, PieceType, TeamType } from '../Constants';
import { Board } from '../models/Board';
import { Piece, Position } from '../models';
import Chessboard from './Chessboard.vue';

import whiteRook from '../assets/images/chess_wr.svg';
import whiteKnight from '../assets/images/chess_wn.svg';
import whiteBishop from '../assets/images/chess_wb.svg';
import whiteQueen from '../assets/images/chess_wq.svg';
import blackRook from '../assets/images/chess_br.svg';
import blackKnight from '../assets/images/chess_bn.svg';
import blackBishop from '../assets/images/chess_bb.svg';
import blackQueen from '../assets/images/chess_bq.svg';

export default defineComponent({
  name: 'Referee',
  props: {
    fen: String,
    moves: {
      type: String,
      default: ''
    },
    isPuzzle: {
      type: Boolean,
      default: false
    }
  },
  components: { Chessboard },
  setup(props) {
    const board = ref<Board>(initialBoard);
    const boardPieces = computed(() => {
      return board.value.pieces;
    });

    const promotion = ref<Piece | null>(null);
    const modalRef = ref<HTMLElement | null>(null);
    const modalBodyRef = ref<HTMLElement | null>(null);
    const orientation = ref('white');
    const teamTurn = ref<'w' | 'b'>(TeamType.W);
    const lastMoveSquares = ref<Position[]>([]);
    const currentMoveIndex = ref(0);
    const moveSequence = ref<string[]>([]);
    const isPuzzleComplete = computed(() => {
      return props.isPuzzle && currentMoveIndex.value >= moveSequence.value.length
    });

    // Parse moves string into array of moves
    watch(() => props.moves, (newMoves) => {
      if (newMoves) {
        moveSequence.value = newMoves.split(' ');
      }
    }, { immediate: true });

    // Function to set board orientation based on the puzzle
    function setPuzzleOrientation() {
      if (props.isPuzzle && moveSequence.value.length > 0) {
        const firstMoveTurn = teamTurn.value; // Current turn is who plays first move
        const playerColor = firstMoveTurn === TeamType.W ? 'black' : 'white'; // Player plays second, so opposite color
        setOrientation(playerColor);
        console.log(`Setting puzzle orientation to ${playerColor} (player's color)`);
      }
    }

    onMounted(() => {
      if (props.fen) {
        board.value = board.value.fenReader(props.fen);
        teamTurn.value = board.value.getTurn() as 'w' | 'b';
      }
      board.value.validMoves();

      // Set player's color at the bottom for puzzles
      setPuzzleOrientation();

      // If this is a puzzle and we have moves, play the first move automatically
      if (props.isPuzzle && moveSequence.value.length > 0) {
        playNextMove();
      }
    });

    watch(() => props.fen, (newFen) => {
      if (newFen) {
        console.log('Referee received FEN:', newFen);
        board.value = board.value.fenReader(newFen);
        teamTurn.value = board.value.getTurn() as 'w' | 'b';
        board.value.validMoves();

        // Reset move sequence when FEN changes
        currentMoveIndex.value = 0;
        lastMoveSquares.value = [];


        // Set player's color at the bottom for puzzles
        setPuzzleOrientation();

        // If this is a puzzle and we have moves, play the first move automatically
        if (props.isPuzzle && moveSequence.value.length > 0) {
          playNextMove();
        }
      }
    }, { immediate: true });

    function setOrientation(color: string) {
      orientation.value = color;
    }
    function getOrientation() {
      return orientation.value;
    }
    function updatePossibleMoves() {
      board.value.validMoves();
    }

    // Convert algebraic notation to Position objects
    function algebraicToPosition(algebraic: string): { from: Position, to: Position } {
      const from = new Position(
        algebraic.charCodeAt(0) - 'a'.charCodeAt(0),
        parseInt(algebraic[1]) - 1
      );
      const to = new Position(
        algebraic.charCodeAt(2) - 'a'.charCodeAt(0),
        parseInt(algebraic[3]) - 1
      );
      return { from, to };
    }

    // Play the next move in the sequence
    async function playNextMove() {
      if (currentMoveIndex.value >= moveSequence.value.length) return;

      const move = moveSequence.value[currentMoveIndex.value];
      const { from, to } = algebraicToPosition(move);

      // Highlight the squares
      lastMoveSquares.value = [from, to];

      // Find the piece to move
      const piece = board.value.pieces.find(p => p.samePosition(from));
      if (!piece) return;

      // Animate the move
      await new Promise(resolve => setTimeout(resolve, 500)); // Wait for highlight to be visible

      // Perform the move
      const validMove = board.value.playMove(to, piece);
      if (validMove) {
        board.value = board.value.clone();
        teamTurn.value = teamTurn.value === TeamType.W ? TeamType.B : TeamType.W;
        currentMoveIndex.value++;

        // Check if puzzle is complete after CPU move
        if (currentMoveIndex.value >= moveSequence.value.length) {
          // Keep the last move square visible for the checkmark
          console.log('Puzzle completed by CPU move! Showing checkmark on last move.');
          // No need to clear lastMoveSquares - keep it to show the checkmark
        } else {
          // For non-final moves, don't clear the highlights
          // They will be cleared when the player makes their move
        }
      }
    }

    function playMove(playedPiece: Piece, destination: Position, chessboard: HTMLElement) {
      // If this is a puzzle, validate the move
      if (props.isPuzzle) {
        const expectedMove = moveSequence.value[currentMoveIndex.value];
        if (!expectedMove) return false;

        const { to } = algebraicToPosition(expectedMove);
        if (!destination.samePosition(to)) {
          return false;
        }

        // Move is correct, play it and the next move
        const validMove = board.value.playMove(destination, playedPiece);
        if (validMove) {
          board.value = board.value.clone();
          teamTurn.value = teamTurn.value === TeamType.W ? TeamType.B : TeamType.W;

          // Now that this move is validated, clear the previous highlights
          lastMoveSquares.value = [];

          currentMoveIndex.value++;

          // Check if puzzle is complete
          if (currentMoveIndex.value >= moveSequence.value.length) {
            // For the completed puzzle, set the last move square to the destination
            // This ensures the checkmark will appear on the last move
            lastMoveSquares.value = [destination];
            console.log('Puzzle completed! Showing checkmark on last move.');
            return validMove;
          }

          // Play the next move automatically
          setTimeout(() => {
            playNextMove();
          }, 300);
        }
        return validMove;
      }

      // Regular move handling for non-puzzle mode
      const validMove = board.value.playMove(destination, playedPiece);
      board.value = board.value.clone();

      const promotionRow = playedPiece.team === TeamType.W ? 7 : 0;
      if (playedPiece.position.y === promotionRow && playedPiece.isPawn) {
        modalPosition(chessboard, playedPiece, playedPiece.position.x);
        promotion.value = playedPiece;
        modalRef.value?.classList.remove('hidden');
      }
      teamTurn.value = validMove
        ? (teamTurn.value === TeamType.W ? TeamType.B : TeamType.W)
        : teamTurn.value;
      return validMove;
    }

    function promote(type: keyof typeof PieceType) {
      board.value.pieces = board.value.pieces.map((piece: Piece) => {
        if (piece.samePosition(promotion.value!.position)) {
          return board.value.createPromotedPiece(type, promotion.value!.position, piece.team);
        }
        return piece;
      });
      updatePossibleMoves();
      board.value = board.value.clone();
      modalRef.value?.classList.add('hidden');
    }

    function promoteColor() {
      if (!promotion.value) return TeamType.W;
      return promotion.value.team;
    }

    function modalPosition(chessboard: HTMLElement, piece: Piece, x: number) {
      const squareSize = chessboard.clientWidth / 8; // assuming 8 columns
      const squareTop = chessboard.offsetTop;
      let modalTop: number;
      let flexDirection: string;
      let squareLeft: number;
      if (orientation.value === 'white') {
        squareLeft = chessboard.offsetLeft + x * squareSize;
        modalTop = piece.team === TeamType.W ? squareTop : squareTop + 4 * squareSize;
        flexDirection = piece.team === TeamType.W ? 'column' : 'column-reverse';
      } else {
        squareLeft = chessboard.offsetLeft + (8 - 1 - x) * squareSize;
        modalTop = piece.team === TeamType.W ? squareTop + 4 * squareSize : squareTop;
        flexDirection = piece.team === TeamType.W ? 'column-reverse' : 'column';
      }
      if (modalBodyRef.value) {
        modalBodyRef.value.style.left = `${squareLeft}px`;
        modalBodyRef.value.style.top = `${modalTop}px`;
        modalBodyRef.value.style.flexDirection = flexDirection;
      }
    }

    return {
      board,
      boardPieces,
      promotion,
      modalRef,
      modalBodyRef,
      orientation,
      teamTurn,
      lastMoveSquares,
      isPuzzleComplete,
      setOrientation,
      getOrientation,
      updatePossibleMoves,
      playMove,
      promote,
      promoteColor,
      modalPosition,
      PieceType,
      TeamType,
      whiteQueen,
      blackQueen,
      whiteKnight,
      blackKnight,
      whiteRook,
      blackRook,
      whiteBishop,
      blackBishop,
      isPuzzle: props.isPuzzle
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
