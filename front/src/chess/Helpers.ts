import { TeamType, PieceType } from "./Constants";

export function oppositeColor(color: string): string {
    return color === TeamType.W ? TeamType.B : TeamType.W;
}

export function removeAnnotations(move: string): string {
    return move.replace(/[!?+\-/#)]+/g, '');
}

export function pieceTypeFromChar(char: string): string | undefined {
    const charToPieceTypeMap: { [key: string]: string } = {
        p: PieceType.PAWN,
        b: PieceType.BISHOP,
        n: PieceType.KNIGHT,
        r: PieceType.ROOK,
        q: PieceType.QUEEN,
        k: PieceType.KING
    };

    return charToPieceTypeMap[char.toLowerCase()];
}