<!-- StockfishAnalysis.vue -->
<template>
    <div class="stockfish-analysis" :class="{ 'is-active': isActive }">
        <div class="toggle-container">
            <span class="label">stockfish</span>
            <label class="switch">
                <input type="checkbox" :checked="isActive" @change="toggleAnalysis">
                <span class="slider"></span>
            </label>
        </div>

        <!-- Analysis panel -->
        <div v-if="isActive" class="analysis-panel">
            <!-- Loading state -->
            <div v-if="isInitializing" class="loading-state">
                <div class="spinner"></div>
                <span>Initializing engine...</span>
            </div>

            <!-- Analysis content -->
            <template v-else>
                <!-- Analysis info -->
                <div class="analysis-info">
                    <div class="depth">Profondeur: {{ depth }}</div>
                    <div class="nodes">Nœuds: {{ nodes }}</div>
                    <div class="time">Temps: {{ time }}</div>
                    <!-- Display top line evaluation here -->
                    <div class="evaluation top-eval"
                        :class="{ 'is-positive': evaluation > 0, 'is-negative': evaluation < 0 }">
                        {{ formatEvaluation(evaluation) }}
                    </div>
                </div>

                <!-- Best moves / Analysis Lines -->
                <div class="analysis-lines">
                    <div v-for="(line, index) in analysisLines" :key="index" class="analysis-line">
                        <span class="line-score"
                            :class="{ 'is-positive': line.score > 0, 'is-negative': line.score < 0 }">
                            ({{ formatEvaluation(line.score) }})
                        </span>
                        <span class="line-pv">
                            {{ line.pv.join(' ') }}
                        </span>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { initStockfish } from '../utils/stockfish';

export default {
    name: 'StockfishAnalysis',
    props: {
        fen: {
            type: String,
            required: true,
            default: 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1'
        }
    },
    setup(props) {
        const isActive = ref(false);
        const isInitializing = ref(false);
        const engine = ref(null);
        const depth = ref(0);
        const nodes = ref(0);
        const time = ref('0s');
        const evaluation = ref(0);
        const bestMoves = ref([]);
        const analysisLines = ref([]); // Store multiple lines { score: number, pv: string[] }
        let isAnalyzing = false;
        let hasEncounteredError = false;
        let multiPvValue = 3; // Start with 3 but reduce if we hit errors

        // Initialize Stockfish
        const initEngine = async () => {
            console.log('[StockfishAnalysis] initEngine started');
            try {
                isInitializing.value = true;
                engine.value = await initStockfish();
                console.log('[StockfishAnalysis] Stockfish interface obtained:', engine.value);

                // Promise for uciok
                const uciPromise = new Promise((resolve, reject) => {
                    const uciHandler = (event) => {
                        // Check for error message
                        if (typeof event.data === 'string' && event.data.startsWith('__ERROR__:')) {
                            console.error('[StockfishAnalysis] Engine error during UCI init:', event.data);
                            reject(new Error('Engine error during initialization'));
                            return;
                        }

                        console.log('[StockfishAnalysis] UCI Handler received:', event.data);
                        if (event.data === 'uciok') {
                            console.log('[StockfishAnalysis] Received uciok');
                            resolve();
                        } else if (event.data.startsWith('option')) {
                            // Ignore option lines during uciok wait
                        } else {
                            // Potentially handle other unexpected messages or errors
                            console.warn('[StockfishAnalysis] Unexpected message during UCI wait:', event.data);
                        }
                    };
                    engine.value.setMessageHandler({ handleEvent: uciHandler });
                    console.log('[StockfishAnalysis] uciHandler set');
                });

                // Start UCI protocol
                console.log('[StockfishAnalysis] Sending uci');
                engine.value.postMessage('uci');

                // Wait for uciok
                console.log('[StockfishAnalysis] Waiting for uciok...');
                await uciPromise;
                console.log('[StockfishAnalysis] uciok received, proceeding...');

                // Promise for readyok
                const readyPromise = new Promise((resolve, reject) => {
                    const readyHandler = (event) => {
                        // Check for error message
                        if (typeof event.data === 'string' && event.data.startsWith('__ERROR__:')) {
                            console.error('[StockfishAnalysis] Engine error during isready:', event.data);
                            reject(new Error('Engine error during initialization'));
                            return;
                        }

                        console.log('[StockfishAnalysis] Ready Handler received:', event.data);
                        if (event.data === 'readyok') {
                            console.log('[StockfishAnalysis] Received readyok');
                            resolve();
                        } else {
                            console.warn('[StockfishAnalysis] Unexpected message during ready wait:', event.data);
                        }
                    };
                    engine.value.setMessageHandler({ handleEvent: readyHandler });
                    console.log('[StockfishAnalysis] readyHandler set');
                });

                // Set options and check if ready
                console.log('[StockfishAnalysis] Sending options and isready');

                // Use reduced MultiPV if we've encountered errors before
                engine.value.postMessage(`setoption name MultiPV value ${multiPvValue}`);
                engine.value.postMessage('setoption name Threads value 1');
                engine.value.postMessage('setoption name Hash value 16');
                engine.value.postMessage('isready');

                // Wait for readyok
                console.log('[StockfishAnalysis] Waiting for readyok...');
                await readyPromise;
                console.log('[StockfishAnalysis] readyok received, initialization complete.');

                // Set up the main message handler for analysis
                console.log('[StockfishAnalysis] Setting main message handler');
                engine.value.setMessageHandler({ handleEvent: handleMessage });
                isInitializing.value = false;
                console.log('[StockfishAnalysis] initEngine finished successfully');
            } catch (error) {
                console.error('[StockfishAnalysis] Failed to initialize Stockfish:', error);
                isInitializing.value = false;

                // If we failed and haven't tried fallback settings yet, try once more
                if (!hasEncounteredError) {
                    console.log('[StockfishAnalysis] Retrying with same settings after error...');
                    hasEncounteredError = true;
                    // Keep multiPvValue the same (don't reduce it)

                    // Clean up any existing engine
                    if (engine.value) {
                        try {
                            engine.value.terminate();
                        } catch (e) {
                            console.error('[StockfishAnalysis] Error terminating engine:', e);
                        }
                        engine.value = null;
                    }

                    // Try again with same settings
                    setTimeout(() => initEngine(), 500);
                }
            }
        };

        const handleMessage = (event) => {
            // Check for error message
            if (typeof event.data === 'string' && event.data.startsWith('__ERROR__')) {
                console.error('[StockfishAnalysis] Engine error during analysis:', event.data);

                try {
                    // Remove the "__ERROR__" prefix and parse the JSON
                    const errorData = JSON.parse(event.data.substring(8)); // Note: removed ":" from prefix

                    // Only restart for memory errors but keep same number of lines
                    if ((errorData.isMemoryError || event.data.includes('memory access out of bounds')) && !hasEncounteredError) {
                        console.log('[StockfishAnalysis] Memory error detected, restarting with same settings...');
                        hasEncounteredError = true;
                        // Keep multiPvValue the same

                        // Stop analyzing and clean up
                        stopAnalysis();

                        // Force terminate the worker
                        if (engine.value) {
                            try {
                                engine.value.terminate();
                            } catch (e) {
                                console.error('[StockfishAnalysis] Error terminating engine:', e);
                            }
                            engine.value = null;
                        }

                        // Restart engine with slight delay to ensure clean shutdown
                        if (isActive.value) {
                            console.log('[StockfishAnalysis] Scheduling engine restart...');
                            setTimeout(() => {
                                console.log('[StockfishAnalysis] Restarting engine now...');
                                initEngine().then(() => {
                                    if (engine.value && !isInitializing.value) {
                                        console.log('[StockfishAnalysis] Restarting analysis...');
                                        startAnalysis();
                                    }
                                }).catch(err => {
                                    console.error('[StockfishAnalysis] Failed to restart engine:', err);
                                });
                            }, 1000); // Use a longer delay (1 second) to ensure proper cleanup
                        }
                    }
                } catch (parseError) {
                    console.error('[StockfishAnalysis] Error parsing error data:', parseError, 'Original error:', event.data);
                    // Even if we can't parse the error, still attempt recovery if it looks like a memory error
                    if (event.data.includes('memory') && !hasEncounteredError) {
                        console.log('[StockfishAnalysis] Memory-related error detected, attempting recovery...');
                        hasEncounteredError = true;

                        // Simple recovery procedure
                        stopAnalysis();
                        if (engine.value) {
                            try {
                                engine.value.terminate();
                            } catch (e) {
                                console.error('[StockfishAnalysis] Error terminating engine:', e);
                            }
                            engine.value = null;
                        }

                        // Restart with delay
                        if (isActive.value) {
                            setTimeout(() => initEngine().then(() => {
                                if (engine.value) startAnalysis();
                            }), 1000);
                        }
                    }
                }
                return;
            }

            const message = event.data;
            // console.log('[StockfishAnalysis] Main Handler received:', message);

            // Only process messages containing principal variations (pv)
            if (message.startsWith('info depth') && message.includes(' pv ')) {

                // Extract common info (assume it's roughly the same for lines at the same depth)
                const depthMatch = message.match(/depth (\d+)/);
                if (depthMatch) depth.value = parseInt(depthMatch[1]);

                const nodesMatch = message.match(/nodes (\d+)/);
                if (nodesMatch) nodes.value = parseInt(nodesMatch[1]);

                const timeMatch = message.match(/time (\d+)/);
                if (timeMatch) time.value = `${Math.floor(parseInt(timeMatch[1]) / 1000)}s`;

                // Extract info specific to this line
                const multiPvMatch = message.match(/multipv (\d+)/);
                const cpMatch = message.match(/score cp (-?\d+)/);
                const mateMatch = message.match(/score mate (-?\d+)/);
                const pvMatch = message.match(/ pv (.+)/);

                if (!multiPvMatch || !pvMatch || !pvMatch[1]) {
                    // Skip if essential info for a line is missing
                    return;
                }

                const lineIndex = parseInt(multiPvMatch[1]) - 1; // 0-based index
                let lineScore = 0;
                let isMate = false;

                if (cpMatch) {
                    lineScore = parseInt(cpMatch[1]) / 100;
                } else if (mateMatch) {
                    const mateIn = parseInt(mateMatch[1]);
                    lineScore = mateIn > 0 ? 999 : -999; // Use sentinel value
                    isMate = true;
                }

                // Adjust score based on side to move
                const fenParts = props.fen.split(' ');
                const sideToMove = fenParts[1];
                if (sideToMove === 'b') {
                    lineScore = -lineScore;
                }

                const linePv = pvMatch[1].trim().split(' ');

                // Store the line data
                const newLineData = { score: lineScore, pv: linePv };
                if (lineIndex < analysisLines.value.length) {
                    // Update existing entry reactively
                    analysisLines.value.splice(lineIndex, 1, newLineData);
                } else {
                    // Add new entries if needed (less common if engine consistently sends 3 lines)
                    while (analysisLines.value.length < lineIndex) {
                        analysisLines.value.push({ score: 0, pv: [] });
                    }
                    analysisLines.value.push(newLineData);
                }

                // Log the current state of analysisLines for debugging
                // console.log(`[StockfishAnalysis] Updated analysisLines[${lineIndex}]. Current state:`, JSON.parse(JSON.stringify(analysisLines.value)));

                // Update overall evaluation to the score of the best line (multipv 1)
                if (lineIndex === 0) {
                    evaluation.value = lineScore;
                    bestMoves.value = linePv.slice(0, 5); // Keep bestMoves synced with top line for potential other uses
                }

                // Optional: Prune array if engine sends fewer lines later
                // analysisLines.value = analysisLines.value.slice(0, max_expected_multipv);
            }
        };

        // Start analysis
        const startAnalysis = () => {
            if (!isActive.value || !props.fen || !engine.value || isAnalyzing) return;

            // Reset values
            depth.value = 0;
            nodes.value = 0;
            time.value = '0s';
            evaluation.value = 0;
            bestMoves.value = [];
            analysisLines.value = []; // Reset analysis lines

            // Start analysis
            isAnalyzing = true;
            engine.value.postMessage('stop'); // Stop any ongoing analysis
            engine.value.postMessage(`position fen ${props.fen}`);
            engine.value.postMessage('go infinite');
        };

        // Stop analysis
        const stopAnalysis = () => {
            if (engine.value && isAnalyzing) {
                engine.value.postMessage('stop');
                isAnalyzing = false;
            }
        };

        // Toggle analysis
        const toggleAnalysis = async () => {
            isActive.value = !isActive.value;
            if (isActive.value) {
                if (!engine.value) {
                    await initEngine();
                } else {
                    engine.value.setMessageHandler({ handleEvent: handleMessage });
                }
                // Check if initialization succeeded before starting analysis
                if (engine.value && !isInitializing.value) {
                    startAnalysis();
                } else {
                    console.error('[StockfishAnalysis] Cannot start analysis - engine not ready.');
                }
            } else {
                stopAnalysis();
            }
        };

        // Format evaluation
        const formatEvaluation = (evaluationValue) => {
            // Check for mate sentinel values
            if (evaluationValue === 999) {
                return 'Mate'; // Mate for White
            } else if (evaluationValue === -999) {
                return '-Mate'; // Mate for Black
            }
            // Format regular centipawn scores
            const sign = evaluationValue >= 0 ? '+' : '';
            return `${sign}${evaluationValue.toFixed(2)}`; // Show two decimal places for centipawns
        };

        // Watch for FEN changes
        watch(() => props.fen, (newFen, oldFen) => {
            if (isActive.value && newFen && engine.value && newFen !== oldFen) {
                stopAnalysis();
                startAnalysis();
            }
        });

        onUnmounted(() => {
            stopAnalysis();
            if (engine.value) {
                engine.value.postMessage('quit');
                engine.value.terminate();
                engine.value = null; // Clear the ref
            }
        });

        return {
            isActive,
            isInitializing,
            depth,
            nodes,
            time,
            evaluation,
            bestMoves,
            analysisLines,
            toggleAnalysis,
            formatEvaluation
        };
    }
};
</script>

<style scoped>
.stockfish-analysis {
    position: relative;
    width: 100%;
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 0.5rem;
    margin-bottom: 0.5rem;
}

.toggle-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.25rem 0.5rem;
}

.label {
    font-size: 0.875rem;
    color: #6c757d;
    text-transform: lowercase;
}

/* Toggle Switch */
.switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 20px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 20px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 2px;
    bottom: 2px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked+.slider {
    background-color: #0d6efd;
}

input:checked+.slider:before {
    transform: translateX(20px);
}

.analysis-panel {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid #dee2e6;
}

.analysis-info {
    display: flex;
    gap: 1rem;
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.best-moves {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    margin-bottom: 0.25rem;
}

.move {
    background: #e9ecef;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
}

.evaluation {
    font-size: 1rem;
    font-weight: bold;
    text-align: center;
}

.evaluation.is-positive {
    color: #198754;
}

.evaluation.is-negative {
    color: #dc3545;
}

.loading-state {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1rem;
    color: #6c757d;
    font-size: 0.875rem;
}

.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #0d6efd;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

.evaluation.top-eval {
    font-weight: bold;
    margin-left: auto;
    /* Push to the right */
    text-align: right;
}

.analysis-lines {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    /* Space between lines */
    margin-top: 0.5rem;
}

.analysis-line {
    font-size: 0.8rem;
    line-height: 1.3;
    display: flex;
    gap: 0.5rem;
    align-items: baseline;
}

.line-score {
    font-weight: bold;
    flex-shrink: 0;
    /* Prevent score from shrinking */
    min-width: 50px;
    /* Ensure some space for score */
    text-align: right;
}

.line-score.is-positive {
    color: #198754;
}

.line-score.is-negative {
    color: #dc3545;
}

.line-pv {
    word-break: break-all;
    /* Allow long lines to wrap */
}

/* Remove old best-moves display style if not needed */
/*
.best-moves {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    margin-bottom: 0.25rem;
}

.move {
    background: #e9ecef;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
}
*/

/* Remove old evaluation style */
/*
.evaluation {
    font-size: 1rem;
    font-weight: bold;
    text-align: center;
}

.evaluation.is-positive {
    color: #198754;
}

.evaluation.is-negative {
    color: #dc3545;
}
*/
</style>