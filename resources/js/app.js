import './bootstrap'
import ApexCharts from 'apexcharts'

// Make ApexCharts globally available
window.ApexCharts = ApexCharts

// Dark mode management
const darkModeKey = 'gabus-dark-mode'

function initDarkMode() {
    const stored = localStorage.getItem(darkModeKey)
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    const isDark = stored !== null ? stored === 'true' : prefersDark

    if (isDark) {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }
}

function toggleDarkMode() {
    const isDark = document.documentElement.classList.toggle('dark')
    localStorage.setItem(darkModeKey, String(isDark))
    return isDark
}

window.darkMode = { init: initDarkMode, toggle: toggleDarkMode }

// Init on load
initDarkMode()

// Toast helper (dispatches Livewire event)
window.toast = (message, type = 'info', duration = 4000) => {
    window.Livewire?.dispatch('toast', { message, type, duration })
}

// Register PWA service worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register('/sw.js')
            .catch(err => console.warn('SW registration failed:', err))
    })
}
