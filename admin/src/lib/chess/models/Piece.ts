import { PieceType, TeamType } from "../Constants";

import { Position } from "./Position";

export class Piece {
    image: string;
    position: Position;
    type: string;
    team: string;
    possibleMoves: Position[];
    constructor(position: Position, type: string, team: string, possibleMoves: Position[] = []) {
        this.position = position;
        this.type = type;
        this.team = team;
        this.possibleMoves = possibleMoves;
    }
    clone(): Piece {
        return new Piece(this.position.clone(), this.type, this.team, this.possibleMoves.map(position => position.clone()));
    }
    samePosition(otherPosition: Position): boolean {
        return this.position.samePosition(otherPosition);
    }

    get isPawn(): boolean {
        return this.type === PieceType.PAWN
    }
    get isQueen(): boolean {
        return this.type === PieceType.QUEEN
    }
    get isKing(): boolean {
        return this.type === PieceType.KING
    }
    get isRook(): boolean {
        return this.type === PieceType.ROOK
    }
    get isKnight(): boolean {
        return this.type === PieceType.KNIGHT
    }
    get isBishop(): boolean {
        return this.type === PieceType.BISHOP
    }
}