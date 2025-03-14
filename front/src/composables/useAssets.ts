import { ASSETS_URL } from '../api'

/**
 * Composable for working with asset URLs
 * This helps standardize how assets (images, etc.) are referenced in the application
 */
export function useAssets() {
  /**
   * Get the full URL for an asset
   * @param path The relative path to the asset
   * @returns The full URL to the asset
   */
  const getAssetUrl = (path: string): string => {
    // If the path is already a full URL, return it as is
    if (path && (path.startsWith('http://') || path.startsWith('https://'))) {
      return path
    }

    // Remove leading slash if present
    const cleanPath = path && path.startsWith('/') ? path.substring(1) : path

    // Combine base URL with path
    return cleanPath ? `${ASSETS_URL}/${cleanPath}` : ''
  }

  /**
   * Get the full URL for a course image
   * @param imagePath The relative path to the course image
   * @returns The full URL to the course image
   */
  const getCourseImageUrl = (imagePath: string | null): string => {
    if (!imagePath) return ''
    return getAssetUrl(`images/courses/${imagePath}`)
  }
  
  /**
   * Get the full URL for a user avatar
   * @param avatarPath The relative path to the user avatar
   * @returns The full URL to the user avatar
   */
  const getUserAvatarUrl = (avatarPath: string | null): string => {
    if (!avatarPath) return ''
    return getAssetUrl(`uploads/avatars/${avatarPath}`)
  }

  return {
    getAssetUrl,
    getCourseImageUrl,
    getUserAvatarUrl
  }
} 