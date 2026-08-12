function observe(el, attr) {
  el.setAttribute(attr, '')

  const observer = new IntersectionObserver(
    ([entry]) => {
      if (entry.isIntersecting) {
        el.classList.add('is-visible')
        observer.unobserve(el)
      }
    },
    { threshold: 0.15, rootMargin: '0px 0px -8% 0px' },
  )

  observer.observe(el)
}

// Fades a single element in as it scrolls into view.
export const vReveal = {
  mounted(el) {
    observe(el, 'data-reveal')
  },
}

// Same, but staggers direct children (grids, lists) via CSS nth-child delays.
export const vRevealGroup = {
  mounted(el) {
    observe(el, 'data-reveal-group')
  },
}
