import { PieceType } from "../Constants";
import { Piece } from "./Piece";
import { Position } from "./Position";

export class Pawn extends Piece{
    enPassant: boolean;
    constructor(position: Position,team: string,possibleMoves: Position[] = [],enPassant: boolean = false){
        super(position,PieceType.PAWN,team,possibleMoves);
        this.enPassant = enPassant;
    }
    clone(): Pawn {
        return new Pawn(this.position.clone(),this.team,this.possibleMoves.map(position => position.clone()), this.enPassant);
    }
}