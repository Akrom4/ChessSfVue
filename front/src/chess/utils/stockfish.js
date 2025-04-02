// stockfish.js
import stockfishWorkerUrl from '/stockfish/stockfish-17-lite-single.js?url'; // Import the URL

let stockfishWorkerInstance = null;
let messageHandler = null;

export const initStockfish = async () => {
    if (stockfishWorkerInstance) {
        // If there's an existing worker, terminate it cleanly first
        try {
            console.log('[stockfish.js] Terminating existing worker before creating a new one');
            stockfishWorkerInstance.terminate();
            stockfishWorkerInstance = null;
            messageHandler = null;
        } catch (err) {
            console.error('[stockfish.js] Error terminating existing worker:', err);
        }
    }

    try {
        console.log('[stockfish.js] Initializing Stockfish worker using URL:', stockfishWorkerUrl);
        // Instantiate the worker using the imported URL
        stockfishWorkerInstance = new Worker(stockfishWorkerUrl, { type: 'module' }); // Pass the URL string

        stockfishWorkerInstance.onmessage = (event) => {
            console.log('[stockfish.js] Received:', event.data);
            if (messageHandler) {
                messageHandler(event); // Call the currently assigned handler
            }
        };

        stockfishWorkerInstance.onerror = function (event) {
            console.error("[stockfish.js] Worker error:", event);
            const isMemoryError = event.message && event.message.includes('memory access out of bounds');

            // Send a synthetic message to mimic a worker message event for error handling
            if (messageHandler) {
                try {
                    // Create a properly formatted error message that can be easily parsed
                    const errorMsg = `__ERROR__${JSON.stringify({
                        type: event.type || '',
                        message: event.message || '',
                        filename: event.filename || '',
                        lineno: event.lineno || 0,
                        colno: event.colno || 0,
                        isMemoryError
                    })}`;

                    messageHandler({ data: errorMsg });
                } catch (e) {
                    console.error("[stockfish.js] Error formatting worker error:", e);
                }
            }
        };

        console.log('[stockfish.js] Worker instance created.');

        // Return the interface
        return {
            postMessage: (message) => {
                console.log('[stockfish.js] Sending:', message);
                if (stockfishWorkerInstance) {
                    stockfishWorkerInstance.postMessage(message);
                } else {
                    console.error('[stockfish.js] Cannot send message - worker is null');
                }
            },
            // Method to set the message handler
            setMessageHandler: (handler) => {
                // The handler passed from Vue might be a Proxy, extract the raw function if needed
                // Or adjust how the handler is called/passed.
                // For now, let's assume the passed handler works directly.
                messageHandler = handler.handleEvent || handler; // Adapt based on how handler is passed
            },
            terminate: () => {
                console.log('[stockfish.js] Terminating worker.');
                if (stockfishWorkerInstance) {
                    stockfishWorkerInstance.terminate();
                    stockfishWorkerInstance = null;
                    messageHandler = null;
                }
            }
        };
    } catch (error) {
        console.error('[stockfish.js] Failed to initialize Stockfish:', error);
        throw error;
    }
};

// Optional: Keep getStockfish if needed elsewhere, though direct use might be less common now
// export const getStockfish = () => stockfishWorkerInstance; 