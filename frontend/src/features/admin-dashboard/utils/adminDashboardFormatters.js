export const formatIdLabel = (value, prefix) => {
  if (!value) {
    return prefix
  }

  return `${prefix} ${String(value).slice(0, 8).toUpperCase()}`
}

export const formatShortMonthDay = (value, fallback = 'Awaiting due date') => {
  if (!value) {
    return fallback
  }

  return new Date(value).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

export const formatDateUs = (value, fallback = 'Active now') => {
  if (!value) {
    return fallback
  }

  return new Date(value).toLocaleDateString('en-US')
}

export const toSlug = (value, fallback = 'folder', maxLength = 18) =>
  String(value ?? fallback)
    .toLowerCase()
    .replaceAll(/\s+/g, '-')
    .slice(0, maxLength)
