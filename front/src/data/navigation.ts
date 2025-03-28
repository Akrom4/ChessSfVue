// src/data/navigation.ts
import { HomeIcon, BookOpenIcon, PuzzlePieceIcon, AcademicCapIcon } from '@heroicons/vue/24/outline'
import type { FunctionalComponent, HTMLAttributes, VNodeProps } from 'vue'

export interface SubItem {
  name: string
  href: string
  current?: boolean
}

export interface NavigationItem {
  name: string
  href?: string
  icon: FunctionalComponent<HTMLAttributes & VNodeProps>
  current: boolean
  children?: SubItem[]
}

export const navigation: NavigationItem[] = [
  { name: 'Accueil', href: '/', icon: HomeIcon, current: false },
  {
    name: 'Leçons',
    href: '/lessons',
    icon: BookOpenIcon,
    current: false,
  },
  {
    name: 'Mes leçons',
    href: '/my-lessons',
    icon: AcademicCapIcon, 
    current: false,
  },
  {
    name: 'Tactique',
    icon: PuzzlePieceIcon,
    current: false,
    children: [
      { name: 'Puzzles', href: '/puzzles' },
      { name: 'Puzzle Rush', href: '/puzzle-rush' },
      { name: 'Puzzle Storm', href: '/puzzle-storm' },
    ],
  },
]
