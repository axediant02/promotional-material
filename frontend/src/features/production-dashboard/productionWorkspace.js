import { inject, provide } from 'vue'

const productionWorkspaceKey = Symbol('production-workspace')

export const provideProductionWorkspace = (value) => {
  provide(productionWorkspaceKey, value)
}

export const useProductionWorkspace = () => {
  const workspace = inject(productionWorkspaceKey, null)

  if (!workspace) {
    throw new Error('Production workspace context is not available.')
  }

  return workspace
}
