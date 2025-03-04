interface JwtPayload {
  exp: number;
  iat: number;
  [key: string]: any;
}

export function decodeToken(token: string): JwtPayload | null {
  try {
    const base64Url = token.split('.')[1]
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/')
    const jsonPayload = decodeURIComponent(atob(base64).split('').map(c => 
      '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
    ).join(''))
    return JSON.parse(jsonPayload)
  } catch (error) {
    console.error('Error decoding token:', error)
    return null
  }
}

export function isTokenExpired(token: string): boolean {
  const payload = decodeToken(token)
  if (!payload) return true

  const currentTime = Math.floor(Date.now() / 1000)
  return payload.exp < currentTime
}

export function getTokenExpirationTime(token: string): number | null {
  const payload = decodeToken(token)
  if (!payload) return null
  return payload.exp * 1000 // Convert to milliseconds
}

export function shouldRefreshToken(token: string, thresholdSeconds: number = 300): boolean {
  const payload = decodeToken(token)
  if (!payload) return true

  const currentTime = Math.floor(Date.now() / 1000)
  return payload.exp - currentTime < thresholdSeconds
} 