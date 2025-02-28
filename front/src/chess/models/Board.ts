import { column, initialBoardState, PieceType, TeamType } from "../Constants";
import { Position } from "./Position";
import { Piece, Pawn, King, Rook } from ".";
import { pieceTypeFromChar } from "../Helpers";

export class Board {
  pieces: Piece[];
  turn: string;
  moveCount: number;
  lastPosition: string;
  orientation: string;

  constructor(pieces: Piece[] = initialBoardState, turn: string = TeamType.W, moveCount: number = 0, lastPosition: string = '') {
    this.pieces = pieces;
    this.turn = turn;
    this.moveCount = moveCount;
    this.lastPosition = lastPosition;
    this.orientation = 'white';
    this.validMoves();
  }

  setMoveCount(n: number = 1): void {
    this.moveCount = n;
  }

  newMoveCount(): void {
    this.moveCount++;
  }

  getMoveCount(): number {
    return this.moveCount;
  }

  setTurn(teamTurn: string): void {
    this.turn = teamTurn;
  }

  getTurn(): string {
    return this.turn;
  }

  getEnPassantSquare(epSquare: string, teamTurn: string): Position {
    const squareValues = Object.values(column);
    const columnIndex = squareValues.indexOf(epSquare[0]);

    return teamTurn === TeamType.W
      ? new Position(columnIndex, parseInt(epSquare[1]) - 2)
      : new Position(columnIndex, parseInt(epSquare[1]));
  }

  clone(): Board {
    return new Board(this.pieces.map((p) => p.clone()), this.turn, this.moveCount, this.lastPosition);
  }

  reverse(): void {
    this.orientation = 'black';
    this.validMoves();
  }

  validMoves(): void {
    for (const piece of this.pieces) {
      piece.possibleMoves = this.getValidMoves(piece);
    }
  }

  pushMove(start: Position, end: Position): void {
    const piece = this.pieces.find(p => p.samePosition(start));

    if (piece) {
      this.pieces = this.pieces.filter(p => !p.samePosition(end));
      piece.position = end;
    }
  }

  undoMove(): Board {
    this.turn = this.turn === TeamType.W ? TeamType.B : TeamType.W;
    return this.fenReader(this.lastPosition);
  }

  isKingInCheck(playedPiece: Piece, destination: Position): { inCheck: boolean, checkingPiece: Piece | undefined } {
    const tempBoard = this.clone();
    const king = tempBoard.pieces.find(
      (p) => p.isKing && p.team === playedPiece.team
    );

    if (!king) {
      return { inCheck: false, checkingPiece: undefined };
    }
    
    if (tempBoard.squareIsOccupiedByOpp(destination, king.team)) {
      tempBoard.pieces = tempBoard.pieces.filter((p) => !p.samePosition(destination));
    }

    const tempPiece = tempBoard.pieces.find((p) => p.samePosition(playedPiece.position));

    if (tempPiece) {
      tempPiece.position = destination;
      tempBoard.validMoves();
    }

    const checkingPiece = tempBoard.pieces.find((p) => {
      if (p.possibleMoves) {
        return p.possibleMoves.some((move) => move.x === king.position.x && move.y === king.position.y);
      }
      return false;
    });

    const inCheck = !!checkingPiece;
    return { inCheck, checkingPiece };
  }

  isEnPassant(initialPosition: Position, desiredPosition: Position, type: string, team: string): boolean {
    if (type === PieceType.PAWN) {
      const pawnDirection = team === TeamType.W ? 1 : -1;
      if (
        Math.abs(desiredPosition.x - initialPosition.x) === 1 &&
        desiredPosition.y - initialPosition.y === pawnDirection
      ) {
        const piece = this.pieces.find(
          (p) =>
            p.samePosition(
              new Position(desiredPosition.x, desiredPosition.y - pawnDirection)
            ) &&
            p instanceof Pawn &&
            p.enPassant &&
            p.team !== team
        );
        if (piece) return true;
      }
    }
    return false;
  }

  isCastle(playedPiece: Piece, destination: Position): { castle: boolean, rook?: Piece } {
    if (playedPiece.isKing &&
      Math.abs(destination.x - playedPiece.position.x) === 2 &&
      destination.y === playedPiece.position.y &&
      (playedPiece instanceof King && !playedPiece.hasMoved) &&
      (playedPiece.position.y === 0 || playedPiece.position.y === 7)
    ) {
      const deltaX = destination.x > playedPiece.position.x ? 1 : -1;
      const rookX = destination.x > playedPiece.position.x ? 7 : 0;
      const rookPosition = new Position(rookX, playedPiece.position.y);
      const rook = this.pieces.find((piece) =>
        piece.samePosition(rookPosition) && piece instanceof Rook && !piece.hasMoved
      );

      const pathClear = this.isPathNotOccupied(
        rookPosition,
        playedPiece.position,
        deltaX,
        0
      );

      const squaresSafe = !playedPiece.inCheck(this.pieces) &&
        !playedPiece.inCheck(this.pieces, new Position(playedPiece.position.x + deltaX, playedPiece.position.y));

      return { castle: !!rook && pathClear && squaresSafe, rook };
    }
    return { castle: false };
  }

  playMove(destination: Position, playedPiece: Piece, promotionChoice: string | null = null): boolean {
    const { inCheck, checkingPiece } = this.isKingInCheck(playedPiece, destination);

    if ((checkingPiece && !checkingPiece.samePosition(destination)) || inCheck) {
        return false;
    }

    const pawnDirection = playedPiece.team === TeamType.W ? 1 : -1;
    const validMove = this.isValidMove(playedPiece.position, destination);

    const enPassant = this.isEnPassant(playedPiece.position, destination, playedPiece.type, playedPiece.team);

    const castle = this.isCastle(playedPiece, destination);

    if (castle.castle && castle.rook instanceof Rook) {
        this.lastPosition = this.getFen();
        // Rook update
        castle.rook.hasMoved = true;
        castle.rook.position = new Position(destination.x + (destination.x > playedPiece.position.x ? -1 : 1), playedPiece.position.y);
        this.validMoves();
        // King update
        if (playedPiece instanceof King) {
          playedPiece.hasMoved = true;
        }
        playedPiece.position = destination;
    } else if (enPassant) {
        this.lastPosition = this.getFen();
        this.pieces = this.pieces.reduce((results, piece) => {
          if (piece.samePosition(playedPiece.position)) {
            if (piece instanceof Pawn) {
              piece.enPassant = false;
            }
            piece.position = destination;

            results.push(piece);
          } else if (
            !piece.samePosition(
              new Position(destination.x, destination.y - pawnDirection)
            )
          ) {
            if (piece instanceof Pawn) piece.enPassant = false;
            results.push(piece);
          }
          return results;
        }, [] as Piece[]);
        this.validMoves();
    } else if (validMove) {
        this.lastPosition = this.getFen();
        this.pieces = this.pieces.reduce((results, piece) => {
          if (piece.samePosition(destination)) {
            return results; // The piece taken isn't added to the array
          }
          else if (piece.samePosition(playedPiece.position)) {
            if (piece instanceof Pawn) {
              piece.enPassant =
                Math.abs(playedPiece.position.y - destination.y) === 2 &&
                piece instanceof Pawn; // This pawn can be taken en passant
            }
            piece.position = destination;
            if (piece instanceof King) {
              piece.hasMoved = true; // King castle rule
            } else if (piece instanceof Rook) {
              piece.hasMoved = true; // Rook castle rule
            }
            if (promotionChoice) {
              const newPiece = this.createPromotedPiece(promotionChoice, destination, playedPiece.team);
              results.push(newPiece); // Add the new promoted piece to the array
            }
            results.push(piece); // Push the new piece position in the updatedPieces array
          } else {
            if (piece instanceof Pawn) {
              piece.enPassant = false; // This pawn cannot be taken en passant
            }
            results.push(piece); // Push the remaining pieces in the updatedPieces array
          }
          return results;
        }, [] as Piece[]);

        this.validMoves();
    } else {
        return false;
    }

    this.setTurn(this.getTurn() === TeamType.W ? TeamType.B : TeamType.W);
    if (playedPiece.team === TeamType.W) this.newMoveCount();
    this.validMoves();
    return true;
  }

  createPromotedPiece(promotionChoice: string, destination: Position, team: string): Piece {
    let piece: Piece;
    let type = pieceTypeFromChar(promotionChoice) || PieceType.QUEEN;

    switch (type) {
      case PieceType.ROOK:
        piece = new Rook(destination, team, [], true);
        break;
      case PieceType.KING:
        piece = new King(destination, team);
        break;
      default:
        piece = new Piece(destination, type, team);
    }
    return piece;
  }

  getValidMoves(piece: Piece): Position[] {
    switch (piece.type) {
      case PieceType.PAWN:
        const possiblePawnMoves: Position[] = [];
        const pawnDirection = piece.team === TeamType.W ? 1 : -1;
        const specialRow = piece.team === TeamType.W ? 1 : 6;
        const normalMove = new Position(
          piece.position.x,
          piece.position.y + pawnDirection
        );
        const specialMove = new Position(
          piece.position.x,
          piece.position.y + pawnDirection * 2
        );
        const upperLeftAttack = new Position(
          piece.position.x - 1,
          piece.position.y + pawnDirection
        );
        const upperRightAttack = new Position(
          piece.position.x + 1,
          piece.position.y + pawnDirection
        );
        const leftPosition = new Position(
          piece.position.x - 1,
          piece.position.y
        );
        const rightPosition = new Position(
          piece.position.x + 1,
          piece.position.y
        );

        if (!this.squareIsOccupied(normalMove)) {
          possiblePawnMoves.push(normalMove);
          if (
            piece.position.y === specialRow &&
            !this.squareIsOccupied(specialMove)
          ) {
            possiblePawnMoves.push(specialMove);
          }
        }
        if (this.squareIsOccupiedByOpp(upperLeftAttack, piece.team))
          possiblePawnMoves.push(upperLeftAttack);
        else if (!this.squareIsOccupied(upperLeftAttack)) {
          const leftPiece = this.pieces.find((p) =>
            p.samePosition(leftPosition)
          );
          if (
            leftPiece != null &&
            leftPiece instanceof Pawn &&
            leftPiece.enPassant &&
            leftPiece.team !== piece.team
          )
            possiblePawnMoves.push(upperLeftAttack);
        }
        if (this.squareIsOccupiedByOpp(upperRightAttack, piece.team))
          possiblePawnMoves.push(upperRightAttack);
        else if (!this.squareIsOccupied(upperLeftAttack)) {
          const rightPiece = this.pieces.find((p) =>
            p.samePosition(rightPosition)
          );
          if (
            rightPiece != null &&
            rightPiece instanceof Pawn &&
            rightPiece.enPassant &&
            rightPiece.team !== piece.team
          )
            possiblePawnMoves.push(upperRightAttack);
        }
        return possiblePawnMoves;
      case PieceType.KNIGHT:
        const knightDirections = [
          { x: -2, y: -1 },
          { x: -2, y: 1 },
          { x: -1, y: -2 },
          { x: -1, y: 2 },
          { x: 1, y: -2 },
          { x: 1, y: 2 },
          { x: 2, y: -1 },
          { x: 2, y: 1 },
        ];

        const possibleKnightMoves = knightDirections
          .map(
            (d) => new Position(piece.position.x + d.x, piece.position.y + d.y)
          ) // knight position + kngiht directions
          .filter((move) => !this.squareIsOccupiedByTeam(move, piece.team)); // filter to keep only free squares or occupied by opp

        return possibleKnightMoves;
      case PieceType.BISHOP:
        const bishopDirections = [
          { x: 1, y: 1 },
          { x: 1, y: -1 },
          { x: -1, y: 1 },
          { x: -1, y: -1 },
        ];
        const possibleBishopMoves = this.getMovesAlongDirections(
          piece,
          bishopDirections
        );
        return possibleBishopMoves;
      case PieceType.ROOK:
        const rookDirections = [
          { x: 0, y: 1 },
          { x: 0, y: -1 },
          { x: 1, y: 0 },
          { x: -1, y: 0 },
        ];
        const possibleRookMoves = this.getMovesAlongDirections(
          piece,
          rookDirections
        );
        return possibleRookMoves;
      case PieceType.QUEEN:
        const queenDirections = [
          { x: 0, y: 1 },
          { x: 0, y: -1 },
          { x: 1, y: 0 },
          { x: -1, y: 0 },
          { x: 1, y: 1 },
          { x: 1, y: -1 },
          { x: -1, y: 1 },
          { x: -1, y: -1 },
        ];
        const possibleQueenMoves = this.getMovesAlongDirections(
          piece,
          queenDirections
        );
        return possibleQueenMoves;
      case PieceType.KING:
        const possibleKingMoves: Position[] = [];
        const directions = [
          { x: 1, y: 1 },
          { x: 1, y: 0 },
          { x: 1, y: -1 },
          { x: 0, y: 1 },
          { x: 0, y: -1 },
          { x: -1, y: 1 },
          { x: -1, y: 0 },
          { x: -1, y: -1 },
        ];
        for (const dir of directions) {
          const newPosition = new Position(
            piece.position.x + dir.x,
            piece.position.y + dir.y
          );

          if (
            newPosition.x >= 0 &&
            newPosition.x < 8 &&
            newPosition.y >= 0 &&
            newPosition.y < 8
          ) {
            if (!this.squareIsOccupiedByTeam(newPosition, piece.team)) {
              possibleKingMoves.push(newPosition);
            }
          }
        }
        return possibleKingMoves;
      default:
        return [];
    }
  }

  getMovesAlongDirections(piece: Piece, directions: { x: number, y: number }[]): Position[] {
    const possibleMoves: Position[] = [];
    for (const dir of directions) {
      for (let i = 1; i < 8; i++) {
        const newPosition = new Position(
          piece.position.x + i * dir.x,
          piece.position.y + i * dir.y
        );

        if (
          newPosition.x < 0 ||
          newPosition.x > 7 ||
          newPosition.y < 0 ||
          newPosition.y > 7
        ) {
          break; // exit loop if new position is out of the board
        }
        if (!this.squareIsOccupied(newPosition)) {
          possibleMoves.push(newPosition);
        } else if (this.squareIsOccupiedByOpp(newPosition, piece.team)) {
          possibleMoves.push(newPosition);
          break; // exit loop if new position is occupied by opponent's piece
        } else {
          break; // exit loop if new position is occupied by player's own piece
        }
      }
    }
    return possibleMoves;
  }

  squareIsOccupied(position: Position): boolean {
    const piece = this.pieces.find((p) => p.samePosition(position));
    return !!piece;
  }

  squareIsOccupiedByOpp(position: Position, team: string): boolean {
    const piece = this.pieces.find(
      (p) => p.samePosition(position) && p.team !== team
    );
    return !!piece;
  }

  squareIsOccupiedByTeam(position: Position, team: string): boolean {
    const piece = this.pieces.find(
      (p) => p.samePosition(position) && p.team === team
    );
    return !!piece;
  }

  isPathNotOccupied(desiredPosition: Position, initialPosition: Position, dx: number, dy: number): boolean {
    const numSteps = Math.max(
      Math.abs(desiredPosition.x - initialPosition.x),
      Math.abs(desiredPosition.y - initialPosition.y)
    );
    for (let i = 1; i < numSteps; i++) {
      const x = initialPosition.x + i * dx;
      const y = initialPosition.y + i * dy;
      if (this.squareIsOccupied(new Position(x, y))) {
        return false;
      }
    }
    return true;
  }

  isValidMove(initialPosition: Position, desiredPosition: Position): boolean {
    const currentPiece = this.pieces.find(p => p.samePosition(initialPosition));
    return currentPiece?.possibleMoves.some(p => p.samePosition(desiredPosition)) || false;
  }

  fenReader(fen: string): Board {
    const fenParts = fen.split(' ');
    const boardState: Piece[] = [];
    const ranks = fenParts[0].split('/');
    const teamTurn = fenParts[1] === 'w' ? TeamType.W : TeamType.B;
    const castle = fenParts[2];
    const moveCount = parseInt(fenParts[5]);

    const enPassantPawnSquare = fenParts[3] !== '-' ? this.getEnPassantSquare(fenParts[3], teamTurn) : null;

    let x = 0;
    let y = 7;

    for (const rank of ranks) {
      for (const char of rank) {
        if (/\d/.test(char)) {
          x += parseInt(char);
        } else {
          const team = /[a-z]/.test(char) ? TeamType.B : TeamType.W;
          const type = pieceTypeFromChar(char) || "defaultType";

          let piece: Piece;

          switch (type) {
            case PieceType.PAWN:
              if (enPassantPawnSquare && enPassantPawnSquare.samePosition(new Position(x, y))) {
                piece = new Pawn(new Position(x, y), team, [], true);
              }
              else
                piece = new Pawn(new Position(x, y), team);
              break;
            case PieceType.ROOK:
              if (castle === 'KQkq') {
                piece = new Rook(new Position(x, y), team, [], false);
              } else {
                const hasMoved = ((team === TeamType.W) && ((x === 0 && !castle.includes('Q')) || (x === 7 && !castle.includes('K'))))
                  || ((team === TeamType.B) && ((x === 0 && !castle.includes('q')) || (x === 7 && !castle.includes('k'))));
                piece = new Rook(new Position(x, y), team, [], hasMoved);
              }
              break;
            case PieceType.KING:
              if (castle === '-')
                piece = new King(new Position(x, y), team, [], true);
              else
                piece = new King(new Position(x, y), team);
              break;
            default:
              piece = new Piece(new Position(x, y), type, team);
          }

          boardState.push(piece);
          x++;
        }
      }
      x = 0;
      y--;
    }

    return new Board(boardState, teamTurn, moveCount);
  }

  getFen(): string {
    let fen = '';
    let emptySquare = 0;
    for (let y = 7; y >= 0; y--) {
      for (let x = 0; x < 8; x++) {
        const piece = this.pieces.find(p => p.position.samePosition(new Position(x, y)));
        if (piece) {
          if (emptySquare > 0) {
            fen += emptySquare;
            emptySquare = 0;
          }
          fen += piece.team === TeamType.W ? piece.type.toUpperCase() : piece.type;
        } else {
          emptySquare++;
        }
      }

      if (emptySquare > 0) {
        fen += emptySquare;
        emptySquare = 0;
      }

      if (y > 0) {
        fen += '/';
      }
    }
    fen += ` ${this.turn}`;

    const whiteKing = this.pieces.find(p => p.type === PieceType.KING && p.team === TeamType.W);
    const blackKing = this.pieces.find(p => p.type === PieceType.KING && p.team === TeamType.B);

    const whiteRooks = this.pieces.filter(p => p.type === PieceType.ROOK && p.team === TeamType.W);
    const blackRooks = this.pieces.filter(p => p.type === PieceType.ROOK && p.team === TeamType.B);

    let castleRights = '';
    if (whiteKing instanceof King && !whiteKing.hasMoved) {
      const whiteKingSideRook = whiteRooks.find(r => r instanceof Rook && r.position.x === 7);
      const whiteQueenSideRook = whiteRooks.find(r => r instanceof Rook && r.position.x === 0);
      
      if (whiteKingSideRook instanceof Rook && !whiteKingSideRook.hasMoved) {
        castleRights += 'K';
      }
      if (whiteQueenSideRook instanceof Rook && !whiteQueenSideRook.hasMoved) {
        castleRights += 'Q';
      }
    }

    if (blackKing instanceof King && !blackKing.hasMoved) {
      const blackKingSideRook = blackRooks.find(r => r instanceof Rook && r.position.x === 7);
      const blackQueenSideRook = blackRooks.find(r => r instanceof Rook && r.position.x === 0);
      
      if (blackKingSideRook instanceof Rook && !blackKingSideRook.hasMoved) {
        castleRights += 'k';
      }
      if (blackQueenSideRook instanceof Rook && !blackQueenSideRook.hasMoved) {
        castleRights += 'q';
      }
    }

    fen += castleRights.length > 0 ? ` ${castleRights}` : ' -';

    const enPassantPawn = this.pieces.find(p => p instanceof Pawn && p.enPassant);
    if (enPassantPawn) {
      const enPassantTargetY = enPassantPawn.team === TeamType.W ? enPassantPawn.position.y - 1 : enPassantPawn.position.y + 1;
      const enPassantTargetSquare = new Position(enPassantPawn.position.x, enPassantTargetY).toString();
      fen += ` ${enPassantTargetSquare}`;
    } else {
      fen += ' -';
    }

    fen += ` 0 ${this.moveCount}`;
    return fen;
  }
}