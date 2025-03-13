import { ref, computed, watch } from 'vue';
import { useRoute, useRouter, RouteLocationRaw } from 'vue-router';

export interface BreadcrumbItem {
  text: string;
  to: RouteLocationRaw;
  icon?: string;
}

// This map will help translate route names to human-readable text
const routeNameMap: Record<string, string> = {
  'Dashboard': 'Accueil',
  'Login': 'Connexion',
  'Chess': 'Echecs',
  'NotFound': 'Page non trouvée',
  'Users': 'Utilisateurs',
  'UserCreate': 'Créer un utilisateur',
  'UserEdit': 'Modifier un utilisateur',
  'Courses': 'Cours',
  'CourseCreate': 'Créer un cours',
  'CourseEdit': 'Modifier un cours',
  'Chapters': 'Chapitres',
  'ChapterCreate': 'Créer un chapitre',
  'ChapterEdit': 'Modifier un chapitre',
  // Add more mappings as you create routes
  // 'UsersList': 'Utilisateurs',
  // 'CoursesList': 'Cours',
  // etc.
};

// Function to get the display name for a route
export const getRouteDisplayName = (routeName: string): string => {
  return routeNameMap[routeName] || routeName;
};

// Store for custom breadcrumbs
const customBreadcrumbs = ref<Record<string, BreadcrumbItem[]>>({});

export function useBreadcrumb() {
  const route = useRoute();
  const router = useRouter();

  // Set custom breadcrumb for a specific route
  const setBreadcrumb = (routeName: string, items: BreadcrumbItem[]) => {
    customBreadcrumbs.value[routeName] = items;
  };

  // Get the breadcrumb items for the current route
  const breadcrumbs = computed(() => {
    // Debug logging
    console.log('Current route:', {
      path: route.path,
      name: route.name,
      matched: route.matched.map(m => ({ name: m.name, path: m.path }))
    });

    // Check if there's a custom breadcrumb for this route
    if (route.name && customBreadcrumbs.value[route.name.toString()]) {
      return customBreadcrumbs.value[route.name.toString()];
    }

    // Default breadcrumb logic
    const result: BreadcrumbItem[] = [
      { text: getRouteDisplayName('Dashboard'), to: '/', icon: 'pi pi-home' }
    ];

    // Special case for root routes with children
    if (route.path !== '/' && route.matched.length >= 2) {
      // Get the actual route component (skipping layout components)
      const currentRoute = route.matched[route.matched.length - 1];
      
      if (currentRoute.name) {
        const name = currentRoute.name.toString();
        const text = getRouteDisplayName(name);
        
        // If it's not Dashboard (already in the breadcrumb), add it
        if (name !== 'Dashboard') {
          result.push({
            text: text,
            to: route.fullPath
          });
        }
      }
    }

    return result;
  });

  return {
    breadcrumbs,
    setBreadcrumb,
    getRouteDisplayName
  };
} 