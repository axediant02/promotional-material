export const normalizeMessage = (message, currentUserId) => ({
  message_id: message?.message_id ?? '',
  thread_id: message?.thread_id ?? '',
  sender_user_id: message?.sender_user_id ?? '',
  sender_name: message?.sender_name ?? 'User',
  body: message?.body ?? '',
  created_at: message?.created_at ?? new Date().toISOString(),
  is_own_message: message?.sender_user_id === currentUserId,
})

export const normalizeThread = (thread, currentUserId) => ({
  thread_id: thread?.thread_id ?? '',
  client_id: thread?.client_id ?? '',
  production_id: thread?.production_id ?? '',
  status: thread?.status ?? 'active',
  started_at: thread?.started_at ?? null,
  closed_at: thread?.closed_at ?? null,
  last_message_at: thread?.last_message_at ?? null,
  unread_count: Number(thread?.unread_count ?? 0),
  last_message_preview: thread?.last_message_preview ?? '',
  counterpart: thread?.counterpart ?? null,
  messages: (thread?.messages ?? []).map((message) => normalizeMessage(message, currentUserId)),
})

export const formatTimestamp = (value) => {
    if(!value){
        return 'Just now'
    }

    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(new Date(value))
}

export const formatThreadStatus = (value) => (value === 'archived' ? 'Archived' : 'Active')