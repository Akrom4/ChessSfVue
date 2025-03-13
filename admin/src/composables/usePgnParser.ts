// src/composables/usePgnParser.ts
import { Pgn } from '../lib/chess/models/Pgn';

/**
 * Composable for parsing PGN (Portable Game Notation) files
 * @returns Methods for working with PGN data
 */
export function usePgnParser() {
  /**
   * Parse PGN text into a structured format with a course and chapters
   * @param rawPgn - Raw PGN text to parse
   * @returns Object containing the parsed course and the raw chapter texts
   */
  const parsePgn = (rawPgn: string) => {
    try {
      const parser = new Pgn(rawPgn);
      // The parseData method now returns both the course and the raw chapter texts
      return parser.parseData();
    } catch (error) {
      console.error('Error parsing PGN:', error);
      return { course: undefined, rawChapterTexts: [] };
    }
  };

  /**
   * Parse PGN text and extract just the chapters without creating a Course
   * More suitable for adding chapters to an existing course
   * @param rawPgn - Raw PGN text to parse
   * @returns Object containing the parsed chapters and the raw chapter texts
   */
  const parseChapters = (rawPgn: string) => {
    try {
      const parser = new Pgn(rawPgn);
      return parser.parseChapters();
    } catch (error) {
      console.error('Error parsing PGN chapters:', error);
      return { chapters: [], rawChapterTexts: [] };
    }
  };

  return { parsePgn, parseChapters };
}