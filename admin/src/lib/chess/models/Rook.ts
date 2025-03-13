import { PieceType } from "../Constants";
import { Piece } from "./Piece";
import { Position } from "./Position";

export class Rook extends Piece {
    hasMoved: boolean;
    constructor(position: Position, team: string, possibleMoves: Position[] = [], hasMoved: boolean = false) {
        super(position, PieceType.ROOK, team, possibleMoves);
        this.hasMoved = hasMoved;
    }

    clone(): Rook {
        return new Rook(
            this.position.clone(),
            this.team,
            this.possibleMoves.map(position => position.clone()),
            this.hasMoved
        );
    }
}