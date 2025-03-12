import { ref, computed, watch } from 'vue';
import { useRoute, useRouter, RouteLocationRaw } from 'vue-router';

export interface BreadcrumbItem {
  text: string;
  to: RouteLocationRaw;
  icon?: string;
}

// This map will help translate route names to human-readable text
const routeNameMap: Record<string, string> = {
  'Dashboard': 'Dashboard',
  'Login': 'Login',
  'Chess': 'Chess',
  'NotFound': 'Page Not Found',
  // Add more mappings as you create routes
  // 'UsersList': 'Users',
  // 'UserCreate': 'Create User',
  // 'UserEdit': 'Edit User',
  // 'CoursesList': 'Courses',
  // etc.
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
    // Check if there's a custom breadcrumb for this route
    if (route.name && customBreadcrumbs.value[route.name.toString()]) {
      return customBreadcrumbs.value[route.name.toString()];
    }

    // Default breadcrumb logic
    const result: BreadcrumbItem[] = [
      { text: 'Dashboard', to: '/', icon: 'pi pi-home' }
    ];

    // Add parent routes
    const matchedRoutes = route.matched;
    
    if (matchedRoutes.length > 1) {
      // Skip the root layout route and the current route
      for (let i = 1; i < matchedRoutes.length - 1; i++) {
        const match = matchedRoutes[i];
        if (match.name && match.path !== '/') {
          const name = match.name.toString();
          result.push({
            text: routeNameMap[name] || name,
            to: match.path
          });
        }
      }
    }

    // Add the current route
    if (route.name && route.name !== 'Dashboard') {
      const name = route.name.toString();
      result.push({
        text: routeNameMap[name] || name,
        to: route.fullPath
      });
    }

    return result;
  });

  return {
    breadcrumbs,
    setBreadcrumb
  };
} 