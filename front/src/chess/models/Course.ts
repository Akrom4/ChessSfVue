import type { Chapter, Course as CourseInterface } from '../types/courseTypes';

export class Course implements CourseInterface {
    chapter: Chapter[];
    moves: string[];
    comments: string[];
    title: string;

    constructor(title: string = '') {
        this.title = title;
        this.chapter = [];
        this.moves = [];
        this.comments = [];
    }

    addChapter(chapter: Chapter): void {
        this.chapter.push(chapter);
    }
}