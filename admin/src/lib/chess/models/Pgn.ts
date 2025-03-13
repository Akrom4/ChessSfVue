import { column, PieceType, TeamType } from "../Constants";
import { oppositeColor, pieceTypeFromChar, removeAnnotations } from "../Helpers";
import { Board, Course, Position } from ".";
import { Piece } from "./Piece";

interface Move {
    move: string;
    moveNumber: number;
    teamColor: string;
    position: string;
}

interface Comment {
    text: string;
    moveNumber: number;
    teamColor: string;
}

interface Variation {
    moves: Move[];
    comments: Comment[];
    variations: Variation[];
    parentMove: Move | null;
}

export class Pgn {
    pgnFile: string | null;
    moves: Move[];
    comments: Comment[];
    chapters: string[];
    variations: Variation[];

    constructor(pgnFile: string | null = null) {
        this.pgnFile = pgnFile;
        this.moves = [];
        this.comments = [];
        this.chapters = [];
        this.variations = [];
    }

    /**
     * Parse the PGN data and extract just the chapters without creating a Course
     * @returns An array of chapter objects and the raw chapter texts
     */
    parseChapters(): { chapters: any[]; rawChapterTexts: string[] } {
        const rawChapterTexts: string[] = [];
        const parsedChapters: any[] = [];
        
        if (!this.pgnFile) {
            return { chapters: parsedChapters, rawChapterTexts };
        }

        const chapters = this.pgnFile.split('\n\n\n');
        // Store the raw chapter texts for later use
        chapters.forEach(chapter => {
            if (chapter.trim()) {
                rawChapterTexts.push(chapter);
            }
        });

        for (const [index, chapter] of chapters.entries()) {
            if (!chapter.trim()) continue;
            
            const lines = chapter.split('\n');
            const startFEN = this.metaData(lines, '[FEN');
            const title = this.metaData(lines, '[Event');

            let board = new Board();
            if (startFEN) {
                board = board.fenReader(startFEN);
            } else {
                board = board.clone();
            }

            const moveLines = lines.filter(line => !line.startsWith('[') && line !== '');
            const moveText = moveLines.join(' ').trim();

            const parsedChapter = this.parseVariation(board, moveText, board.moveCount, board.turn);
            
            // Create a chapter object in the original format to match what the user expects
            parsedChapters.push({
                FEN: startFEN || "",
                Title: title || "",
                Number: index + 1,
                Moves: parsedChapter.moves,  // Keep the full move objects with position, teamColor, etc.
                Comments: parsedChapter.comments,  // Keep the full comment objects
                Variations: parsedChapter.variations,  // Include variations
                // Also include these properties for compatibility with our form
                title: title || "",
                rawpgn: rawChapterTexts[index] || '',
                pgndata: [] // Keep this empty as we're now returning the full structured data
            });
        }
        
        return { chapters: parsedChapters, rawChapterTexts };
    }

    parseData(): { course: Course | undefined; rawChapterTexts: string[] } {
        const rawChapterTexts: string[] = [];
        
        if (!this.pgnFile) {
            return { course: undefined, rawChapterTexts };
        }

        const chapters = this.pgnFile.split('\n\n\n');
        chapters.forEach(chapter => {
            if (chapter.trim()) {
                rawChapterTexts.push(chapter);
            }
        });
        
        const courseTitleLine = chapters.find(line => line.startsWith('[CourseTitle'));
        const courseTitle = courseTitleLine ? courseTitleLine.split('"')[1] : '';
        const course = new Course(courseTitle);

        for (const [index, chapter] of chapters.entries()) {
            if (!chapter.trim()) continue;
            
            const lines = chapter.split('\n');
            const startFEN = this.metaData(lines, '[FEN');
            const title = this.metaData(lines, '[Event');

            let board = new Board();
            if (startFEN) {
                board = board.fenReader(startFEN);
            } else {
                board = board.clone();
            }

            const moveLines = lines.filter(line => !line.startsWith('[') && line !== '');
            const moveText = moveLines.join(' ').trim();

            const parsedChapter = this.parseVariation(board, moveText, board.moveCount, board.turn);
            
            course.addChapter({
                title: title || '',
                rawpgn: rawChapterTexts[index] || '',
                pgndata: [{
                    parsedMoves: parsedChapter.moves.map(m => m.move),
                    parsedComments: parsedChapter.comments.map(c => c.text),
                    fen: startFEN,
                    index: index + 1
                }]
            });
        }
        
        return { course, rawChapterTexts };
    }

    parseVariation(board: Board, moveText: string, moveNumber: number, teamColor: string, parentMove: Move | null = null): Variation {
        const moveTokens = moveText.split(/(\d+\.{1,3}\s?|\s*\{.*?\}\s*|\s*\(\s*|\s*\)\s*|\s+)/);
        const variation: Variation = {
            moves: [],
            comments: [],
            variations: [],
            parentMove: parentMove
        };

        let nestedMoveText = '';
        const parenthesesStack: string[] = [];

        for (let i = 0; i < moveTokens.length; i++) {
            let token = moveTokens[i].trim();

            if (token === '*' || token === '') {
                continue;
            }

            if (token === '(') {
                parenthesesStack.push(token);
                if (parenthesesStack.length === 1) {
                    nestedMoveText = '';
                } else {
                    nestedMoveText += ' ' + token;
                }
            } else if (token === ')') {
                parenthesesStack.pop();
                if (parenthesesStack.length === 0) {
                    const lastMove = variation.moves[variation.moves.length - 1];
                    const variationStartingPosition = variation.moves[variation.moves.length - 2];
                    const variationBoard = board.fenReader(variationStartingPosition.position);

                    const nestedVariation = this.parseVariation(
                        variationBoard,
                        nestedMoveText.trim(),
                        moveNumber,
                        teamColor,
                        lastMove
                    );
                    variation.variations.push(nestedVariation);

                    nestedMoveText = '';
                } else {
                    nestedMoveText += ' ' + token;
                }
            } else if (parenthesesStack.length > 0) {
                nestedMoveText += ' ' + token;
            } else if (token.endsWith('.')) {
                moveNumber = parseInt(token.slice(0, -1), 10);
                teamColor = token.endsWith('...') ? TeamType.B : TeamType.W;
            } else if (token.startsWith('{')) {
                let comment = token.slice(1, -1);
                variation.comments.push({
                    text: comment.trim(),
                    moveNumber: moveNumber,
                    teamColor: oppositeColor(teamColor)
                });
            } else {
                this.pushPGNMove(board, token, teamColor);
                variation.moves.push({
                    move: token,
                    moveNumber: moveNumber,
                    teamColor: teamColor,
                    position: board.getFen()
                });
                teamColor = oppositeColor(teamColor);
            }
        }
        return variation;
    }

    metaData(lines: string[], string: string): string {
        const startMetaDataLine = lines.find(line => line.startsWith(string));
        let metaData = '';

        if (startMetaDataLine) {
            metaData = startMetaDataLine.split('"')[1];
        }
        return metaData;
    }

    pushPGNMove(board: Board, newmove: string, teamColor: string): void {
        const move = removeAnnotations(newmove);
        let movingPieceType: string;
        let targetSquare: Position;
        let promotionType: string | null = null;
    
        if (move === "OO" || move === "OOO") {
            movingPieceType = PieceType.KING;
            const kingside = move === "OO";
            const rowKing = teamColor === TeamType.W ? 0 : 7;
            const columnKing = kingside ? 6 : 2;
            targetSquare = new Position(columnKing, rowKing);
        } else {
            movingPieceType = (move.charAt(0) === move.charAt(0).toUpperCase())
                ? pieceTypeFromChar(move.charAt(0)) || PieceType.PAWN
                : PieceType.PAWN;
    
            const moveWithoutPieceType = movingPieceType === PieceType.PAWN ? move : move.substring(1);
            // Check for promotion
            const promotionMatch = moveWithoutPieceType.match(/=[QRBN]/);
            let moveWithoutPromotion = move;
    
            if (promotionMatch) {
                moveWithoutPromotion = move.replace(promotionMatch[0], '');
                promotionType = promotionMatch ? pieceTypeFromChar(promotionMatch[0].charAt(1)) || null : null;
            }
            const columnChar = moveWithoutPromotion.charAt(moveWithoutPromotion.length - 2);
            const rowChar = moveWithoutPromotion.charAt(moveWithoutPromotion.length - 1);
    
            const columnPiece = column.indexOf(columnChar);
            const rowPiece = parseInt(rowChar) - 1;
    
            targetSquare = new Position(columnPiece, rowPiece);
        }
        const movingPiece = this.findMovingPiece(board, move, movingPieceType, targetSquare, teamColor);

        if (!movingPiece) {
            throw new Error(`No valid piece found for move: ${move}`);
        }

        board.playMove(targetSquare, movingPiece, promotionType);
    }
    

    findMovingPiece(board: Board, move: string, movingPieceType: string, targetSquare: Position, teamColor: string): Piece | undefined {
        // For castling moves, find the king of the current turn
        if (movingPieceType === PieceType.KING && (move === "OO" || move === "OOO")) {
            return board.pieces.find((piece) => piece.type === movingPieceType && piece.team === teamColor);
        }

        // For other moves, find the piece of the given type that can move to the target square
        const possiblePieces = board.pieces.filter((piece) => piece.type === movingPieceType && piece.team === teamColor);

        // Check for disambiguation information (file or rank) in the move string
        let disambiguationFile: number | null = null;
        let disambiguationRank: number | null = null;

        const disambiguationMatch = move.match(/([a-h]?)([1-8]?)[x-]?[a-h][1-8]/);
        if (disambiguationMatch) {
            disambiguationFile = disambiguationMatch[1] ? column.indexOf(disambiguationMatch[1]) : null;
            disambiguationRank = disambiguationMatch[2] ? parseInt(disambiguationMatch[2]) - 1 : null;
        }

        for (const piece of possiblePieces) {
            const validMoves = board.getValidMoves(piece);

            if (
                validMoves.some((move) => move.x === targetSquare.x && move.y === targetSquare.y) &&
                (disambiguationFile === null || piece.position.x === disambiguationFile) &&
                (disambiguationRank === null || piece.position.y === disambiguationRank)
            ) {
                return piece;
            }
        }
    }
}




