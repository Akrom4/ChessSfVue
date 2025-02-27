// src/data/navigation.ts
import { HomeIcon, BookOpenIcon, PuzzlePieceIcon } from '@heroicons/vue/24/outline'
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
  { name: 'Accueil', href: '#', icon: HomeIcon, current: false },
  {
    name: 'Leçons',
    icon: BookOpenIcon,
    current: false,
    children: [
      { name: 'Ouvertures', href: '#' },
      { name: 'Stratégie', href: '#' },
      { name: 'Finales', href: '#' },
    ],
  },
  {
    name: 'Tactique',
    icon: PuzzlePieceIcon,
    current: false,
    children: [
      { name: 'Puzzle Rush', href: '/chess' },
      { name: 'Puzzle Storm', href: '#' },
    ],
  },
]
