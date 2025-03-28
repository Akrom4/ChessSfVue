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
                                <template v-for="(move, index) in solutionMoves" :key="index">
                                    <span v-if="index % 2 === 0" class="move-number">{{ Math.floor(index / 2) + 1
                                    }}.</span>
                                    <span :class="['move', { 'active': index === currentMoveIndex }]"
                                        @click="goToMove(index)">
                                        {{ move }}
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Navigation controls -->
                        <div class="solution-navigation mt-4">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="goToStart" title="First position" class="nav-button">
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
        const isLoading = ref(false);
        const isInitialLoading = ref(true);
        const solutionMode = ref(false);
        const currentMoveIndex = ref(-1);
        const initialFen = ref("");
        const isPlaying = ref(false);
        const refereeKey = ref(0); // Used to force re-render of Referee component
        let playInterval: number | null = null;

        // Parse the moves string into an array of algebraic notation moves
        const solutionMoves = computed(() => {
            if (!moves.value) return [];
            return moves.value.split(' ');
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
                initialFen.value = puzzle.fen;
                fen.value = puzzle.fen;
                moves.value = puzzle.moves;

                // Reset UI state
                solutionMode.value = false;
                currentMoveIndex.value = -1;
                refereeKey.value++; // Force re-render
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
            // Reset to initial state for solution viewing
            fen.value = initialFen.value;
            currentMoveIndex.value = -1;
            refereeKey.value++; // Force re-render
        };

        // Navigation functions
        const goToStart = () => {
            currentMoveIndex.value = -1;
            fen.value = initialFen.value;
        };

        const goToEnd = () => {
            if (solutionMoves.value.length > 0) {
                currentMoveIndex.value = solutionMoves.value.length - 1;
                // Load the board with all moves applied
                playAllMoves();
            }
        };

        const goToPrev = () => {
            if (currentMoveIndex.value <= 0) {
                goToStart();
            } else {
                currentMoveIndex.value--;
                // Apply moves up to the current index
                playMovesToIndex();
            }
        };

        const goToNext = () => {
            if (currentMoveIndex.value < solutionMoves.value.length - 1) {
                currentMoveIndex.value++;
                // Apply moves up to the current index
                playMovesToIndex();
            }
        };

        const goToMove = (index: number) => {
            currentMoveIndex.value = index;
            playMovesToIndex();
        };

        // Function to play all moves in the sequence
        const playAllMoves = () => {
            // Instead of manually processing moves, we'll set the currentMoveIndex
            // which will inform the Referee component which moves to display
            currentMoveIndex.value = solutionMoves.value.length - 1;

            // We can simply reset the fen to trigger the Referee to re-render
            // with the updated currentMoveIndex
            const tempFen = fen.value;
            fen.value = '';
            setTimeout(() => {
                fen.value = tempFen;
            }, 0);
        };

        // Function to play moves up to a specific index
        const playMovesToIndex = () => {
            // For viewing moves in the solution panel, we'll use a different approach
            // We'll create a "solution" fen string that shows the board after playing
            // moves up to the current index

            try {
                const board = new Board();
                let currentBoard = board.fenReader(initialFen.value);

                // Only play moves if there are any to play and currentMoveIndex is valid
                if (solutionMoves.value.length > 0 && currentMoveIndex.value >= 0) {
                    // Play the moves up to the currentMoveIndex
                    for (let i = 0; i <= Math.min(currentMoveIndex.value, solutionMoves.value.length - 1); i++) {
                        const moveStr = solutionMoves.value[i];

                        // Convert from algebraic notation (e2e4) to positions
                        const from = new Position(
                            moveStr.charCodeAt(0) - 'a'.charCodeAt(0),
                            parseInt(moveStr[1]) - 1
                        );
                        const to = new Position(
                            moveStr.charCodeAt(2) - 'a'.charCodeAt(0),
                            parseInt(moveStr[3]) - 1
                        );

                        // Find the piece at the from position
                        const piece = currentBoard.pieces.find(p => p.samePosition(from));

                        if (piece) {
                            // Play the move
                            currentBoard.playMove(to, piece);
                            // Clone to update the board state
                            currentBoard = currentBoard.clone();
                        }
                    }
                }

                // Update the FEN to reflect the board after playing these moves
                fen.value = currentBoard.getFen();
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
                // Start from the beginning if at the end
                if (currentMoveIndex.value >= solutionMoves.value.length - 1) {
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

        watch(fen, (newFen) => {
            console.log('FEN changed to:', newFen);
        });

        // Clean up interval on component unmount
        onMounted(() => {
            loadNewPuzzle();

            return () => {
                if (playInterval) {
                    clearInterval(playInterval);
                }
            };
        });

        return {
            fen,
            moves,
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
            refereeKey
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
    gap: 0.5rem;
}

.move-number {
    color: #666;
    font-weight: bold;
    margin-right: 0.25rem;
}

.move {
    cursor: pointer;
    padding: 0.15rem 0.5rem;
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