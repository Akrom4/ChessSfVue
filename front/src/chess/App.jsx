import './App.css';
import Referee from './components/Referee/Referee';
import PgnReader from './components/PgnReader/PgnReader';
import GameNavigator from './components/GameNavigator/GameNavigator';
import { useState, useEffect } from "react";

function App({ pgnDataProp }) {

  const data = typeof pgnDataProp === 'string' ? JSON.parse(pgnDataProp) : pgnDataProp;
  console.log(data);

  const INITIAL_FEN = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1";

  // const [gameState, setGameState] = useState(data.chapter[0].Moves);

  const [fen, setFen] = useState(INITIAL_FEN);
  const [currentGameStateIndex, setCurrentGameStateIndex] = useState(-1);

  useEffect(() => {
    if (gameState.length > 0 && currentGameStateIndex >= 0 && currentGameStateIndex < gameState.length) {
      setFen(gameState[currentGameStateIndex].position);
    }
  }, [gameState, currentGameStateIndex]);

  const handleMoveClick = (move) => {
    setFen(move.position);
    const index = gameState.findIndex(m => m.position === move.position);
    if (index !== -1) {
      setCurrentGameStateIndex(index);
    }
  };

  return (
    <div id="app">
      <div className="game-container">
        <Referee fen={fen} />
        <GameNavigator
          gameState={gameState}
          currentGameStateIndex={currentGameStateIndex}
          setCurrentGameStateIndex={setCurrentGameStateIndex}
          setFen={setFen}
          INITIAL_FEN={INITIAL_FEN}
        />
      </div>
      <PgnReader
        pgnData={data}
        onMoveClick={handleMoveClick}
        gameState={gameState}
        currentGameStateIndex={currentGameStateIndex}
      />
    </div>
  );
}

export default App;
