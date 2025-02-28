import { Board, Pawn, King, Piece, Position, Rook } from "./models";

export const row: string[] = ['1', '2', '3', '4', '5', '6', '7', '8'];
export const column: string[] = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];

export const PieceType = {
    PAWN: 'p',
    BISHOP: 'b',
    KNIGHT: 'n',
    ROOK: 'r',
    QUEEN: 'q',
    KING: 'k'
} as const;

export const TeamType = { W: 'w', B: 'b' } as const;

export const initialBoardState: Piece[] = [];

for (let i = 0; i < 8; i++) {
    initialBoardState.push(new Pawn(new Position(i, 6), TeamType.B));
}
for (let i = 0; i < 8; i++) {
    initialBoardState.push(new Pawn(new Position(i, 1), TeamType.W));
}
for (let i = 0; i < 2; i++) {
    const rowT = i === 0 ? 0 : 7;
    const team = i === 0 ? TeamType.W : TeamType.B;

    initialBoardState.push(new Rook(new Position(0, rowT), team));
    initialBoardState.push(new Rook(new Position(7, rowT), team));
    initialBoardState.push(new Piece(new Position(1, rowT), PieceType.KNIGHT, team));
    initialBoardState.push(new Piece(new Position(6, rowT), PieceType.KNIGHT, team));
    initialBoardState.push(new Piece(new Position(2, rowT), PieceType.BISHOP, team));
    initialBoardState.push(new Piece(new Position(5, rowT), PieceType.BISHOP, team));
    initialBoardState.push(new Piece(new Position(3, rowT), PieceType.QUEEN, team));
    initialBoardState.push(new King(new Position(4, rowT), team));
}

export const initialBoard = new Board(initialBoardState, TeamType.W);


