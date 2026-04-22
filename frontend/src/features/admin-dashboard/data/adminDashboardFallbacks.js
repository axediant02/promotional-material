export const adminDashboardFallbacks = {
  governanceInsights: [
    {
      id: 'role-changes',
      label: 'Role Changes Today',
      value: '2',
      detail: 'Temporary fallback until role-change snapshots are exposed by the backend.',
    },
    {
      id: 'production-load',
      label: 'Production Teams',
      value: '3 active',
      detail: 'Fallback load summary used to keep the admin overview operational while team-balancing APIs are incomplete.',
    },
    {
      id: 'audit-trail',
      label: 'Recent Admin Actions',
      value: '7 entries',
      detail: 'Fallback governance signal until a dedicated admin activity widget is available.',
    },
  ],
}
