export interface Chapter {
    id?: number;
    title?: string;
    pgndata?: any[];
    rawpgn?: string;
}

export class Course {
    id?: number;
    title: string;
    description?: string;
    image?: string;
    author?: string;
    colorside?: string;
    chapters: Chapter[];

    constructor(title: string = '') {
        this.title = title;
        this.chapters = [];
    }

    addChapter(chapter: Chapter): void {
        this.chapters.push(chapter);
    }
}