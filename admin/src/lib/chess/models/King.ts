import { PieceType } from "../Constants";
import { Piece } from "./Piece";
import { Position } from "./Position";

export class King extends Piece {
    hasMoved: boolean;
    constructor(position: Position, team: string, possibleMoves: Position[] = [], hasMoved: boolean = false) {
        super(position, PieceType.KING, team, possibleMoves);
        this.hasMoved = hasMoved;
    }

    clone(): King {
        return new King(this.position.clone(), this.team, this.possibleMoves.map(position => position.clone()), this.hasMoved);
    }

    inCheck(pieces: Piece[], destination: Position = this.position): boolean {
        return pieces.some(piece => {
            if(piece.team !== this.team)
                return piece.possibleMoves && piece.possibleMoves.some(move => destination.samePosition(move));
            return false;
        });
    }
}