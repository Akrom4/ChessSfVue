<template>
    <div class="puzzle-view">
        <div id="chess-app">
            <div class="game-container">
                <div v-if="isInitialLoading" class="loading">
                    Loading puzzle...
                </div>
                <div v-else class="chessboard-wrapper">
                    <Referee :key="refereeKey" :fen="fen" :moves="moves" :isPuzzle="!solutionMode" />
                </div>
            </div>

            <!-- Controls -->
            <div class="controls-wrap">
                <!-- Controls Panel -->
                <div v-if="!solutionMode" class="controls-container">
                    <div class="button-group">
                        <!-- Solution button -->
                        <button @click="showSolution" class="control-button">
                            <EyeIcon class="button-icon" />
                            <span>Voir la solution</span>
                        </button>

                        <!-- New puzzle button -->
                        <button @click="loadNewPuzzle" :disabled="isLoading" class="control-button">
                            <ArrowPathIcon class="button-icon" />
                            <span>{{ isLoading ? 'Chargement...' : 'Nouveau puzzle' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Solution Panel (shows when solution button is clicked) -->
                <div v-if="solutionMode" class="solution-container">
                    <div class="flex justify-between mb-4">
                        <h3 class="text-xl font-bold">Solution du puzzle</h3>
                    </div>

                    <div class="solution-content">
                        <!-- Solution viewer -->
                        <div class="solution-moves">
                            <div class="moves-list">
                                <!-- For black to play puzzles, show "1..." prefix first -->
                                <div v-if="isBlackToPlay" class="move-container">
                                    <span class="move-number">1</span><span class="ellipsis">...</span>
                                </div>

                                <template v-for="(formattedMove, index) in formattedSolutionMoves" :key="index">
                                    <!-- Skip displaying the first move (index 0) -->
                                    <div v-if="index > 0" class="move-container">
                                        <!-- Move numbering logic based on who starts -->
                                        <span v-if="shouldShowMoveNumber(index)" class="move-number">
                                            {{ getMoveNumber(index) }}
                                        </span>
                                        <span :class="['move', { 'active': index === currentMoveIndex }]"
                                            @click="goToMove(index)">
                                            {{ formattedMove }}
                                        </span>
                                    </div>
                                </template>
                            </div>
                            <div v-if="formattedSolutionMoves.length <= 1" class="text-gray-500 text-center py-2">
                                Aucun coup dans la solution
                            </div>
                        </div>

                        <!-- Navigation controls -->
                        <div class="solution-navigation mt-4">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="goToStart" title="Initial position" class="nav-button">
                                    <ChevronDoubleLeftIcon class="nav-icon" />
                                </button>
                                <button @click="goToPrev" title="Previous move" class="nav-button">
                                    <ChevronLeftIcon class="nav-icon" />
                                </button>
                                <button @click="playMoves" title="Play solution" class="nav-button">
                                    <PlayIcon v-if="!isPlaying" class="nav-icon" />
                                    <StopIcon v-else class="nav-icon" />
                                </button>
                                <button @click="goToNext" title="Next move" class="nav-button">
                                    <ChevronRightIcon class="nav-icon" />
                                </button>
                                <button @click="goToEnd" title="Last position" class="nav-button">
                                    <ChevronDoubleRightIcon class="nav-icon" />
                                </button>
                            </div>
                        </div>

                        <!-- New puzzle button at bottom of solution panel -->
                        <div class="mt-4">
                            <button @click="loadNewPuzzle" :disabled="isLoading" class="control-button">
                                <ArrowPathIcon class="button-icon" />
                                <span>{{ isLoading ? 'Chargement...' : 'Nouveau puzzle' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted, watch, computed } from 'vue';
import Referee from '@/chess/components/Referee.vue';
import { Board } from '@/chess/models/Board';
import { Pgn } from '@/chess/models/Pgn';
import { Position } from '@/chess/models/Position';
import {
    ChevronDoubleLeftIcon,
    ChevronDoubleRightIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    PlayIcon,
    StopIcon,
    EyeIcon,
    ArrowPathIcon
} from "@heroicons/vue/24/outline";

export default defineComponent({
    name: 'PuzzleView',
    components: {
        Referee,
        ChevronDoubleLeftIcon,
        ChevronDoubleRightIcon,
        ChevronLeftIcon,
        ChevronRightIcon,
        PlayIcon,
        StopIcon,
        EyeIcon,
        ArrowPathIcon
    },
    setup() {
        const fen = ref("");
        const moves = ref("");
        const originalMoves = ref(""); // Store the original moves string
        const isLoading = ref(false);
        const isInitialLoading = ref(true);
        const solutionMode = ref(false);
        const currentMoveIndex = ref(-1);
        const initialFen = ref("");
        const isPlaying = ref(false);
        const refereeKey = ref(0); // Used to force re-render of Referee component
        let playInterval: number | null = null;
        const isBlackToPlay = ref(false);

        // Parse the moves string into an array of algebraic notation moves
        const solutionMoves = computed(() => {
            // Use originalMoves to ensure we always have the full solution
            if (!originalMoves.value) {
                console.log('solutionMoves: No original moves available');
                return [];
            }

            const movesArray = originalMoves.value.split(' ');
            console.log(`solutionMoves: Parsed ${movesArray.length} moves from "${originalMoves.value}"`);
            return movesArray;
        });

        // Formatted moves for display in the solution panel
        const formattedSolutionMoves = computed(() => {
            if (solutionMoves.value.length === 0) return [];

            const moves: string[] = new Array(solutionMoves.value.length);
            let currentBoard = new Board().fenReader(initialFen.value);

            console.log("Formatting solution moves from FEN:", initialFen.value);

            // Format each move using the current board state
            for (let i = 0; i < solutionMoves.value.length; i++) {
                const moveStr = solutionMoves.value[i];
                moves[i] = formatMove(moveStr, currentBoard);
                console.log(`Move ${i}: ${moveStr} → ${moves[i]}`);

                // Apply the move to the board for the next iteration
                const from = new Position(
                    moveStr.charCodeAt(0) - 'a'.charCodeAt(0),
                    parseInt(moveStr[1]) - 1
                );
                const to = new Position(
                    moveStr.charCodeAt(2) - 'a'.charCodeAt(0),
                    parseInt(moveStr[3]) - 1
                );

                const piece = currentBoard.pieces.find(p => p.samePosition(from));
                if (piece) {
                    currentBoard.playMove(to, piece);
                    currentBoard = currentBoard.clone();
                }
            }

            return moves;
        });

        // Function to load a new puzzle
        const loadNewPuzzle = async () => {
            try {
                isLoading.value = true;
                // Stop any ongoing solution playback
                if (playInterval) {
                    clearInterval(playInterval);
                    playInterval = null;
                    isPlaying.value = false;
                }

                const response = await fetch('http://localhost:8000/api/puzzles/random');
                const puzzle = await response.json();
                console.log('Loaded puzzle FEN:', puzzle.fen);
                console.log('Loaded puzzle moves:', puzzle.moves);
                initialFen.value = puzzle.fen;
                fen.value = puzzle.fen;
                moves.value = puzzle.moves;
                originalMoves.value = puzzle.moves; // Store the original moves
                console.log('Set originalMoves to:', originalMoves.value);

                // Determine if black plays first
                const fenParts = puzzle.fen.split(' ');
                // If white to move in FEN, user plays as black (so we should use black-to-play notation)
                // If black to move in FEN, user plays as white (so we should use white-to-play notation)
                isBlackToPlay.value = fenParts[1] === 'w'; // Reversed from FEN since user plays opposite
                console.log(`User is playing as: ${isBlackToPlay.value ? 'black' : 'white'}`);
                console.log(`Should show "1...": ${isBlackToPlay.value}`);

                // Reset UI state
                solutionMode.value = false;
                currentMoveIndex.value = -1;

                // Force a re-render only when loading new puzzle
                refereeKey.value++;
            } catch (error) {
                console.error('Error loading puzzle:', error);
            } finally {
                isLoading.value = false;
                isInitialLoading.value = false;
            }
        };

        // Show solution (one-way toggle)
        const showSolution = () => {
            solutionMode.value = true;

            // We want to show the solution after the first move
            // So we need to apply the first move to the board
            if (solutionMoves.value.length > 0) {
                // Set currentMoveIndex to 0 (the first move)
                // This will display the position AFTER the first move
                currentMoveIndex.value = 0;

                // Apply the first move to the board
                playMovesToIndex();

                // This ensures we're showing the position after the first move
                // where the user needs to find the second move
                console.log('Solution mode: Showing board after first move');
                console.log(`Solution contains ${solutionMoves.value.length} moves: ${originalMoves.value}`);
                console.log(`User plays as: ${isBlackToPlay.value ? 'black' : 'white'}`);
            } else {
                // If no moves in the solution, just show the initial position
                fen.value = initialFen.value;
                currentMoveIndex.value = -1;
            }
        };

        // Navigation functions for solution mode
        const goToStart = () => {
            // In solution mode, we want to start at the position after the first move
            // since that's what the user is trying to solve
            if (solutionMode.value && solutionMoves.value.length > 0) {
                currentMoveIndex.value = 0; // First move
                playMovesToIndex();
            } else {
                // Non-solution mode or empty solution
                currentMoveIndex.value = -1;
                fen.value = initialFen.value;
            }
        };

        const goToEnd = () => {
            if (solutionMoves.value.length > 0) {
                currentMoveIndex.value = solutionMoves.value.length - 1;
                // Apply all moves to show final position
                playMovesToIndex();
            }
        };

        const goToPrev = () => {
            if (solutionMode.value) {
                // In solution mode, we don't go before the first move (index 0)
                if (currentMoveIndex.value <= 0) {
                    currentMoveIndex.value = 0;
                    playMovesToIndex();
                } else {
                    currentMoveIndex.value--;
                    playMovesToIndex();
                }
            } else {
                // Regular mode behavior
                if (currentMoveIndex.value <= 0) {
                    currentMoveIndex.value = -1;
                    fen.value = initialFen.value;
                } else {
                    currentMoveIndex.value--;
                    playMovesToIndex();
                }
            }
        };

        const goToNext = () => {
            if (currentMoveIndex.value < solutionMoves.value.length - 1) {
                currentMoveIndex.value++;
                playMovesToIndex();
            }
        };

        const goToMove = (index: number) => {
            console.log(`Going to move index: ${index}`);
            if (index >= 0 && index < solutionMoves.value.length) {
                currentMoveIndex.value = index;
                playMovesToIndex();
            }
        };

        // Function to play all moves in the sequence
        const playAllMoves = () => {
            // Instead of manually processing moves, we'll set the currentMoveIndex
            // which will inform the Referee component which moves to display
            currentMoveIndex.value = solutionMoves.value.length - 1;

            // Apply all moves up to the last one
            playMovesToIndex();
        };

        // Function to play moves up to a specific index
        const playMovesToIndex = () => {
            // For viewing moves in the solution panel, we'll use a different approach
            // We'll create a "solution" fen string that shows the board after playing
            // moves up to the current index

            console.log(`Playing moves to index: ${currentMoveIndex.value}`);
            console.log(`Total solution moves: ${solutionMoves.value.length} - ${JSON.stringify(solutionMoves.value)}`);

            try {
                const board = new Board();
                let currentBoard = board.fenReader(initialFen.value);

                // Track the last move positions for highlighting
                let lastMoveFrom = null;
                let lastMoveTo = null;

                // Only play moves if there are any to play and currentMoveIndex is valid
                if (solutionMoves.value.length > 0 && currentMoveIndex.value >= 0) {
                    // Play the moves up to the currentMoveIndex
                    for (let i = 0; i <= Math.min(currentMoveIndex.value, solutionMoves.value.length - 1); i++) {
                        const moveStr = solutionMoves.value[i];
                        console.log(`Playing move ${i}: ${moveStr}`);

                        // Convert from algebraic notation (e2e4) to positions
                        const from = new Position(
                            moveStr.charCodeAt(0) - 'a'.charCodeAt(0),
                            parseInt(moveStr[1]) - 1
                        );
                        const to = new Position(
                            moveStr.charCodeAt(2) - 'a'.charCodeAt(0),
                            parseInt(moveStr[3]) - 1
                        );

                        // Save the last move positions
                        lastMoveFrom = from;
                        lastMoveTo = to;

                        // Find the piece at the from position
                        const piece = currentBoard.pieces.find(p => p.samePosition(from));

                        if (piece) {
                            // Play the move
                            currentBoard.playMove(to, piece);
                            // Clone to update the board state
                            currentBoard = currentBoard.clone();
                        } else {
                            console.error(`No piece found at position ${from.x},${from.y} for move ${moveStr}`);
                        }
                    }
                }

                // Update the FEN to reflect the board after playing these moves
                fen.value = currentBoard.getFen();
                console.log(`Updated FEN to: ${fen.value}`);

                // If we're in solution mode, add the move highlight information for the Referee component
                if (solutionMode.value && lastMoveFrom && lastMoveTo) {
                    // Convert position x/y back to algebraic notation
                    const files = 'abcdefgh';
                    const fromFile = files[lastMoveFrom.x];
                    const fromRank = lastMoveFrom.y + 1;
                    const toFile = files[lastMoveTo.x];
                    const toRank = lastMoveTo.y + 1;

                    // Set the current move for highlighting in the Referee component
                    // This doesn't affect solutionMoves which uses originalMoves
                    moves.value = `${fromFile}${fromRank}${toFile}${toRank}`;
                    console.log(`Highlighting move: ${moves.value}`);
                }
            } catch (error) {
                console.error('Error playing moves:', error);
            }
        };

        // Animated playthrough of all moves
        const playMoves = () => {
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
                // Start from the first move (index 0) if we're at the start or end
                if (currentMoveIndex.value < 0 || currentMoveIndex.value >= solutionMoves.value.length - 1) {
                    goToStart();
                }

                // Play moves with 1-second interval
                playInterval = window.setInterval(() => {
                    if (currentMoveIndex.value < solutionMoves.value.length - 1) {
                        goToNext();
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

        // Helper functions for move numbering in the solution panel
        const shouldShowMoveNumber = (index: number) => {
            if (isBlackToPlay.value) {
                // For black to play puzzles:
                // Show number for white's moves (even indices after skipping first move)
                return index % 2 === 0;
            } else {
                // For white to play puzzles:
                // Show number for white's moves (odd indices after skipping first move)
                return index % 2 === 1;
            }
        };

        const getMoveNumber = (index: number) => {
            if (isBlackToPlay.value) {
                // For black to play puzzles, this is actually white's move after Black's 1st move
                // So we start with 2. for white's move (even indices after skipping first move)
                return Math.floor(index / 2) + 1 + '.';
            } else {
                // For white to play puzzles, index 1 is move 1, index 3 is move 2, etc.
                return Math.floor((index + 1) / 2) + '.';
            }
        };

        // Create a simplified check detection based on actual board positions
        const isKingInCheck = (board: Board, kingTeam: string): boolean => {
            // Find the king
            const king = board.pieces.find(p => p.type === 'k' && p.team === kingTeam);
            if (!king) return false;

            // Check if any opponent piece can attack the king
            for (const piece of board.pieces) {
                if (piece.team !== kingTeam) {
                    const moves = board.getValidMoves(piece);
                    if (moves.some(move => move.x === king.position.x && move.y === king.position.y)) {
                        return true;
                    }
                }
            }

            return false;
        };

        // Helper function to convert algebraic notation (e2e4) to readable notation (Ne5)
        const formatMove = (moveStr: string, boardBeforeMove: Board): string => {
            if (!moveStr || moveStr.length < 4) return moveStr;

            // Extract positions from algebraic notation
            const from = new Position(
                moveStr.charCodeAt(0) - 'a'.charCodeAt(0),
                parseInt(moveStr[1]) - 1
            );
            const to = new Position(
                moveStr.charCodeAt(2) - 'a'.charCodeAt(0),
                parseInt(moveStr[3]) - 1
            );

            // Find the piece at the from position
            const piece = boardBeforeMove.pieces.find(p => p.samePosition(from));
            if (!piece) return moveStr;

            const files = 'abcdefgh';
            const toFile = files[to.x];
            const toRank = to.y + 1;

            // Check for castling (King moving 2 squares)
            if (piece.type === 'k' && Math.abs(to.x - from.x) === 2) {
                // Kingside castling (O-O)
                if (to.x > from.x) {
                    return 'O-O';
                }
                // Queenside castling (O-O-O)
                else {
                    return 'O-O-O';
                }
            }

            // Use piece type for display, except pawn which just shows the destination
            let pieceSymbol = '';
            if (piece.type !== 'p') {
                // Map piece types to symbols
                const pieceSymbols: Record<string, string> = {
                    'r': 'T', // Rook (Tour in French)
                    'n': 'C', // Knight (Cavalier in French)
                    'b': 'F', // Bishop (Fou in French)
                    'q': 'D', // Queen (Dame in French)
                    'k': 'R'  // King (Roi in French)
                };
                pieceSymbol = pieceSymbols[piece.type] || piece.type.toUpperCase();
            }

            // Check if it's a capture
            const isCapture = boardBeforeMove.squareIsOccupiedByOpp(to, piece.team);
            const captureSymbol = isCapture ? 'x' : '';

            // Check for ambiguity (multiple pieces of same type could move to target)
            let disambiguation = '';
            if (piece.type !== 'p' && piece.type !== 'k') {
                const otherPieces = boardBeforeMove.pieces.filter(p =>
                    p.type === piece.type &&
                    p.team === piece.team &&
                    !p.samePosition(from)
                );

                for (const otherPiece of otherPieces) {
                    const validMoves = boardBeforeMove.getValidMoves(otherPiece);
                    if (validMoves.some(m => m.x === to.x && m.y === to.y)) {
                        // Need disambiguation
                        if (otherPiece.position.x !== from.x) {
                            // File is sufficient
                            disambiguation = files[from.x];
                        } else if (otherPiece.position.y !== from.y) {
                            // Rank is needed
                            disambiguation = String(from.y + 1);
                        } else {
                            // Both file and rank
                            disambiguation = files[from.x] + (from.y + 1);
                        }
                        break;
                    }
                }
            }

            // Create a clone of the board to test for check/checkmate
            const testBoard = boardBeforeMove.clone();
            const testPiece = testBoard.pieces.find(p => p.samePosition(from));
            let suffix = '';

            if (testPiece) {
                // Make the move on the test board
                const moveResult = testBoard.playMove(to, testPiece);

                if (moveResult) {
                    // Check if opponent's king is in check after the move
                    const opposingTeam = piece.team === 'w' ? 'b' : 'w';
                    const isCheck = isKingInCheck(testBoard, opposingTeam);

                    if (isCheck) {
                        // Check if it's checkmate by seeing if opponent has any valid moves
                        let isCheckmate = true;

                        // Try all opponent's pieces for any valid move
                        for (const p of testBoard.pieces) {
                            if (p.team === opposingTeam) {
                                const moves = testBoard.getValidMoves(p);

                                // For each possible move, check if it gets out of check
                                for (const m of moves) {
                                    const tempBoard = testBoard.clone();
                                    const tempPiece = tempBoard.pieces.find(tp => tp.samePosition(p.position));

                                    if (tempPiece && tempBoard.playMove(m, tempPiece)) {
                                        // If this move doesn't leave the king in check, it's not checkmate
                                        if (!isKingInCheck(tempBoard, opposingTeam)) {
                                            isCheckmate = false;
                                            break;
                                        }
                                    }
                                }

                                if (!isCheckmate) break;
                            }
                        }

                        suffix = isCheckmate ? '#' : '+';
                    }
                }
            }

            // Format based on piece type
            let baseNotation = '';
            if (piece.type === 'p') {
                // For pawns, show captures differently
                if (isCapture) {
                    baseNotation = `${files[from.x]}${captureSymbol}${toFile}${toRank}`;
                } else {
                    baseNotation = `${toFile}${toRank}`;
                }
            } else {
                // For other pieces, show the piece symbol with disambiguation if needed
                baseNotation = `${pieceSymbol}${disambiguation}${captureSymbol}${toFile}${toRank}`;
            }

            return baseNotation + suffix;
        };

        watch(fen, (newFen) => {
            console.log('FEN changed to:', newFen);
        });

        // Clean up interval on component unmount
        onMounted(() => {
            loadNewPuzzle();

            // Add cleanup function
            return () => {
                if (playInterval) {
                    clearInterval(playInterval);
                }
            };
        });

        return {
            fen,
            moves,
            originalMoves,
            isLoading,
            isInitialLoading,
            loadNewPuzzle,
            solutionMode,
            solutionMoves,
            currentMoveIndex,
            goToStart,
            goToEnd,
            goToPrev,
            goToNext,
            goToMove,
            playMoves,
            showSolution,
            isPlaying,
            refereeKey,
            isBlackToPlay,
            shouldShowMoveNumber,
            getMoveNumber,
            formatMove,
            formattedSolutionMoves,
            isKingInCheck
        };
    }
});
</script>

<style scoped>
.puzzle-view {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
    padding: 1rem;
    width: 100%;
    min-height: 100%;
}

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
}

.chessboard-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
}

.controls-wrap {
    display: flex;
    flex-direction: column;
    margin-left: 1rem;
    min-width: 16rem;
    max-width: 20rem;
}

.controls-container,
.solution-container {
    background-color: #f5f5f5;
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.1);
    width: 100%;
}

.button-group {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.control-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    font-weight: 500;
    background-color: var(--color-nav-start);
    color: white;
    transition: all 0.2s;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.15);
}

.control-button:hover:not(:disabled) {
    background-color: var(--color-nav-hover);
    transform: translateY(-0.125rem);
}

.control-button:active:not(:disabled) {
    background-color: var(--color-nav-end);
    transform: translateY(0);
}

.control-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.button-icon {
    width: 1.25rem;
    height: 1.25rem;
}

.solution-moves {
    max-height: 18.75rem;
    overflow-y: auto;
    margin-bottom: 1rem;
    background: white;
    padding: 1rem;
    border-radius: 0.25rem;
    border: 0.0625rem solid #e0e0e0;
}

.moves-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    align-items: center;
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

.ellipsis {
    padding: 0.1rem 0;
    font-weight: bold;
}

.move {
    cursor: pointer;
    padding: 0.1rem 0.35rem;
    border-radius: 0.25rem;
    transition: background-color 0.2s;
}

.move:hover {
    background-color: #eee;
}

.move.active {
    background-color: #4CAF50;
    color: white;
}

.loading {
    font-size: 1.2rem;
    color: #666;
}

.nav-button {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    background-color: var(--color-nav-start);
    color: var(--color-custom-yellow);
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.15);
    transition: all 0.2s;
}

.nav-button:hover {
    background-color: var(--color-nav-hover);
    transform: translateY(-0.125rem);
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
}

.nav-button:active {
    background-color: var(--color-nav-end);
    transform: translateY(0);
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.15);
}

.nav-button:focus {
    outline: none;
}

.nav-icon {
    width: 1.25rem;
    height: 1.25rem;
}

/* Responsive layout */
@media (max-width: 768px) {
    #chess-app {
        flex-direction: column;
    }

    .controls-wrap {
        margin: 1rem 0 0 0;
        max-width: none;
        width: 100%;
    }

    .button-group {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .control-button {
        flex: 1;
        min-width: 8rem;
    }
}

@media (max-width: 480px) {
    .puzzle-view {
        padding: 0.5rem;
    }

    .button-group {
        flex-direction: column;
    }
}

@media (max-width: 768px) {
    .chessboard-wrapper {
        width: 100%;
        height: auto;
        aspect-ratio: 1/1;
        margin: 0 auto;
        max-width: 80vmin;
    }

    :deep(.chessboard-wrapper #chessboard) {
        width: 100% !important;
        height: 100% !important;
        max-width: 100%;
    }
}

@media (max-width: 480px) {
    .chessboard-wrapper {
        max-width: 95vmin;
        margin: 0 auto;
    }
}
</style>