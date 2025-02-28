export interface Chapter {
    Title: string;
    Number: number;
    Moves: string[];
    Comments: string[];
    FEN: string;
}

export interface Course {
    chapter: Chapter[];
    moves: string[];
    comments: string[];
    title: string;
    addChapter(chapter: Chapter): void;
}