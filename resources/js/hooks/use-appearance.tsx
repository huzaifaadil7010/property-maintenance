export type ResolvedAppearance = 'light' | 'dark';
export type Appearance = ResolvedAppearance | 'system';

export type UseAppearanceReturn = {
    readonly appearance: Appearance;
    readonly resolvedAppearance: ResolvedAppearance;
    readonly updateAppearance: (mode: Appearance) => void;
};

const LIGHT_APPEARANCE = 'light' as const;

/*
 * Dark and system appearance support is intentionally paused while the light
 * visual system is being established. Keep the dynamic implementation below
 * commented so it can be restored when dark mode work resumes.
 *
 * const listeners = new Set<() => void>();
 * let currentAppearance: Appearance = 'system';
 *
 * const prefersDark = () =>
 *     window.matchMedia('(prefers-color-scheme: dark)').matches;
 *
 * const getStoredAppearance = (): Appearance =>
 *     (localStorage.getItem('appearance') as Appearance) || 'system';
 *
 * const isDarkMode = (appearance: Appearance): boolean =>
 *     appearance === 'dark' ||
 *     (appearance === 'system' && prefersDark());
 *
 * const applyTheme = (appearance: Appearance): void => {
 *     const isDark = isDarkMode(appearance);
 *     document.documentElement.classList.toggle('dark', isDark);
 *     document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
 * };
 *
 * initializeTheme previously restored the localStorage value, applied it, and
 * subscribed to system theme changes. useAppearance previously subscribed via
 * useSyncExternalStore and updateAppearance persisted to localStorage/cookie.
 */

export function initializeTheme(): void {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.classList.remove('dark');
    document.documentElement.style.colorScheme = LIGHT_APPEARANCE;
}

export function useAppearance(): UseAppearanceReturn {
    const updateAppearance = (): void => {
        initializeTheme();
    };

    return {
        appearance: LIGHT_APPEARANCE,
        resolvedAppearance: LIGHT_APPEARANCE,
        updateAppearance,
    } as const;
}
